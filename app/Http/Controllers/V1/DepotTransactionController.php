<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreReactDepotTransactionRequest;
use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\PharmacyFulfillment;
use App\Services\DepotStockService;
use App\Support\DepotRolePermissions;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DepotTransactionController extends Controller
{
    use ManagesDepotAccess;
    use PaginatesInertiaIndex;
    use ProvidesDepotFormData;

    private const FILTER_KEYS = [
        'search', 'depot_id', 'pharmacy_id', 'medicine_id', 'tool_id', 'item_type', 'type', 'status', 'date_from', 'date_to', 'per_page',
    ];

    public function __construct(
        private readonly DepotStockService $stockService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.transaction.view');

        $query = DepotTransaction::with([
            'depot:id,name',
            'fromDepot:id,name',
            'toDepot:id,name',
            'pharmacy:id,name',
            'medicine:id,name',
            'tool:id,name',
            'unit:id,name',
            'createdBy:id,name,last_name',
        ]);

        $this->applyTransactionFilters($query, $request);
        $this->scopeTransactionsToUserDepots($query, $request->user());

        $paginator = $this->paginateQuery($query->latest('transaction_date')->latest('id'), $request, 15);
        $options = $this->depotFormOptions();

        return Inertia::render('Depots/Transactions/Index', [
            'transactions' => $this->paginationPayload($paginator, fn (DepotTransaction $tx) => $this->transformListItem($tx)),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'depots' => $options['activeDepots'],
                'pharmacies' => $options['pharmacies'],
                'medicines' => $options['medicines'],
                'tools' => $options['tools'],
                'types' => DepotTransaction::types(),
                'statuses' => DepotTransaction::statuses(),
            ],
            'permissions' => $this->depotTransactionPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.transactions.index'),
                'create' => route('depots.transactions.create'),
                'show' => url('/depots/transactions'),
                'cancel' => url('/depots/transactions'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.transaction.create');

        $user = $request->user();
        $hintedDepotId = $this->validatedTransactionDepotHint($request);

        return Inertia::render('Depots/Transactions/Create', [
            'depot' => $this->transactionDepotPreview($hintedDepotId, $user),
            'defaults' => [
                'type' => DepotTransaction::TYPE_ADJUSTMENT,
            ],
            'formData' => $this->depotFormOptions(),
            'types' => [
                DepotTransaction::TYPE_STOCK_IN,
                DepotTransaction::TYPE_STOCK_OUT,
                DepotTransaction::TYPE_ADJUSTMENT,
            ],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.transactions.index'),
                'store' => route('depots.transactions.store'),
                'stockAvailable' => route('depots.stock.available'),
            ],
        ]);
    }

    public function store(StoreReactDepotTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $hintedDepotId = session()->pull('depot_transaction_depot_hint');
        $depotId = $this->resolveTransactionDepotId($hintedDepotId, $request->user());
        $data['depot_id'] = $depotId;

        $this->authorizeDepotPermission('depot.transaction.create', $depotId);

        $itemType = ! empty($data['medicine_id'])
            ? DepotTransaction::ITEM_MEDICINE
            : DepotTransaction::ITEM_TOOL;
        $itemId = (int) ($data['medicine_id'] ?? $data['tool_id']);

        DB::transaction(function () use ($data, $itemType, $itemId) {
            Depot::whereKey($data['depot_id'])->lockForUpdate()->firstOrFail();

            if ($data['type'] === DepotTransaction::TYPE_STOCK_OUT) {
                $this->stockService->lockLedger((int) $data['depot_id'], $itemType, $itemId);
                $this->stockService->ensureAvailable($itemType, (int) $data['depot_id'], $itemId, (int) $data['quantity']);
            }

            DepotTransaction::create([
                ...$data,
                'from_depot_id' => $data['type'] === DepotTransaction::TYPE_STOCK_OUT ? $data['depot_id'] : null,
                'transaction_type' => DepotTransaction::legacyTypeFor($data['type']),
                'status' => DepotTransaction::STATUS_COMPLETED,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('depots.transactions.index')
            ->with('success', localize('global.depot.transaction_created_successfully.'));
    }

    public function show(DepotTransaction $depotTransaction): Response
    {
        $this->authorizeDepotTransactionRecord($depotTransaction, DepotRolePermissions::ACTION_TRANSACTION_VIEW);

        $depotTransaction->load([
            'depot:id,name',
            'fromDepot:id,name',
            'toDepot:id,name',
            'pharmacy:id,name',
            'medicine:id,name',
            'tool:id,name',
            'unit:id,name',
            'createdBy:id,name,last_name',
            'updatedBy:id,name,last_name',
            'depotRequest:id,request_number',
        ]);

        return Inertia::render('Depots/Transactions/Show', [
            'transaction' => $this->transformDetail($depotTransaction),
            'permissions' => $this->depotTransactionPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.transactions.index'),
                'cancel' => route('depots.transactions.cancel', $depotTransaction),
            ],
        ]);
    }

    public function cancel(DepotTransaction $depotTransaction): RedirectResponse
    {
        $this->authorizeDepotTransactionRecord($depotTransaction, DepotRolePermissions::ACTION_TRANSACTION_CREATE);

        if ($depotTransaction->status === DepotTransaction::STATUS_CANCELLED) {
            return redirect()->back()->with('error', localize('global.depot.transaction_already_cancelled.'));
        }

        DB::transaction(function () use ($depotTransaction) {
            $depotTransaction->load('transactionable');

            if ($depotTransaction->transactionable instanceof PharmacyFulfillment) {
                $depotTransaction->transactionable->delete();
            }

            $depotTransaction->update([
                'status' => DepotTransaction::STATUS_CANCELLED,
                'updated_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('depots.transactions.index')
            ->with('success', localize('global.depot.transaction_cancelled_successfully.'));
    }

    public function availableStock(Request $request): JsonResponse
    {
        $request->validate([
            'depot_id' => ['required', 'exists:depots,id'],
            'item_type' => ['nullable', 'in:medicine,tool'],
            'medicine_id' => ['nullable', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'exists:tools,id'],
        ]);

        $this->authorizeDepotStockView((int) $request->depot_id);

        $itemType = $request->get('item_type');
        if (! $itemType) {
            $itemType = $request->filled('tool_id')
                ? DepotTransaction::ITEM_TOOL
                : DepotTransaction::ITEM_MEDICINE;
        }

        if ($itemType === DepotTransaction::ITEM_TOOL) {
            $request->validate(['tool_id' => ['required', 'exists:tools,id']]);

            return response()->json([
                'success' => true,
                'available_stock' => $this->stockService->availableToolStock((int) $request->depot_id, (int) $request->tool_id),
                'item_type' => DepotTransaction::ITEM_TOOL,
            ]);
        }

        $request->validate(['medicine_id' => ['required', 'exists:medicines,id']]);

        return response()->json([
            'success' => true,
            'available_stock' => $this->stockService->availableMedicineStock((int) $request->depot_id, (int) $request->medicine_id),
            'item_type' => DepotTransaction::ITEM_MEDICINE,
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotTransaction>  $query
     */
    private function applyTransactionFilters($query, Request $request): void
    {
        if ($request->filled('depot_id')) {
            $query->forDepot((int) $request->depot_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }
        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
        }
        if ($request->filled('item_type')) {
            if ($request->item_type === DepotTransaction::ITEM_MEDICINE) {
                $query->whereNotNull('medicine_id');
            } elseif ($request->item_type === DepotTransaction::ITEM_TOOL) {
                $query->whereNotNull('tool_id');
            }
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            try {
                $query->whereDate('transaction_date', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }
        if ($request->filled('date_to')) {
            try {
                $query->whereDate('transaction_date', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('medicine', fn ($medicine) => $medicine->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('tool', fn ($tool) => $tool->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('fromDepot', fn ($depot) => $depot->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('toDepot', fn ($depot) => $depot->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pharmacy', fn ($pharmacy) => $pharmacy->where('name', 'like', "%{$search}%"));
            });
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotTransaction>  $query
     */
    private function scopeTransactionsToUserDepots($query, ?\App\Models\User $user = null): void
    {
        if ($this->isDepotSystemAdmin($user)) {
            return;
        }

        $allowedIds = $this->allowedDepotIds($user);

        if (empty($allowedIds)) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($scoped) use ($allowedIds) {
            $scoped->whereIn('depot_id', $allowedIds)
                ->orWhereIn('from_depot_id', $allowedIds)
                ->orWhereIn('to_depot_id', $allowedIds);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function transformListItem(DepotTransaction $tx): array
    {
        return [
            'id' => $tx->id,
            'transaction_number' => $tx->transaction_number,
            'type' => $tx->type,
            'status' => $tx->status,
            'quantity' => $tx->quantity,
            'item_name' => $tx->medicine?->name ?? $tx->tool?->name,
            'item_type' => $tx->medicine_id ? DepotTransaction::ITEM_MEDICINE : ($tx->tool_id ? DepotTransaction::ITEM_TOOL : null),
            'source_name' => $tx->fromDepot?->name ?? $tx->depot?->name,
            'destination_name' => $tx->toDepot?->name ?? $tx->pharmacy?->name,
            'transaction_date' => $this->formatDate($tx->transaction_date),
            'created_by_name' => $tx->createdBy ? trim("{$tx->createdBy->name} {$tx->createdBy->last_name}") : null,
        ];
    }

    private function formatDate(?\Illuminate\Support\Carbon $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return verta($value)->format('Y/m/d');
        } catch (\Throwable) {
            return $value->format('Y-m-d');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(DepotTransaction $tx): array
    {
        return [
            ...$this->transformListItem($tx),
            'batch_number' => $tx->batch_number,
            'unit_name' => $tx->unit?->name,
            'issued_date' => $tx->issued_date?->format('Y-m-d'),
            'expiry_date' => $tx->expiry_date?->format('Y-m-d'),
            'notes' => $tx->notes,
            'depot_name' => $tx->depot?->name,
            'from_depot_name' => $tx->fromDepot?->name,
            'to_depot_name' => $tx->toDepot?->name,
            'pharmacy_name' => $tx->pharmacy?->name,
            'request_number' => $tx->depotRequest?->request_number,
            'created_at' => $tx->created_at?->format('Y-m-d H:i'),
            'updated_by_name' => $tx->updatedBy ? trim("{$tx->updatedBy->name} {$tx->updatedBy->last_name}") : null,
        ];
    }
}
