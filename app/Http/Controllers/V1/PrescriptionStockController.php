<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Pharmacy;
use App\Models\PrescriptionStock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PrescriptionStockController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'pharmacy_id', 'stock_status', 'sort_by', 'sort_order', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorizePharmacyManager($request->user());

        $query = PrescriptionStock::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('medicine_name', 'like', "%{$search}%")
                    ->orWhere('pharmacy_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        if ($request->filled('stock_status')) {
            match ($request->stock_status) {
                'low_stock' => $query->whereRaw('pharmacy_stock <= minimum_stock'),
                'out_of_stock' => $query->where('pharmacy_stock', 0),
                'overstocked' => $query->whereRaw('pharmacy_stock >= maximum_stock'),
                'expired' => $query->where('expired_stock', '>', 0),
                'expiring_soon' => $query->where('expiring_soon_stock', '>', 0),
                'total_low_stock' => $query->whereRaw('total_stock <= minimum_stock'),
                'total_out_of_stock' => $query->where('total_stock', 0),
                'total_overstocked' => $query->whereRaw('total_stock >= maximum_stock'),
                default => null,
            };
        }

        $sortBy = $request->get('sort_by', 'medicine_name');
        $sortOrder = $request->get('sort_order', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortBy, $sortOrder);

        $paginator = $this->paginateQuery($query, $request, 15);

        return Inertia::render('PrescriptionStocks/Index', [
            'prescriptionStocks' => $this->paginationPayload($paginator, fn (PrescriptionStock $stock) => [
                'medicine_id' => $stock->medicine_id,
                'medicine_name' => $stock->medicine_name,
                'pharmacy_id' => $stock->pharmacy_id ?? null,
                'pharmacy_name' => $stock->pharmacy_name ?? null,
                'pharmacy_stock' => (int) ($stock->pharmacy_stock ?? 0),
                'total_stock' => (int) ($stock->total_stock ?? 0),
                'pharmacy_income' => (int) ($stock->pharmacy_income ?? 0),
                'pharmacy_outcome' => (int) ($stock->pharmacy_outcome ?? 0),
                'minimum_stock' => (int) ($stock->minimum_stock ?? 0),
                'maximum_stock' => (int) ($stock->maximum_stock ?? 0),
                'stock_status' => $this->resolveStockStatus($stock),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => Pharmacy::query()->orderBy('name')->get(['id', 'name']),
                'stockStatuses' => [
                    'low_stock',
                    'out_of_stock',
                    'overstocked',
                    'total_low_stock',
                    'total_out_of_stock',
                    'total_overstocked',
                ],
            ],
            'permissions' => [
                'create' => $request->user()->can('create-prescription-stocks')
                    || $this->isPharmacyAdmin($request->user()),
            ],
            'urls' => [
                'index' => route('prescription-stocks.index'),
                'createIncome' => route('incomes.create'),
            ],
        ]);
    }

    private function resolveStockStatus(PrescriptionStock $stock): string
    {
        $pharmacyStock = (int) ($stock->pharmacy_stock ?? 0);
        $minimum = (int) ($stock->minimum_stock ?? 0);
        $maximum = (int) ($stock->maximum_stock ?? 0);

        if ($pharmacyStock <= 0) {
            return 'out_of_stock';
        }

        if ($pharmacyStock <= $minimum) {
            return 'low_stock';
        }

        if ($maximum > 0 && $pharmacyStock >= $maximum) {
            return 'overstocked';
        }

        return 'normal';
    }
}
