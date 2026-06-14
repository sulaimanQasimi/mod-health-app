<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Medicine;
use App\Models\Pharmacy;
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

        $usageSql = "(SELECT p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id, COUNT(*) as total
            FROM prescription_items pi
            LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
            JOIN prescriptions p ON pi.prescription_id = p.id
            WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL
            GROUP BY p.pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id))";

        $unionSql = "((SELECT pharmacy_id, medicine_id FROM pharmacy_fulfillments WHERE deleted_at IS NULL)
            UNION
            (SELECT pharmacy_id, medicine_id FROM outcomes WHERE deleted_at IS NULL AND pharmacy_id IS NOT NULL)
            UNION
            (SELECT p.pharmacy_id as pharmacy_id, COALESCE(pai.medicine_id, pi.medicine_id) as medicine_id
             FROM prescription_items pi
             LEFT JOIN prescription_alternative_items pai ON pai.prescription_item_id = pi.id AND pai.prescription_id = pi.prescription_id AND pai.is_selected = 1 AND pai.deleted_at IS NULL
             JOIN prescriptions p ON pi.prescription_id = p.id
             WHERE pi.deleted_at IS NULL AND p.deleted_at IS NULL AND p.pharmacy_id IS NOT NULL)) as u";
        $ftSql = '(SELECT pharmacy_id, medicine_id, SUM(CAST(amount AS UNSIGNED)) as total FROM pharmacy_fulfillments WHERE deleted_at IS NULL GROUP BY pharmacy_id, medicine_id)';
        $stockSql = 'COALESCE(ft.total, 0) - COALESCE(ot.total, 0)';

        $baseQuery = DB::table(DB::raw($unionSql))
            ->join('medicines as m', 'm.id', '=', 'u.medicine_id')
            ->join('pharmacies as p', 'p.id', '=', 'u.pharmacy_id')
            ->leftJoin(DB::raw("{$ftSql} as ft"), function ($join) {
                $join->on('ft.pharmacy_id', '=', 'u.pharmacy_id')->on('ft.medicine_id', '=', 'u.medicine_id');
            })
            ->leftJoin(DB::raw("{$usageSql} as ot"), function ($join) {
                $join->on('ot.pharmacy_id', '=', 'u.pharmacy_id')->on('ot.medicine_id', '=', 'u.medicine_id');
            })
            ->whereIn('u.pharmacy_id', $allowedPharmacyIds)
            ->whereNull('m.deleted_at')
            ->whereNull('p.deleted_at');

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

        $stockStats['total_income'] = (int) DB::table('pharmacy_fulfillments')
            ->whereNull('deleted_at')
            ->whereIn('pharmacy_id', $allowedPharmacyIds)
            ->sum(DB::raw('CAST(amount AS UNSIGNED)'));
        $stockStats['total_outcome'] = (int) DB::table('prescription_items as pi')
            ->leftJoin('prescription_alternative_items as pai', function ($join) {
                $join->on('pai.prescription_item_id', '=', 'pi.id')
                    ->on('pai.prescription_id', '=', 'pi.prescription_id')
                    ->whereRaw('(pai.is_selected = 1 AND pai.deleted_at IS NULL)');
            })
            ->join('prescriptions as p', 'pi.prescription_id', '=', 'p.id')
            ->whereNull('pi.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereIn('p.pharmacy_id', $allowedPharmacyIds)
            ->count();

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
            DB::raw('COALESCE(ft.total, 0) as income'),
            DB::raw('COALESCE(ot.total, 0) as outcome'),
            DB::raw("{$stockSql} as stock"),
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
                'index' => route('react.pharmacy-stock.index'),
                'requests' => route('react.depots.requests.index'),
                'outcomes' => route('react.outcomes.index'),
            ],
        ];
    }
}
