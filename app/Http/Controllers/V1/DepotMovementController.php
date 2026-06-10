<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotToPharmacyRequest;
use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\PharmacyFulfillment;
use App\Models\Unit;
use App\Services\DepotStockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DepotMovementController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function __construct(
        private readonly DepotStockService $stockService,
    ) {}

    public function depotToDepot(Request $request): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.create');

        return redirect()->route('react.depots.requests.create', [
            'source_depot_id' => $request->query('from_depot_id'),
            'requesting_depot_id' => $request->query('to_depot_id'),
        ]);
    }

    public function depotToPharmacy(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.movement.depot_to_pharmacy');

        return Inertia::render('Depots/Movements/DepotToPharmacy', [
            'defaults' => [
                'from_depot_id' => (string) $request->query('from_depot_id', ''),
                'pharmacy_id' => (string) $request->query('pharmacy_id', ''),
            ],
            'formData' => $this->depotFormOptions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'store' => route('react.depots.movements.depot-to-pharmacy.store'),
                'transactions' => route('react.depots.transactions.index'),
                'stockAvailable' => route('react.depots.stock.available'),
            ],
        ]);
    }

    public function storeDepotToPharmacy(StoreDepotToPharmacyRequest $request): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.movement.depot_to_pharmacy');

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

        return redirect()
            ->route('react.depots.transactions.index')
            ->with('success', localize('global.depot.depot_to_pharmacy_completed_successfully.'));
    }
}
