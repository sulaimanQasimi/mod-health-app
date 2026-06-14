<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\User;
use App\Services\DepotStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DepotController extends Controller
{
    use ManagesDepotAccess;
    use PaginatesInertiaIndex;
    use ProvidesDepotFormData;

    private const FILTER_KEYS = [
        'search', 'branch_id', 'department_id', 'pharmacy_id', 'parent_depot_id', 'is_active', 'is_base', 'per_page',
    ];

    public function __construct(
        private readonly DepotStockService $stockService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.view');

        $query = Depot::query()->with(['department:id,name', 'branch:id,name', 'pharmacy:id,name', 'parentDepot:id,name', 'activeUsers:id']);

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
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active === '1');
        }
        if ($request->filled('is_base')) {
            $query->where('is_base', $request->is_base === '1');
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request, 15);
        $options = $this->depotFormOptions();

        return Inertia::render('Depots/Index', [
            'depots' => $this->paginationPayload($paginator, fn (Depot $depot) => $this->transformListItem($depot)),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'branches' => $options['branches'],
                'departments' => $options['departments'],
                'pharmacies' => $options['pharmacies'],
                'parentDepots' => $options['depots'],
            ],
            'permissions' => $this->depotCrudPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.index'),
                'create' => route('react.depots.create'),
                'show' => url('/react/depots'),
                'edit' => url('/react/depots'),
                'destroy' => url('/react/depots'),
                'stock' => url('/react/depots'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorizeDepotPermission('depot.create');

        return Inertia::render('Depots/Create', [
            'formData' => $this->depotFormOptions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.index'),
                'store' => route('react.depots.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
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

        return redirect()
            ->route('react.depots.show', $depot)
            ->with('success', localize('global.depot.depot_created_successfully.'));
    }

    public function show(Depot $depot): Response
    {
        $this->authorizeDepotPermission('depot.view');

        $depot->load(['department:id,name', 'branch:id,name', 'pharmacy:id,name', 'parentDepot:id,name', 'activeUsers:id,name,last_name,email']);

        $stockItems = $this->stockService->stockItemsForDepot($depot->id)->take(10)->values()->all();

        $recentTransactions = DepotTransaction::query()
            ->with(['medicine:id,name', 'tool:id,name', 'fromDepot:id,name', 'toDepot:id,name', 'pharmacy:id,name'])
            ->forDepot($depot->id)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (DepotTransaction $tx) => $this->transformTransactionPreview($tx))
            ->values()
            ->all();

        $pendingOutgoing = DepotRequest::query()
            ->with(['medicine:id,name', 'tool:id,name', 'sourceDepot:id,name'])
            ->where('requesting_depot_id', $depot->id)
            ->whereIn('status', [DepotRequest::STATUS_DRAFT, DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (DepotRequest $item) => $this->transformRequestPreview($item))
            ->values()
            ->all();

        $pendingIncoming = DepotRequest::query()
            ->with(['medicine:id,name', 'tool:id,name', 'requestingDepot:id,name'])
            ->where('source_depot_id', $depot->id)
            ->whereIn('status', [DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (DepotRequest $item) => $this->transformRequestPreview($item))
            ->values()
            ->all();

        $stockCount = $this->stockService->stockItemsForDepot($depot->id)->count();
        $totalQuantity = $this->stockService->stockItemsForDepot($depot->id)->sum('available');

        return Inertia::render('Depots/Show', [
            'depot' => $this->transformDetail($depot),
            'metrics' => [
                'stock_items' => $stockCount,
                'total_quantity' => $totalQuantity,
                'recent_transactions' => count($recentTransactions),
                'pending_requests' => count($pendingOutgoing) + count($pendingIncoming),
            ],
            'stockPreview' => $stockItems,
            'recentTransactions' => $recentTransactions,
            'pendingOutgoingRequests' => $pendingOutgoing,
            'pendingIncomingRequests' => $pendingIncoming,
            'permissions' => array_merge($this->depotCrudPermissions(), [
                'transaction_create' => $this->userCan('depot.transaction.create'),
                'request_create' => $this->userCan('depot.request.create'),
                'movement_pharmacy' => $this->userCan('depot.movement.depot_to_pharmacy') && (bool) $depot->pharmacy_id,
            ]),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.index'),
                'edit' => route('react.depots.edit', $depot),
                'destroy' => route('react.depots.destroy', $depot),
                'stock' => route('react.depots.stock', $depot),
                'transactionCreate' => route('react.depots.transactions.create', ['depot_id' => $depot->id]),
                'requestCreate' => route('react.depots.requests.create', ['requesting_depot_id' => $depot->id]),
                'depotToPharmacy' => route('react.depots.movements.depot-to-pharmacy', array_filter([
                    'from_depot_id' => $depot->id,
                    'pharmacy_id' => $depot->pharmacy_id,
                ])),
            ],
        ]);
    }

    public function stock(Request $request, Depot $depot): Response
    {
        $this->authorizeDepotPermission('depot.view');

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
            ->pipe(fn ($items) => $this->stockService->filterAndSortStockItems($items, $stockStatus, (string) $sortBy, (string) $sortOrder))
            ->map(fn (array $item) => [
                ...$item,
                'stock_status' => $this->stockLevelForQuantity((int) $item['available']),
            ])
            ->values()
            ->all();

        $maxQuantity = collect($stockItems)->max('available') ?: 1;

        return Inertia::render('Depots/Stock', [
            'depot' => [
                'id' => $depot->id,
                'name' => $depot->name,
                'is_active' => (bool) $depot->is_active,
            ],
            'stockItems' => $stockItems,
            'stockStats' => $this->stockService->stockStatsForDepot($depot->id),
            'maxQuantity' => (int) $maxQuantity,
            'filters' => [
                'item_type' => (string) ($itemType ?? ''),
                'search' => (string) ($search ?? ''),
                'stock_status' => (string) ($stockStatus ?? ''),
                'sort_by' => (string) $sortBy,
                'sort_order' => (string) $sortOrder,
            ],
            'filterOptions' => [
                'stockStatuses' => ['healthy', 'low_stock', 'out_of_stock'],
                'sortFields' => ['name', 'quantity', 'item_type'],
            ],
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'show' => route('react.depots.show', $depot),
                'stock' => route('react.depots.stock', $depot),
                'requestCreate' => route('react.depots.requests.create', ['source_depot_id' => $depot->id]),
                'transactionCreate' => route('react.depots.transactions.create', ['depot_id' => $depot->id]),
            ],
            'permissions' => [
                'request_create' => $this->userCan('depot.request.create'),
                'transaction_create' => $this->userCan('depot.transaction.create'),
            ],
        ]);
    }

    private function stockLevelForQuantity(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }

        if ($quantity <= DepotStockService::LOW_STOCK_THRESHOLD) {
            return 'low_stock';
        }

        return 'healthy';
    }

    public function edit(Depot $depot): Response
    {
        $this->authorizeDepotPermission('depot.update');

        $depot->load('activeUsers:id,name,last_name,email');

        return Inertia::render('Depots/Edit', [
            'depot' => $this->transformDetail($depot),
            'formData' => $this->depotFormOptions($depot),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.index'),
                'show' => route('react.depots.show', $depot),
                'update' => route('react.depots.update', $depot),
            ],
        ]);
    }

    public function update(Request $request, Depot $depot): RedirectResponse
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

        return redirect()
            ->route('react.depots.show', $depot)
            ->with('success', localize('global.depot.depot_updated_successfully.'));
    }

    public function destroy(Depot $depot): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.delete');

        $depot->delete();

        return redirect()
            ->route('react.depots.index')
            ->with('success', localize('global.depot.depot_deleted_successfully.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function transformListItem(Depot $depot): array
    {
        return [
            'id' => $depot->id,
            'name' => $depot->name,
            'address' => $depot->address,
            'branch_name' => $depot->branch?->name,
            'department_name' => $depot->department?->name,
            'pharmacy_name' => $depot->pharmacy?->name,
            'parent_depot_name' => $depot->parentDepot?->name,
            'is_active' => (bool) $depot->is_active,
            'is_base' => (bool) $depot->is_base,
            'users_count' => $depot->activeUsers->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(Depot $depot): array
    {
        return [
            'id' => $depot->id,
            'name' => $depot->name,
            'address' => $depot->address,
            'branch_id' => $depot->branch_id,
            'department_id' => $depot->department_id,
            'pharmacy_id' => $depot->pharmacy_id,
            'parent_depot_id' => $depot->parent_depot_id,
            'branch_name' => $depot->branch?->name,
            'department_name' => $depot->department?->name,
            'pharmacy_name' => $depot->pharmacy?->name,
            'parent_depot_name' => $depot->parentDepot?->name,
            'is_active' => (bool) $depot->is_active,
            'is_base' => (bool) $depot->is_base,
            'users' => $depot->activeUsers->map(fn (User $user) => [
                'id' => $user->id,
                'full_name' => trim("{$user->name} {$user->last_name}"),
                'email' => $user->email,
                'role' => $user->pivot->role,
            ])->values()->all(),
            'assignments' => $depot->activeUsers->map(fn (User $user) => [
                'user_id' => (string) $user->id,
                'role' => $user->pivot->role ?? 'staff',
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformTransactionPreview(DepotTransaction $tx): array
    {
        return [
            'id' => $tx->id,
            'transaction_number' => $tx->transaction_number,
            'type' => $tx->type,
            'status' => $tx->status,
            'quantity' => $tx->quantity,
            'item_name' => $tx->medicine?->name ?? $tx->tool?->name,
            'transaction_date' => $tx->transaction_date?->format('Y-m-d'),
            'show_url' => route('react.depots.transactions.show', $tx),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformRequestPreview(DepotRequest $request): array
    {
        return [
            'id' => $request->id,
            'request_number' => $request->request_number,
            'status' => $request->status,
            'quantity' => $request->quantity,
            'item_name' => $request->medicine?->name ?? $request->tool?->name,
            'counterparty_name' => $request->sourceDepot?->name ?? $request->requestingDepot?->name,
            'show_url' => route('react.depots.requests.show', $request),
        ];
    }
}
