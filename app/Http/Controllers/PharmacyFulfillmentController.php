<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Stock;
use App\Support\InventoryNav;
use App\Support\PackQuantity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyFulfillmentController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const STOCK_FILTER_KEYS = [
        'search', 'medicine_id', 'pharmacy_id', 'stock_status', 'batch_number', 'per_page',
    ];

    private const LOW_STOCK_THRESHOLD = 10;

    public function stock(Request $request): Response
    {
        $user = $request->user();
        $this->authorizePharmacyFulfillment($user);

        $allowedPharmacyIds = $this->allowedPharmacyIds(
            $user,
            $request->filled('pharmacy_id') ? (int) $request->pharmacy_id : null,
        );

        $emptyStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_batches' => 0,
            'total_low_stock' => 0,
            'total_out_of_stock' => 0,
        ];

        if ($allowedPharmacyIds === []) {
            return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
                $request,
                $user,
                new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                $emptyStats,
                collect(),
            ));
        }

        $query = Stock::query()
            ->with(['medicine:id,name'])
            ->where('location_type', Stock::LOCATION_PHARMACY)
            ->whereIn('location_id', $allowedPharmacyIds);

        if ($request->filled('search')) {
            $search = (string) $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('medicine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', (int) $request->medicine_id);
        }

        if ($request->filled('batch_number')) {
            $query->where('batch_number', 'like', '%'.$request->string('batch_number').'%');
        }

        if ($request->stock_status === 'out_of_stock') {
            $query->where('qty_on_hand', '<=', 0)->where('qty_sold', '>', 0);
        } elseif ($request->stock_status === 'low_stock') {
            $query->where('qty_on_hand', '>', 0)
                ->where('qty_on_hand', '<=', self::LOW_STOCK_THRESHOLD);
        } elseif ($request->stock_status === 'in_stock') {
            $query->where('qty_on_hand', '>', self::LOW_STOCK_THRESHOLD);
        } else {
            $query->where(function ($q) {
                $q->where('qty_on_hand', '>', 0)->orWhere('qty_sold', '>', 0);
            });
        }

        $statsBase = Stock::query()
            ->where('location_type', Stock::LOCATION_PHARMACY)
            ->whereIn('location_id', $allowedPharmacyIds);

        $stockStats = [
            'total_items' => (int) (clone $statsBase)
                ->where('qty_on_hand', '>', 0)
                ->selectRaw('count(distinct medicine_id) as aggregate')
                ->value('aggregate'),
            'total_stock' => (int) (clone $statsBase)->sum('qty_on_hand'),
            'total_batches' => (int) (clone $statsBase)->where('qty_on_hand', '>', 0)->count(),
            'total_low_stock' => (int) (clone $statsBase)
                ->where('qty_on_hand', '>', 0)
                ->where('qty_on_hand', '<=', self::LOW_STOCK_THRESHOLD)
                ->count(),
            'total_out_of_stock' => (int) (clone $statsBase)
                ->where('qty_on_hand', '<=', 0)
                ->where('qty_sold', '>', 0)
                ->count(),
        ];

        $paginator = $this->paginateQuery(
            $query->orderByDesc('qty_on_hand')->orderBy('batch_number'),
            $request,
            20,
        );

        $pharmacies = Pharmacy::query()
            ->whereIn('id', $allowedPharmacyIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->keyBy('id');

        return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
            $request,
            $user,
            $paginator,
            $stockStats,
            $pharmacies,
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int|string, Pharmacy>  $pharmacies
     * @return array<string, mixed>
     */
    private function stockPagePayload(
        Request $request,
        \App\Models\User $user,
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        array $stockStats,
        $pharmacies,
    ): array {
        $pharmacyIds = $pharmacies->keys()->map(fn ($id) => (int) $id)->all();

        $medicineIds = $pharmacyIds === []
            ? []
            : Stock::query()
                ->where('location_type', Stock::LOCATION_PHARMACY)
                ->whereIn('location_id', $pharmacyIds)
                ->distinct()
                ->pluck('medicine_id')
                ->filter()
                ->all();

        return [
            'stockItems' => $this->paginationPayload($paginator, function (Stock $stock) use ($pharmacies) {
                $split = PackQuantity::fromUnits((int) $stock->qty_on_hand, (int) $stock->units_per_pack);
                $qty = (int) $stock->qty_on_hand;
                $status = $qty <= 0
                    ? 'out_of_stock'
                    : ($qty <= self::LOW_STOCK_THRESHOLD ? 'low_stock' : 'in_stock');

                return [
                    'id' => $stock->id,
                    'medicine_id' => $stock->medicine_id,
                    'medicine_name' => $stock->medicine?->name,
                    'pharmacy_id' => $stock->location_id,
                    'pharmacy_name' => $pharmacies->get($stock->location_id)?->name,
                    'batch_number' => $stock->batch_number,
                    'qty_on_hand' => $qty,
                    'qty_sold' => (int) $stock->qty_sold,
                    'packs' => $split['packs'],
                    'units' => $split['units'],
                    'units_per_pack' => (int) $stock->units_per_pack,
                    'expiry_date' => optional($stock->expiry_date)?->format('Y-m-d'),
                    'unit_sale_price' => (float) $stock->unit_sale_price,
                    'pack_price' => (float) $stock->pack_price,
                    'stock_status' => $status,
                ];
            }),
            'stockStats' => $stockStats,
            'filters' => $this->collectFilters($request, self::STOCK_FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => $this->isPharmacyAdmin($user)
                    ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                    : $user->activePharmacies->map(fn (Pharmacy $pharmacy) => [
                        'id' => $pharmacy->id,
                        'name' => $pharmacy->name,
                    ])->values()->all(),
                'medicines' => $medicineIds === []
                    ? []
                    : Medicine::query()->whereIn('id', $medicineIds)->orderBy('name')->get(['id', 'name']),
                'stockStatuses' => ['in_stock', 'low_stock', 'out_of_stock'],
            ],
            'userPharmacies' => $user->activePharmacies->map(fn (Pharmacy $pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all(),
            'inventoryNav' => InventoryNav::urls(),
            'urls' => [
                'index' => route('pharmacy-fulfillments.stock'),
                'transfers' => route('depot-transfers.index'),
                'stocks' => route('stocks.index', ['location_type' => 'pharmacy']),
                'outcomes' => route('outcomes.index'),
            ],
        ];
    }
}
