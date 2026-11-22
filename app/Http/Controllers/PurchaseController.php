<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Depot;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
use App\Support\InventoryNav;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseController extends Controller
{
    public function __construct(
        private readonly PurchaseService $purchases,
    ) {
    }

    public function index(Request $request): Response
    {
        $purchases = Purchase::query()
            ->with(['depot:id,name', 'creator:id,name'])
            ->withCount('items')
            ->when($request->string('search')->toString(), function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('purchase_number', 'like', "%{$search}%")
                        ->orWhere('supplier', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('depot_id'), fn ($q) => $q->where('depot_id', $request->integer('depot_id')))
            ->latest('id')
            ->paginate((int) $request->input('per_page', 15))
            ->withQueryString();

        return Inertia::render('Purchases/Index', [
            'purchases' => $purchases,
            'filters' => [
                'search' => $request->input('search', ''),
                'status' => $request->input('status', ''),
                'depot_id' => $request->input('depot_id', ''),
                'per_page' => (string) $request->input('per_page', '15'),
            ],
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('purchases.index'),
                'create' => route('purchases.create'),
                'show' => route('purchases.show', ['purchase' => '__ID__']),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Purchases/Create', [
            'formData' => $this->formData(),
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'store' => route('purchases.store'),
                'index' => route('purchases.index'),
                'back' => route('purchases.index'),
            ],
        ]);
    }

    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchases->create($request->safe()->except('items'), $request->validated('items'));

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase saved as draft.');
    }

    public function show(Purchase $purchase): Response
    {
        $purchase->load(['depot:id,name', 'items.medicine:id,name,units_per_pack', 'items.stock', 'creator:id,name', 'receiver:id,name']);

        return Inertia::render('Purchases/Show', [
            'purchase' => $purchase,
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('purchases.index'),
                'receive' => route('purchases.receive', $purchase),
                'cancel' => route('purchases.cancel', $purchase),
            ],
        ]);
    }

    public function receive(Purchase $purchase)
    {
        $this->purchases->receive($purchase);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase received into depot stock.');
    }

    public function cancel(Purchase $purchase)
    {
        $this->purchases->cancel($purchase);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase cancelled.');
    }

    private function formData(): array
    {
        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'medicines' => Medicine::query()
                ->orderBy('name')
                ->get(['id', 'name', 'unit_id', 'units_per_pack'])
                ->map(fn (Medicine $m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'unit_id' => $m->unit_id,
                    'units_per_pack' => $m->unitsPerPack(),
                ]),
        ];
    }
}
