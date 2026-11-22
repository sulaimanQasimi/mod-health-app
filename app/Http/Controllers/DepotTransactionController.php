<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotToDepotRequest;
use App\Http\Requests\Depot\StoreDepotToPharmacyRequest;
use App\Http\Requests\Depot\StoreDepotTransactionRequest;
use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;
use App\Services\DepotStockService;
use App\Support\DepotRolePermissions;
use Carbon\Carbon;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DepotTransactionController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    private const FILTER_KEYS = [
        'search', 'depot_id', 'pharmacy_id', 'medicine_id', 'tool_id', 'item_type', 'type', 'status', 'date_from', 'date_to', 'per_page',
    ];

    public function __construct(
        private readonly DepotStockService $stockService
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeDepotPermission('depot.transaction.view');

        $query = DepotTransaction::with([
            'depot',
            'fromDepot',
            'toDepot',
            'pharmacy',
            'medicine',
            'tool',
            'unit',
            'createdBy',
            'depotRequest',
        ]);

        $this->applyTransactionFilters($query, $request);
        $this->scopeTransactionsToUserDepots($query, $request->user());

        $perPage = (int) $request->get('per_page', 15);
        if (! in_array($perPage, [10, 15, 25, 50], true)) {
            $perPage = 15;
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate($perPage)->appends($request->query());

        return view('pages.depots.transactions.index', array_merge($this->formData(), [
            'transactions' => $transactions,
            'types' => DepotTransaction::types(),
            'statuses' => DepotTransaction::statuses(),
            'typeOptions' => depot_label_options(DepotTransaction::types(), 'type'),
            'statusOptions' => depot_label_options(DepotTransaction::statuses()),
            'filters' => collect(self::FILTER_KEYS)
                ->mapWithKeys(fn (string $key) => [$key => (string) $request->query($key, '')])
                ->all(),
            'permissions' => $this->depotTransactionPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]));
    }

    public function create(Request $request): View
    {
        $this->authorizeDepotPermission('depot.transaction.create');

        $hintedDepotId = $this->validatedTransactionDepotHint($request);

        $depotPreview = $this->transactionDepotPreview($hintedDepotId, $request->user());

        return view('pages.depots.transactions.create', array_merge($this->formData(), [
            'depot' => $depotPreview,
            'defaults' => [
                'type' => DepotTransaction::TYPE_ADJUSTMENT,
            ],
            'formData' => $this->depotFormOptions(),
            'types' => $createTypes = [
                DepotTransaction::TYPE_STOCK_IN,
                DepotTransaction::TYPE_STOCK_OUT,
                DepotTransaction::TYPE_ADJUSTMENT,
            ],
            'typeOptions' => depot_label_options($createTypes, 'type'),
            'selectedDepotId' => old('depot_id', $request->input('depot_id', $depotPreview['id'] ?? '')),
            'permissions' => $this->depotTransactionPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]));
    }

    public function store(StoreDepotTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $depotId = (int) $data['depot_id'];

        $this->authorizeDepotPermission('depot.transaction.create', $depotId);
        $this->authorizeDepotMembership($depotId);
        session()->forget('depot_transaction_depot_hint');

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

        return redirect()->route('depots.transactions.index')
            ->with('success', localize('global.depot.transaction_created_successfully.'));
    }

    public function show(DepotTransaction $depotTransaction): View
    {
        $this->authorizeDepotTransactionRecord($depotTransaction, DepotRolePermissions::ACTION_TRANSACTION_VIEW);

        $depotTransaction->load([
            'depot',
            'fromDepot',
            'toDepot',
            'pharmacy',
            'medicine',
            'tool',
            'unit',
            'createdBy',
            'updatedBy',
            'transactionable',
            'depotRequest',
        ]);

        $depotId = $depotTransaction->primaryDepotId();

        return view('pages.depots.transactions.show', [
            'depotTransaction' => $depotTransaction,
            'permissions' => $this->depotTransactionPermissions($depotId),
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
        ]);
    }

    public function destroy(DepotTransaction $depotTransaction): RedirectResponse
    {
        return $this->cancel($depotTransaction);
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

        return redirect()->route('depots.transactions.index')
            ->with('success', localize('global.depot.transaction_cancelled_successfully.'));
    }

    public function depotToDepot()
    {
        return view('pages.depots.movements.depot-to-depot', $this->formData());
    }

    public function storeDepotToDepot(StoreDepotToDepotRequest $request)
    {
        $data = $request->validated();
        $itemType = ! empty($data['medicine_id'])
            ? DepotTransaction::ITEM_MEDICINE
            : DepotTransaction::ITEM_TOOL;
        $itemId = (int) ($data['medicine_id'] ?? $data['tool_id']);

        DB::transaction(function () use ($data, $itemType, $itemId) {
            Depot::whereKey($data['from_depot_id'])->lockForUpdate()->firstOrFail();
            Depot::whereKey($data['to_depot_id'])->lockForUpdate()->firstOrFail();

            $this->stockService->lockLedger((int) $data['from_depot_id'], $itemType, $itemId);
            $this->stockService->ensureAvailable($itemType, (int) $data['from_depot_id'], $itemId, (int) $data['quantity']);

            DepotTransaction::create([
                ...$data,
                'depot_id' => $data['from_depot_id'],
                'type' => DepotTransaction::TYPE_DEPOT_TO_DEPOT,
                'transaction_type' => 'transfer',
                'status' => DepotTransaction::STATUS_COMPLETED,
                'user_id' => Auth::id(),
            ]);
        });

        return redirect()->route('depots.transactions.index')
            ->with('success', localize('global.depot.depot_to_depot_completed_successfully.'));
    }

    public function depotToPharmacy()
    {
        return view('pages.depots.movements.depot-to-pharmacy', $this->formData());
    }

    public function storeDepotToPharmacy(StoreDepotToPharmacyRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Depot::whereKey($data['from_depot_id'])->lockForUpdate()->firstOrFail();
            $this->stockService->lockLedger((int) $data['from_depot_id'], DepotTransaction::ITEM_MEDICINE, (int) $data['medicine_id']);
            $this->stockService->ensureAvailable(DepotTransaction::ITEM_MEDICINE, (int) $data['from_depot_id'], (int) $data['medicine_id'], (int) $data['quantity']);

            $transactionNumber = DepotTransaction::nextTransactionNumber();
            $transactionDate = $data['transaction_date'] ?? now()->toDateString();

            $fulfillment = PharmacyFulfillment::create([
                'medicine_id' => $data['medicine_id'],
                'unit_type' => $data['unit_id'] ? optional(Unit::find($data['unit_id']))->name : null,
                'amount' => (string) $data['quantity'],
                'form_no' => $transactionNumber,
                'date' => $transactionDate,
                'pharmacy_id' => $data['pharmacy_id'],
                'user_id' => Auth::id(),
            ]);

            DepotTransaction::create([
                ...$data,
                'transaction_number' => $transactionNumber,
                'depot_id' => $data['from_depot_id'],
                'type' => DepotTransaction::TYPE_DEPOT_TO_PHARMACY,
                'transaction_type' => 'out',
                'status' => DepotTransaction::STATUS_COMPLETED,
                'user_id' => Auth::id(),
                'transactionable_type' => PharmacyFulfillment::class,
                'transactionable_id' => $fulfillment->id,
            ]);
        });

        return redirect()->route('depots.transactions.index')
            ->with('success', localize('global.depot.depot_to_pharmacy_completed_successfully.'));
    }

    public function stock(Request $request): JsonResponse
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
        if ($request->filled('date_from') && ($from = $this->parseFilterDate((string) $request->date_from))) {
            $query->whereDate('transaction_date', '>=', $from);
        }
        if ($request->filled('date_to') && ($to = $this->parseFilterDate((string) $request->date_to))) {
            $query->whereDate('transaction_date', '<=', $to);
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
     * Accepts Jalali (Verta) or Gregorian input and returns a Gregorian date.
     */
    private function parseFilterDate(string $value): ?Carbon
    {
        $value = trim($value);

        try {
            if (preg_match('/^(19|20)\d{2}[-\/]/', $value)) {
                return Carbon::parse($value);
            }

            return Carbon::instance(Verta::parse($value)->datetime());
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotTransaction>  $query
     */
    private function scopeTransactionsToUserDepots($query, ?User $user = null): void
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

    private function formData(): array
    {
        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(),
            'tools' => Tool::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }
}
