<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\User;
use App\Services\DepotStockService;
use App\Support\DepotRolePermissions;
use App\Support\DepotUi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DepotController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function __construct(
        private readonly DepotStockService $stockService,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeDepotPermission('depot.view');

        $query = Depot::query()->with([
            'department',
            'branch',
            'pharmacy',
            'parentDepot',
            'activeUsers',
        ]);

        $this->scopeQueryToUserDepots($query, $request->user());

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('parent_depot_id')) {
            $query->where('parent_depot_id', $request->parent_depot_id);
        }
        if ($request->filled('is_active') && in_array((string) $request->is_active, ['0', '1'], true)) {
            $query->where('is_active', $request->is_active === '1');
        }
        if ($request->filled('is_base') && in_array((string) $request->is_base, ['0', '1'], true)) {
            $query->where('is_base', $request->is_base === '1');
        }

        $perPage = (int) $request->get('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        $depots = $query->orderBy('name')->paginate($perPage)->appends($request->query());
        $options = $this->depotFormOptions();

        return view('pages.depots.index', [
            'depots' => $depots,
            'branches' => collect($options['branches'])->map(fn ($item) => (object) $item),
            'departments' => collect($options['departments'])->map(fn ($item) => (object) $item),
            'pharmacies' => collect($options['pharmacies'])->map(fn ($item) => (object) $item),
            'parentDepots' => collect($options['depots'])->map(fn ($item) => (object) $item),
            'permissions' => $this->depotCrudPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeDepotPermission('depot.create');

        $options = $this->depotFormOptions();

        return view('pages.depots.create', [
            'branches' => collect($options['branches'])->map(fn ($item) => (object) $item),
            'departments' => collect($options['departments'])->map(fn ($item) => (object) $item),
            'pharmacies' => collect($options['pharmacies'])->map(fn ($item) => (object) $item),
            'depots' => collect($options['depots'])->map(fn ($item) => (object) $item),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'last_name', 'email']),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeDepotPermission('depot.create');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_base' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'nullable|exists:users,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|in:manager,staff,procurement,viewer',
        ]);

        $depot = DB::transaction(function () use ($data, $request) {
            $depot = Depot::create([
                ...collect($data)->except(['user_ids', 'roles'])->all(),
                'is_active' => $request->boolean('is_active', true),
                'is_base' => $request->boolean('is_base'),
                'created_by' => Auth::id(),
            ]);

            $depot->syncUsers($data['user_ids'] ?? [], $data['roles'] ?? []);

            return $depot;
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => localize('global.depot.depot_created_successfully.'),
                'redirect' => route('depots.show', $depot),
            ]);
        }

        return redirect()
            ->route('depots.show', $depot)
            ->with('success', localize('global.depot.depot_created_successfully.'));
    }

    public function show(Depot $depot): View
    {
        $this->authorizeDepotRecord($depot, DepotRolePermissions::ACTION_VIEW);

        $depot->load([
            'department',
            'branch',
            'pharmacy',
            'parentDepot',
            'activeUsers',
        ]);

        $stockItems = $this->stockService->stockItemsForDepot($depot->id)->take(10)->values();

        $recentTransactions = DepotTransaction::query()
            ->with(['medicine', 'tool', 'fromDepot', 'toDepot', 'pharmacy'])
            ->forDepot($depot->id)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(10)
            ->get();

        $pendingOutgoingRequests = DepotRequest::query()
            ->with(['items.medicine', 'items.tool', 'sourceDepot', 'pharmacy'])
            ->where(function ($q) use ($depot) {
                $q->where('requesting_depot_id', $depot->id);
                if ($depot->pharmacy_id) {
                    $q->orWhere('pharmacy_id', $depot->pharmacy_id);
                }
            })
            ->whereIn('status', [
                DepotRequest::STATUS_DRAFT,
                DepotRequest::STATUS_PENDING,
                DepotRequest::STATUS_APPROVED,
            ])
            ->latest('id')
            ->limit(5)
            ->get();

        $pendingIncomingRequests = DepotRequest::query()
            ->with(['items.medicine', 'items.tool', 'requestingDepot', 'pharmacy'])
            ->where('source_depot_id', $depot->id)
            ->whereIn('status', [DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED])
            ->latest('id')
            ->limit(5)
            ->get();

        $allStock = $this->stockService->stockItemsForDepot($depot->id);

        return view('pages.depots.show', [
            'depot' => $depot,
            'stockItems' => $stockItems,
            'recentTransactions' => $recentTransactions,
            'transactionRows' => $recentTransactions
                ->map(fn (DepotTransaction $txn) => $this->transactionRow($txn, $depot))
                ->all(),
            'pendingOutgoingRequests' => $pendingOutgoingRequests,
            'pendingIncomingRequests' => $pendingIncomingRequests,
            'metrics' => [
                'stock_items' => $allStock->count(),
                'total_quantity' => (int) $allStock->sum('available'),
                'recent_transactions' => $recentTransactions->count(),
                'pending_requests' => $pendingOutgoingRequests->count() + $pendingIncomingRequests->count(),
            ],
            'permissions' => array_merge($this->depotCrudPermissions(), [
                'transaction_create' => $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_TRANSACTION_CREATE,
                    $depot->id,
                ),
                'request_create' => $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_REQUEST_CREATE,
                    $depot->id,
                ),
                'movement_pharmacy' => $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_MOVEMENT_DEPOT_TO_PHARMACY,
                    $depot->id,
                ) && (bool) $depot->pharmacy_id,
            ]),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function transactionRow(DepotTransaction $txn, Depot $depot): array
    {
        $isDepotTransfer = $txn->type === DepotTransaction::TYPE_DEPOT_TO_DEPOT;
        $isIncomingTransfer = $isDepotTransfer && (int) $txn->to_depot_id === (int) $depot->id;
        $isOutgoingTransfer = $isDepotTransfer && (int) $txn->from_depot_id === (int) $depot->id;

        $direction = match (true) {
            in_array($txn->type, [DepotTransaction::TYPE_STOCK_IN, DepotTransaction::TYPE_ADJUSTMENT], true), $isIncomingTransfer => 'in',
            in_array($txn->type, [DepotTransaction::TYPE_STOCK_OUT, DepotTransaction::TYPE_DEPOT_TO_PHARMACY], true), $isOutgoingTransfer => 'out',
            default => 'transfer',
        };

        return [
            'type' => $txn->type,
            'type_label' => str_replace('_', ' ', $txn->type),
            'icon' => match ($direction) {
                'in' => 'bx-down-arrow-alt',
                'out' => 'bx-up-arrow-alt',
                default => 'bx-transfer-alt',
            },
            'icon_class' => $txn->type === DepotTransaction::TYPE_ADJUSTMENT
                ? 'is-adjustment'
                : 'is-'.$direction,
            'item_name' => $txn->medicine?->name ?? $txn->tool?->name ?? '-',
            'quantity' => $txn->quantity,
            'date' => $txn->transaction_date ? verta($txn->transaction_date)->format('Y-m-d') : '-',
        ];
    }

    /**
     * @param  array<string, int>  $stockStats
     * @return list<array<string, mixed>>
     */
    protected function stockStatusChips(Request $request, Depot $depot, array $stockStats, string $activeStatus): array
    {
        $chips = [
            ['value' => '', 'label' => localize('global.all'), 'icon' => 'bx-grid-alt', 'count' => $stockStats['total_items']],
            ['value' => 'healthy', 'label' => localize('global.in_stock'), 'icon' => 'bx-check-circle', 'count' => $stockStats['total_healthy']],
            ['value' => 'low_stock', 'label' => localize('global.low_stock'), 'icon' => 'bx-error', 'count' => $stockStats['total_low_stock']],
            ['value' => 'out_of_stock', 'label' => localize('global.out_of_stock'), 'icon' => 'bx-x-circle', 'count' => $stockStats['total_out_of_stock']],
        ];

        return array_map(function (array $chip) use ($request, $depot, $activeStatus) {
            $query = array_filter(
                array_merge($request->except('page'), ['stock_status' => $chip['value']]),
                fn ($value) => $value !== null && $value !== ''
            );

            $chip['url'] = route('depots.stock', array_merge(['depot' => $depot->id], $query));
            $chip['active'] = $activeStatus === (string) $chip['value'];

            return $chip;
        }, $chips);
    }

    public function stock(Request $request, Depot $depot): View
    {
        $this->authorizeDepotRecord($depot, DepotRolePermissions::ACTION_VIEW);

        $itemType = $request->get('item_type') ?: null;
        $search = $request->get('search') ?: null;
        $stockStatus = $request->get('stock_status') ?: null;
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');

        $stockItems = $this->stockService
            ->stockItemsForDepot($depot->id, $itemType, $search, includeZero: true)
            ->when(
                ! $stockStatus,
                fn ($items) => $items->where('available', '>', 0),
            )
            ->pipe(fn ($items) => $this->stockService->filterAndSortStockItems(
                $items,
                $stockStatus,
                (string) $sortBy,
                (string) $sortOrder,
            ))
            ->map(fn (array $item) => [
                ...$item,
                'stock_status' => DepotUi::stockLevel((int) $item['available']),
            ])
            ->values();

        $maxQuantity = max(1, (int) ($stockItems->max('available') ?: 1));

        $stockItems = $stockItems->map(function (array $item) use ($maxQuantity) {
            $isTool = $item['item_type'] === 'tool';

            return [
                ...$item,
                'is_tool' => $isTool,
                'item_type_label' => $isTool ? localize('global.depot.tool') : localize('global.medicine'),
                'bar_percent' => min(100, max(0, (int) round(((int) $item['available'] / $maxQuantity) * 100))),
            ];
        });

        $stockStats = $this->stockService->stockStatsForDepot($depot->id);

        $filters = [
            'item_type' => (string) ($itemType ?? ''),
            'search' => (string) ($search ?? ''),
            'stock_status' => (string) ($stockStatus ?? ''),
            'sort_by' => (string) $sortBy,
            'sort_order' => (string) $sortOrder,
        ];

        return view('pages.depots.stock', [
            'depot' => $depot,
            'stockItems' => $stockItems,
            'stockStats' => $stockStats,
            'stockCount' => $stockStats['total_items'],
            'stockTotal' => $stockStats['total_quantity'],
            'medicineCount' => $stockStats['medicine_count'],
            'toolCount' => $stockStats['tool_count'],
            'maxQuantity' => $maxQuantity,
            'itemType' => $itemType,
            'search' => $search,
            'filters' => $filters,
            'activeStatus' => $filters['stock_status'],
            'statusChips' => $this->stockStatusChips($request, $depot, $stockStats, $filters['stock_status']),
            'permissions' => [
                'request_create' => $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_REQUEST_CREATE,
                    $depot->id,
                ),
                'transaction_create' => $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_TRANSACTION_CREATE,
                    $depot->id,
                ),
            ],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function edit(Depot $depot): View
    {
        $this->authorizeDepotPermission('depot.update');

        $depot->load('activeUsers');
        $options = $this->depotFormOptions($depot);

        return view('pages.depots.edit', [
            'depot' => $depot,
            'branches' => collect($options['branches'])->map(fn ($item) => (object) $item),
            'departments' => collect($options['departments'])->map(fn ($item) => (object) $item),
            'pharmacies' => collect($options['pharmacies'])->map(fn ($item) => (object) $item),
            'depots' => collect($options['depots'])->map(fn ($item) => (object) $item),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'last_name', 'email']),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function update(Request $request, Depot $depot): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->authorizeDepotPermission('depot.update');

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'is_base' => 'nullable|boolean',
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'pharmacy_id' => 'nullable|exists:pharmacies,id',
            'parent_depot_id' => 'nullable|exists:depots,id|not_in:'.$depot->id,
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'nullable|exists:users,id',
            'roles' => 'nullable|array',
            'roles.*' => 'nullable|in:manager,staff,procurement,viewer',
        ]);

        DB::transaction(function () use ($data, $request, $depot) {
            $depot->update([
                ...collect($data)->except(['user_ids', 'roles'])->all(),
                'is_active' => $request->boolean('is_active'),
                'is_base' => $request->boolean('is_base'),
                'updated_by' => Auth::id(),
            ]);

            $depot->syncUsers($data['user_ids'] ?? [], $data['roles'] ?? []);
        });

        if ($request->ajax()) {
            return response()->json([
                'message' => localize('global.depot.depot_updated_successfully.'),
                'redirect' => route('depots.show', $depot),
            ]);
        }

        return redirect()
            ->route('depots.show', $depot)
            ->with('success', localize('global.depot.depot_updated_successfully.'));
    }

    public function destroy(Depot $depot): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.delete');

        $depot->delete();

        return redirect()
            ->route('depots.index')
            ->with('success', localize('global.depot.depot_deleted_successfully.'));
    }
}
