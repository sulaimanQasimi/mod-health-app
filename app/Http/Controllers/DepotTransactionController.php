<?php

namespace App\Http\Controllers;

use App\Http\Requests\Depot\StoreDepotToDepotRequest;
use App\Http\Requests\Depot\StoreDepotToPharmacyRequest;
use App\Http\Requests\Depot\StoreDepotTransactionRequest;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use App\Models\Tool;
use App\Models\Unit;
use App\Services\DepotStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepotTransactionController extends Controller
{
    public function __construct(
        private readonly DepotStockService $stockService
    ) {
    }

    public function index(Request $request)
    {
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
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
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

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(15)->appends($request->query());

        return view('pages.depots.transactions.index', array_merge($this->formData(), [
            'transactions' => $transactions,
            'types' => DepotTransaction::types(),
            'statuses' => DepotTransaction::statuses(),
        ]));
    }

    public function create()
    {
        return view('pages.depots.transactions.create', array_merge($this->formData(), [
            'types' => [
                DepotTransaction::TYPE_STOCK_IN,
                DepotTransaction::TYPE_STOCK_OUT,
                DepotTransaction::TYPE_ADJUSTMENT,
            ],
        ]));
    }

    public function store(StoreDepotTransactionRequest $request)
    {
        $data = $request->validated();
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

    public function show(DepotTransaction $depotTransaction)
    {
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

        return view('pages.depots.transactions.show', compact('depotTransaction'));
    }

    public function destroy(DepotTransaction $depotTransaction)
    {
        return $this->cancel($depotTransaction);
    }

    public function cancel(DepotTransaction $depotTransaction)
    {
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

            $transactionDate = $data['transaction_date'] ?? now()->toDateString();

            foreach ($data['items'] as $item) {
                $medicineId = (int) $item['medicine_id'];
                $quantity = (int) $item['quantity'];

                $this->stockService->lockLedger((int) $data['from_depot_id'], DepotTransaction::ITEM_MEDICINE, $medicineId);
                $this->stockService->ensureAvailable(
                    DepotTransaction::ITEM_MEDICINE,
                    (int) $data['from_depot_id'],
                    $medicineId,
                    $quantity
                );

                $transactionNumber = DepotTransaction::nextTransactionNumber();

                $fulfillment = PharmacyFulfillment::create([
                    'medicine_id' => $medicineId,
                    'unit_type' => ! empty($item['unit_id']) ? optional(Unit::find($item['unit_id']))->name : null,
                    'amount' => (string) $quantity,
                    'form_no' => $transactionNumber,
                    'date' => $transactionDate,
                    'pharmacy_id' => $data['pharmacy_id'],
                    'user_id' => Auth::id(),
                ]);

                DepotTransaction::create([
                    'from_depot_id' => $data['from_depot_id'],
                    'depot_id' => $data['from_depot_id'],
                    'pharmacy_id' => $data['pharmacy_id'],
                    'medicine_id' => $medicineId,
                    'unit_id' => $item['unit_id'] ?? null,
                    'batch_number' => $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $quantity,
                    'transaction_date' => $transactionDate,
                    'notes' => $data['notes'] ?? null,
                    'transaction_number' => $transactionNumber,
                    'type' => DepotTransaction::TYPE_DEPOT_TO_PHARMACY,
                    'transaction_type' => 'out',
                    'status' => DepotTransaction::STATUS_COMPLETED,
                    'user_id' => Auth::id(),
                    'transactionable_type' => PharmacyFulfillment::class,
                    'transactionable_id' => $fulfillment->id,
                ]);
            }
        });

        return redirect()->route('depots.transactions.index')
            ->with('success', localize('global.depot.depot_to_pharmacy_completed_successfully.'));
    }

    public function stock(Request $request)
    {
        $request->validate([
            'depot_id' => ['required', 'exists:depots,id'],
            'item_type' => ['nullable', 'in:medicine,tool'],
            'medicine_id' => ['nullable', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'exists:tools,id'],
        ]);

        $itemType = $request->get('item_type');
        if (! $itemType) {
            $itemType = $request->filled('tool_id')
                ? DepotTransaction::ITEM_TOOL
                : DepotTransaction::ITEM_MEDICINE;
        }

        if ($itemType === DepotTransaction::ITEM_TOOL) {
            $request->validate(['tool_id' => ['required', 'exists:tools,id']]);

            return response()->json([
                'available_stock' => $this->stockService->availableToolStock((int) $request->depot_id, (int) $request->tool_id),
                'item_type' => DepotTransaction::ITEM_TOOL,
            ]);
        }

        $request->validate(['medicine_id' => ['required', 'exists:medicines,id']]);

        return response()->json([
            'available_stock' => $this->stockService->availableMedicineStock((int) $request->depot_id, (int) $request->medicine_id),
            'item_type' => DepotTransaction::ITEM_MEDICINE,
        ]);
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
