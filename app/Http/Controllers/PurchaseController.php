<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\StorePurchaseRequest;
use App\Models\Depot;
use App\Models\Medicine;
use App\Models\MedicineGramMl;
use App\Models\MedicineType;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Unit;
use App\Services\PurchaseService;
use App\Support\InventoryNav;
use Illuminate\Http\JsonResponse;
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
                'quickSupplier' => route('purchases.quick-supplier'),
                'quickMedicine' => route('purchases.quick-medicine'),
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
        $purchase->load([
            'depot:id,name',
            'items.medicine.medicineType:id,type',
            'items.medicine.medicineGramMl:id,name,ml',
            'items.stock',
            'creator:id,name',
            'receiver:id,name',
        ]);

        return Inertia::render('Purchases/Show', [
            'purchase' => [
                'id' => $purchase->id,
                'purchase_number' => $purchase->purchase_number,
                'supplier' => $purchase->supplier,
                'purchase_date' => $purchase->purchase_date?->toDateString(),
                'status' => $purchase->status,
                'notes' => $purchase->notes,
                'depot' => $purchase->depot,
                'items' => $purchase->items->map(fn ($item) => [
                    'id' => $item->id,
                    'batch_number' => $item->batch_number,
                    'expiry_date' => $item->expiry_date?->toDateString(),
                    'packs' => $item->packs,
                    'units' => $item->units,
                    'qty_units' => $item->qty_units,
                    'units_per_pack' => $item->units_per_pack,
                    'unit_sale_price' => $item->unit_sale_price,
                    'pack_price' => $item->pack_price,
                    'medicine' => $item->medicine ? [
                        'id' => $item->medicine->id,
                        'name' => $item->medicine->name,
                        'label' => $this->medicineLabel($item->medicine),
                    ] : null,
                ]),
            ],
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
            ->with('success', 'Purchase finalized into depot stock.');
    }

    public function cancel(Purchase $purchase)
    {
        $this->purchases->cancel($purchase);

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Purchase cancelled.');
    }

    public function quickSupplier(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $supplier = Supplier::query()->create([
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'is_active' => true,
        ]);

        return response()->json([
            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
            ],
        ]);
    }

    public function quickMedicine(Request $request): JsonResponse
    {
        $this->authorize('create', Medicine::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:192'],
            'medicine_type_id' => ['nullable', 'integer', 'exists:medicine_types,id'],
            'medicine_gram_ml_id' => ['nullable', 'integer', 'exists:medicine_gram_mls,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'units_per_pack' => ['nullable', 'integer', 'min:1'],
        ]);

        $medicine = Medicine::query()->create([
            'name' => $data['name'],
            'medicine_type_id' => $data['medicine_type_id'] ?? null,
            'medicine_gram_ml_id' => $data['medicine_gram_ml_id'] ?? null,
            'unit_id' => $data['unit_id'] ?? null,
            'units_per_pack' => max(1, (int) ($data['units_per_pack'] ?? 1)),
        ]);

        $medicine->load(['medicineType:id,type', 'medicineGramMl:id,name,ml']);

        return response()->json([
            'medicine' => [
                'id' => $medicine->id,
                'name' => $medicine->name,
                'label' => $this->medicineLabel($medicine),
                'unit_id' => $medicine->unit_id,
                'units_per_pack' => $medicine->unitsPerPack(),
            ],
        ]);
    }

    private function formData(): array
    {
        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'medicines' => Medicine::query()
                ->with(['medicineType:id,type', 'medicineGramMl:id,name,ml'])
                ->orderBy('name')
                ->get(['id', 'name', 'unit_id', 'units_per_pack', 'medicine_type_id', 'medicine_gram_ml_id'])
                ->map(fn (Medicine $m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'label' => $this->medicineLabel($m),
                    'unit_id' => $m->unit_id,
                    'units_per_pack' => $m->unitsPerPack(),
                ]),
            'types' => MedicineType::query()->orderBy('type')->get(['id', 'type']),
            'gramMls' => MedicineGramMl::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'ml']),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'symbol']),
        ];
    }

    private function medicineLabel(Medicine $medicine): string
    {
        $parts = array_values(array_filter([
            $medicine->name,
            $medicine->medicineGramMl?->name
                ?: ($medicine->medicineGramMl?->ml !== null ? (string) $medicine->medicineGramMl->ml : null),
            $medicine->medicineType?->type,
        ], fn ($part) => filled($part)));

        return implode('-', $parts);
    }
}
