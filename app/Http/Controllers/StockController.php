<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Pharmacy;
use App\Models\Stock;
use App\Services\StockService;
use App\Support\InventoryNav;
use App\Support\PackQuantity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockController extends Controller
{
    public function __construct(
        private readonly StockService $stocks,
    ) {
    }

    public function index(Request $request): Response
    {
        $locationType = $request->input('location_type');
        $locationId = $request->filled('location_id') ? $request->integer('location_id') : null;
        $search = $request->input('search');
        $batchNumber = $request->input('batch_number');

        if ($batchNumber) {
            $rows = $this->stocks->listByBatchNumber($batchNumber);
        } else {
            $rows = $this->stocks->listForLocation(
                $locationType ?: null,
                $locationId,
                $search
            );
        }

        $depots = Depot::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $pharmacies = Pharmacy::query()->orderBy('name')->get(['id', 'name']);

        $payload = $rows->map(function (Stock $stock) use ($depots, $pharmacies) {
            $split = PackQuantity::fromUnits((int) $stock->qty_on_hand, (int) $stock->units_per_pack);
            $locationName = match ($stock->location_type) {
                Stock::LOCATION_DEPOT => $depots->firstWhere('id', $stock->location_id)?->name,
                Stock::LOCATION_PHARMACY => $pharmacies->firstWhere('id', $stock->location_id)?->name,
                default => null,
            };

            return [
                'id' => $stock->id,
                'batch_number' => $stock->batch_number,
                'medicine' => $stock->medicine?->only(['id', 'name']),
                'location_type' => $stock->location_type,
                'location_id' => $stock->location_id,
                'location_name' => $locationName ?? $stock->locationLabel(),
                'qty_on_hand' => $stock->qty_on_hand,
                'qty_sold' => $stock->qty_sold,
                'packs_on_hand' => $split['packs'],
                'units_on_hand' => $split['units'],
                'units_per_pack' => $stock->units_per_pack,
                'expiry_date' => optional($stock->expiry_date)?->format('Y-m-d'),
                'purchase_price' => (float) $stock->purchase_price,
                'unit_sale_price' => (float) $stock->unit_sale_price,
                'pack_price' => (float) $stock->pack_price,
            ];
        });

        return Inertia::render('Stocks/Index', [
            'stocks' => $payload,
            'filters' => [
                'location_type' => $locationType ?? '',
                'location_id' => $locationId ? (string) $locationId : '',
                'search' => $search ?? '',
                'batch_number' => $batchNumber ?? '',
            ],
            'filterOptions' => [
                'depots' => $depots,
                'pharmacies' => $pharmacies,
            ],
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('stocks.index'),
                'batch' => route('stocks.batch', ['batchNumber' => '__BATCH__']),
            ],
        ]);
    }

    public function batch(string $batchNumber): Response
    {
        $request = request()->merge(['batch_number' => $batchNumber]);

        return $this->index($request);
    }

    public function available(Request $request)
    {
        $request->validate([
            'location_type' => ['required', 'in:depot,pharmacy'],
            'location_id' => ['required', 'integer'],
            'medicine_id' => ['required', 'integer', 'exists:medicines,id'],
        ]);

        return response()->json([
            'available' => $this->stocks->availableForLocation(
                $request->string('location_type')->toString(),
                $request->integer('location_id'),
                $request->integer('medicine_id')
            ),
        ]);
    }
}
