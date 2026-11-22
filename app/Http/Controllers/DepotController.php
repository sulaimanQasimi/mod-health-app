<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Models\Depot;
use App\Models\DepotTransferRequest;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use App\Support\DepotRolePermissions;
use App\Support\InventoryNav;
use App\Support\PackQuantity;
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

    private const LOW_STOCK_THRESHOLD = 10;

    public function index(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.view');

        $query = Depot::query()->with(['department:id,name', 'branch:id,name', 'pharmacy:id,name', 'parentDepot:id,name', 'activeUsers:id']);

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
            'navPermissions' => $this->depotNavPermissions(),
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('depots.index'),
                'create' => route('depots.create'),
                'show' => url('/depots'),
                'edit' => url('/depots'),
                'destroy' => url('/depots'),
                'stock' => url('/depots'),
            ],
        ]);
    }

    public function create(): Response
    {
        $this->authorizeDepotPermission('depot.create');

        return Inertia::render('Depots/Create', [
            'formData' => $this->depotFormOptions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.index'),
                'store' => route('depots.store'),
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
            ->route('depots.show', $depot)
            ->with('success', localize('global.depot.depot_created_successfully.'));
    }

    public function show(Depot $depot): Response
    {
        $this->authorizeDepotRecord($depot, DepotRolePermissions::ACTION_VIEW);

        $depot->load(['department:id,name', 'branch:id,name', 'pharmacy:id,name', 'parentDepot:id,name', 'activeUsers:id,name,last_name,email']);

        $stocks = Stock::query()
            ->with('medicine:id,name')
            ->forDepot($depot->id)
            ->where('qty_on_hand', '>', 0)
            ->orderByDesc('id')
            ->get();

        $stockPreview = $stocks->take(10)->map(fn (Stock $stock) => $this->transformStockPreview($stock))->values()->all();

        $recentMovements = StockMovement::query()
            ->with('medicine:id,name')
            ->where(function ($q) use ($depot) {
                $q->where(function ($from) use ($depot) {
                    $from->where('from_location_type', Stock::LOCATION_DEPOT)
                        ->where('from_location_id', $depot->id);
                })->orWhere(function ($to) use ($depot) {
                    $to->where('to_location_type', Stock::LOCATION_DEPOT)
                        ->where('to_location_id', $depot->id);
                });
            })
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (StockMovement $movement) => [
                'id' => $movement->id,
                'type' => $movement->type,
                'batch_number' => $movement->batch_number,
                'medicine_name' => $movement->medicine?->name,
                'qty_units' => $movement->qty_units,
                'created_at' => optional($movement->created_at)?->toDateTimeString(),
            ])
            ->values()
            ->all();

        $pendingTransfers = DepotTransferRequest::query()
            ->with(['pharmacy:id,name', 'destinationDepot:id,name'])
            ->where('source_depot_id', $depot->id)
            ->whereIn('status', [
                DepotTransferRequest::STATUS_DRAFT,
                DepotTransferRequest::STATUS_PENDING,
                DepotTransferRequest::STATUS_APPROVED,
            ])
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn (DepotTransferRequest $item) => [
                'id' => $item->id,
                'request_number' => $item->request_number,
                'status' => $item->status,
                'destination' => $item->pharmacy?->name ?? $item->destinationDepot?->name,
            ])
            ->values()
            ->all();

        return Inertia::render('Depots/Show', [
            'depot' => $this->transformDetail($depot),
            'metrics' => [
                'stock_items' => $stocks->count(),
                'total_quantity' => (int) $stocks->sum('qty_on_hand'),
                'recent_transactions' => count($recentMovements),
                'pending_requests' => count($pendingTransfers),
            ],
            'stockPreview' => $stockPreview,
            'recentTransactions' => $recentMovements,
            'pendingOutgoingRequests' => $pendingTransfers,
            'pendingIncomingRequests' => [],
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
            'urls' => [
                'index' => route('depots.index'),
                'edit' => route('depots.edit', $depot),
                'destroy' => route('depots.destroy', $depot),
                'stock' => route('stocks.index', ['location_type' => 'depot', 'location_id' => $depot->id]),
                'transactionCreate' => route('purchases.create'),
                'requestCreate' => route('depot-transfers.create', ['source_depot_id' => $depot->id]),
                'depotToPharmacy' => route('depot-transfers.create', array_filter([
                    'source_depot_id' => $depot->id,
                    'pharmacy_id' => $depot->pharmacy_id,
                ])),
            ],
        ]);
    }

    private function transformStockPreview(Stock $stock): array
    {
        $split = PackQuantity::fromUnits((int) $stock->qty_on_hand, (int) $stock->units_per_pack);

        return [
            'id' => $stock->id,
            'name' => $stock->medicine?->name,
            'batch_number' => $stock->batch_number,
            'available' => $stock->qty_on_hand,
            'packs' => $split['packs'],
            'units' => $split['units'],
            'unit_sale_price' => (float) $stock->unit_sale_price,
            'pack_price' => (float) $stock->pack_price,
            'stock_status' => $this->stockLevelForQuantity((int) $stock->qty_on_hand),
        ];
    }

    private function stockLevelForQuantity(int $quantity): string
    {
        if ($quantity <= 0) {
            return 'out_of_stock';
        }

        if ($quantity <= self::LOW_STOCK_THRESHOLD) {
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
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.index'),
                'show' => route('depots.show', $depot),
                'update' => route('depots.update', $depot),
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

}
