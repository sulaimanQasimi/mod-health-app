<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PharmacyFulfillmentController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const STOCK_FILTER_KEYS = [
        'search', 'medicine_id', 'pharmacy_id', 'stock_status', 'sort_by', 'sort_order', 'per_page',
    ];

    public function stock(Request $request): Response
    {
        $user = $request->user();
        $this->authorizePharmacyFulfillment($user);

        $allowedPharmacyIds = $this->allowedPharmacyIds(
            $user,
            $request->filled('pharmacy_id') ? (int) $request->pharmacy_id : null,
        );

        $stockStats = [
            'total_items' => 0,
            'total_stock' => 0,
            'total_income' => 0,
            'total_outcome' => 0,
            'total_low_stock' => 0,
            'total_out_of_stock' => 0,
        ];

        if (empty($allowedPharmacyIds)) {
            return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
                $request,
                $user,
                new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
                $stockStats,
            ));
        }

        $pharmacyMorph = DB::getPdo()->quote((new Pharmacy)->getMorphClass());

        $baseQuery = DB::table(DB::raw("(SELECT owner_id as pharmacy_id, medicine_id,
                SUM(qty_on_hand) as stock,
                AVG(purchase_price) as avg_purchase_price,
                AVG(sale_price) as avg_sale_price
            FROM stock_batches
            WHERE deleted_at IS NULL
              AND owner_type = {$pharmacyMorph}
              AND medicine_id IS NOT NULL
            GROUP BY owner_id, medicine_id) as sb"))
            ->join('medicines as m', 'm.id', '=', 'sb.medicine_id')
            ->join('pharmacies as p', 'p.id', '=', 'sb.pharmacy_id')
            ->leftJoin(DB::raw("(SELECT sb2.owner_id as pharmacy_id, sb2.medicine_id, COALESCE(SUM(a.quantity), 0) as total
                FROM prescription_item_batch_allocations a
                JOIN stock_batches sb2 ON sb2.id = a.stock_batch_id
                WHERE sb2.owner_type = {$pharmacyMorph}
                  AND sb2.deleted_at IS NULL
                GROUP BY sb2.owner_id, sb2.medicine_id) as ot"), function ($join) {
                $join->on('ot.pharmacy_id', '=', 'sb.pharmacy_id')->on('ot.medicine_id', '=', 'sb.medicine_id');
            })
            ->whereIn('sb.pharmacy_id', $allowedPharmacyIds)
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at');

        $stockSql = 'COALESCE(sb.stock, 0)';

        if ($request->filled('search')) {
            $search = $request->search;
            $baseQuery->where(function ($query) use ($search) {
                $query->where('m.name', 'like', "%{$search}%")
                    ->orWhere('p.name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('medicine_id')) {
            $baseQuery->where('m.id', (int) $request->medicine_id);
        }

        if ($request->stock_status === 'out_of_stock') {
            $baseQuery->whereRaw("{$stockSql} <= 0");
        } elseif ($request->stock_status === 'low_stock') {
            $baseQuery->whereRaw("{$stockSql} > 0 AND {$stockSql} <= 10");
        }

        $ownerType = (new Pharmacy)->getMorphClass();
        $stockStats['total_income'] = (int) StockBatch::query()
            ->where('owner_type', $ownerType)
            ->whereIn('owner_id', $allowedPharmacyIds)
            ->whereNotNull('medicine_id')
            ->sum(DB::raw('qty_on_hand'));
        $stockStats['total_outcome'] = (int) DB::table('prescription_item_batch_allocations as a')
            ->join('stock_batches as sb', 'sb.id', '=', 'a.stock_batch_id')
            ->where('sb.owner_type', $ownerType)
            ->whereIn('sb.owner_id', $allowedPharmacyIds)
            ->sum('a.quantity');

        $statsResult = (clone $baseQuery)->selectRaw("
            COUNT(*) as total_items,
            COALESCE(SUM({$stockSql}), 0) as total_stock,
            SUM(CASE WHEN {$stockSql} > 0 AND {$stockSql} <= 10 THEN 1 ELSE 0 END) as total_low_stock,
            SUM(CASE WHEN {$stockSql} <= 0 THEN 1 ELSE 0 END) as total_out_of_stock
        ")->first();

        if ($statsResult) {
            $stockStats['total_items'] = (int) $statsResult->total_items;
            $stockStats['total_stock'] = (int) $statsResult->total_stock;
            $stockStats['total_low_stock'] = (int) $statsResult->total_low_stock;
            $stockStats['total_out_of_stock'] = (int) $statsResult->total_out_of_stock;
        }

        $query = (clone $baseQuery)->select(
            'm.id as medicine_id',
            'm.name as medicine_name',
            'p.id as pharmacy_id',
            'p.name as pharmacy_name',
            DB::raw('COALESCE(sb.stock, 0) + COALESCE(ot.total, 0) as income'),
            DB::raw('COALESCE(ot.total, 0) as outcome'),
            DB::raw("{$stockSql} as stock"),
            DB::raw('COALESCE(sb.avg_purchase_price, 0) as purchase_price'),
            DB::raw('COALESCE(sb.avg_sale_price, 0) as sale_price'),
        );

        $sortBy = $request->get('sort_by', 'medicine');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $sortableColumns = [
            'medicine' => 'm.name',
            'pharmacy' => 'p.name',
            'income' => 'income',
            'outcome' => 'outcome',
            'stock' => 'stock',
        ];
        $sortColumn = $sortableColumns[$sortBy] ?? 'm.name';
        $query->orderBy($sortColumn, $sortOrder)->orderBy('m.name')->orderBy('p.name');

        $perPage = (int) $request->get('per_page', 15);
        $paginator = $query->paginate($perPage > 0 ? $perPage : 15)->withQueryString();

        return Inertia::render('PharmacyFulfillments/Stock', $this->stockPagePayload(
            $request,
            $user,
            $paginator,
            $stockStats,
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function stockPagePayload(
        Request $request,
        \App\Models\User $user,
        \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator,
        array $stockStats,
    ): array {
        return [
            'stockItems' => [
                'data' => collect($paginator->items())->map(fn ($item) => [
                    'medicine_id' => $item->medicine_id,
                    'medicine_name' => $item->medicine_name,
                    'pharmacy_id' => $item->pharmacy_id,
                    'pharmacy_name' => $item->pharmacy_name,
                    'income' => (int) $item->income,
                    'outcome' => (int) $item->outcome,
                    'stock' => (int) $item->stock,
                    'purchase_price' => (float) ($item->purchase_price ?? 0),
                    'sale_price' => (float) ($item->sale_price ?? 0),
                ])->values()->all(),
                'links' => method_exists($paginator, 'linkCollection') ? $paginator->linkCollection()->toArray() : [],
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stockStats' => $stockStats,
            'filters' => $this->collectFilters($request, self::STOCK_FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => $this->isPharmacyAdmin($user)
                    ? Pharmacy::query()->orderBy('name')->get(['id', 'name'])
                    : $user->activePharmacies->map(fn (Pharmacy $pharmacy) => ['id' => $pharmacy->id, 'name' => $pharmacy->name])->values()->all(),
                'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
                'stockStatuses' => ['out_of_stock', 'low_stock'],
            ],
            'userPharmacies' => $user->activePharmacies->map(fn (Pharmacy $pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all(),
            'urls' => [
                'index' => route('pharmacy-stock.index'),
                'requests' => route('depots.requests.index'),
                'outcomes' => route('outcomes.index'),
            ],
        ];
    }
}
