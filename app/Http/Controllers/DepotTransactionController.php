<?php

namespace App\Http\Controllers;

use App\Http\Requests\Depot\StoreDepotToDepotRequest;
use App\Http\Requests\Depot\StoreDepotToPharmacyRequest;
use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use App\Models\Tool;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepotTransactionController extends Controller
{
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
                    ->orWhereHas('tool', fn ($tool) => $tool->where('id', $search))
                    ->orWhereHas('fromDepot', fn ($depot) => $depot->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('toDepot', fn ($depot) => $depot->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('pharmacy', fn ($pharmacy) => $pharmacy->where('name', 'like', "%{$search}%"));
            });
        }

        $transactions = $query->latest('transaction_date')->latest('id')->paginate(15)->withQueryString();

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'depot_id' => ['required', 'exists:depots,id'],
            'medicine_id' => ['required', 'exists:medicines,id'],
            'tool_id' => ['nullable', 'exists:tools,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'batch_number' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:stock_in,stock_out,adjustment'],
            'quantity' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['nullable', 'date'],
            'issued_date' => ['nullable', 'date'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($data) {
            Depot::whereKey($data['depot_id'])->lockForUpdate()->firstOrFail();

            if ($data['type'] === DepotTransaction::TYPE_STOCK_OUT) {
                $this->lockDepotMedicineLedger((int) $data['depot_id'], (int) $data['medicine_id']);
                $this->ensureAvailableStock((int) $data['depot_id'], (int) $data['medicine_id'], (int) $data['quantity']);
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
            ->with('success', 'Depot transaction created successfully.');
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
            return redirect()->back()->with('error', 'Transaction is already cancelled.');
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
            ->with('success', 'Depot transaction cancelled successfully.');
    }

    public function depotToDepot()
    {
        return view('pages.depots.movements.depot-to-depot', $this->formData());
    }

    public function storeDepotToDepot(StoreDepotToDepotRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            Depot::whereKey($data['from_depot_id'])->lockForUpdate()->firstOrFail();
            Depot::whereKey($data['to_depot_id'])->lockForUpdate()->firstOrFail();
            $this->lockDepotMedicineLedger((int) $data['from_depot_id'], (int) $data['medicine_id']);
            $this->ensureAvailableStock((int) $data['from_depot_id'], (int) $data['medicine_id'], (int) $data['quantity']);

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
            ->with('success', 'Depot to depot movement completed successfully.');
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
            $this->lockDepotMedicineLedger((int) $data['from_depot_id'], (int) $data['medicine_id']);
            $this->ensureAvailableStock((int) $data['from_depot_id'], (int) $data['medicine_id'], (int) $data['quantity']);

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
            ->with('success', 'Depot to pharmacy movement completed successfully.');
    }

    public function stock(Request $request)
    {
        $request->validate([
            'depot_id' => ['required', 'exists:depots,id'],
            'medicine_id' => ['required', 'exists:medicines,id'],
        ]);

        return response()->json([
            'available_stock' => DepotTransaction::availableStock((int) $request->depot_id, (int) $request->medicine_id),
        ]);
    }

    private function formData(): array
    {
        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(),
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(),
            'tools' => Tool::query()->orderBy('id')->get(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function lockDepotMedicineLedger(int $depotId, int $medicineId): void
    {
        DepotTransaction::query()
            ->where('medicine_id', $medicineId)
            ->where(function ($query) use ($depotId) {
                $query->where('depot_id', $depotId)
                    ->orWhere('from_depot_id', $depotId)
                    ->orWhere('to_depot_id', $depotId);
            })
            ->lockForUpdate()
            ->get(['id']);
    }

    private function ensureAvailableStock(int $depotId, int $medicineId, int $quantity): void
    {
        $available = DepotTransaction::availableStock($depotId, $medicineId);

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient depot stock. Available quantity is {$available}.",
            ]);
        }
    }
}
