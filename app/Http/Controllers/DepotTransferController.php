<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepotTransfer\StoreDepotTransferRequest;
use App\Models\Depot;
use App\Models\DepotTransferRequest;
use App\Models\Pharmacy;
use App\Models\Stock;
use App\Services\DepotTransferService;
use App\Services\StockService;
use App\Support\InventoryNav;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepotTransferController extends Controller
{
    public function __construct(
        private readonly DepotTransferService $transfers,
        private readonly StockService $stocks,
    ) {
    }

    public function index(Request $request): Response
    {
        $transfers = DepotTransferRequest::query()
            ->with(['sourceDepot:id,name', 'pharmacy:id,name', 'destinationDepot:id,name'])
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('DepotTransfers/Index', [
            'transfers' => $transfers,
            'filters' => [
                'status' => $request->input('status', ''),
            ],
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('depot-transfers.index'),
                'create' => route('depot-transfers.create'),
                'show' => route('depot-transfers.show', ['depotTransfer' => '__ID__']),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $sourceDepotId = $request->filled('source_depot_id')
            ? (int) $request->input('source_depot_id')
            : null;

        return Inertia::render('DepotTransfers/Create', [
            'formData' => $this->formData($sourceDepotId),
            'defaults' => [
                'source_depot_id' => $sourceDepotId,
                'pharmacy_id' => $request->input('pharmacy_id'),
            ],
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'store' => route('depot-transfers.store'),
                'index' => route('depot-transfers.index'),
                'back' => route('depot-transfers.index'),
                'availableStock' => route('stocks.available'),
                'stockMedicines' => route('stocks.medicines'),
            ],
        ]);
    }

    public function store(StoreDepotTransferRequest $request)
    {
        $transfer = $this->transfers->create(
            $request->safe()->except('items'),
            $request->validated('items')
        );

        return redirect()
            ->route('depot-transfers.show', $transfer)
            ->with('success', 'Transfer request created.');
    }

    public function show(DepotTransferRequest $depotTransfer): Response
    {
        $depotTransfer->load([
            'sourceDepot:id,name',
            'pharmacy:id,name',
            'destinationDepot:id,name',
            'items.medicine:id,name,units_per_pack',
            'items.fromStock',
            'items.toStock',
            'creator:id,name',
        ]);

        return Inertia::render('DepotTransfers/Show', [
            'transfer' => $depotTransfer,
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('depot-transfers.index'),
                'submit' => route('depot-transfers.submit', $depotTransfer),
                'approve' => route('depot-transfers.approve', $depotTransfer),
                'reject' => route('depot-transfers.reject', $depotTransfer),
                'fulfill' => route('depot-transfers.fulfill', $depotTransfer),
                'cancel' => route('depot-transfers.cancel', $depotTransfer),
            ],
        ]);
    }

    public function submit(DepotTransferRequest $depotTransfer)
    {
        $this->transfers->submit($depotTransfer);

        return back()->with('success', 'Transfer submitted.');
    }

    public function approve(DepotTransferRequest $depotTransfer)
    {
        $this->transfers->approve($depotTransfer);

        return back()->with('success', 'Transfer approved and stock moved to destination.');
    }

    public function reject(Request $request, DepotTransferRequest $depotTransfer)
    {
        $this->transfers->reject($depotTransfer, $request->input('rejection_reason'));

        return back()->with('success', 'Transfer rejected.');
    }

    public function fulfill(DepotTransferRequest $depotTransfer)
    {
        $this->transfers->fulfill($depotTransfer);

        return back()->with('success', 'Transfer fulfilled — depot stock deducted and destination increased.');
    }

    public function cancel(DepotTransferRequest $depotTransfer)
    {
        $this->transfers->cancel($depotTransfer);

        return back()->with('success', 'Transfer cancelled.');
    }

    /**
     * @return array{depots: mixed, pharmacies: mixed, medicines: list<array{id: int, name: string, units_per_pack: int, total_qty: int}>}
     */
    private function formData(?int $sourceDepotId = null): array
    {
        $medicines = $sourceDepotId
            ? $this->stocks->medicinesForLocation(Stock::LOCATION_DEPOT, $sourceDepotId)
            : [];

        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name']),
            'medicines' => $medicines,
        ];
    }
}
