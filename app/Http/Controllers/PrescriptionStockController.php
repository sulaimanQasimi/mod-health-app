<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionStock;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrescriptionStockController extends Controller
{
    public function index(Request $request)
    {
        $query = PrescriptionStock::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('medicine_name', 'like', "%{$search}%")
                  ->orWhere('pharmacy_name', 'like', "%{$search}%");
            });
        }

        // Filter by pharmacy
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            switch ($status) {
                case 'low_stock':
                    $query->whereRaw('pharmacy_stock <= minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('pharmacy_stock', 0);
                    break;
                case 'overstocked':
                    $query->whereRaw('pharmacy_stock >= maximum_stock');
                    break;
                case 'expired':
                    $query->where('expired_stock', '>', 0);
                    break;
                case 'expiring_soon':
                    $query->where('expiring_soon_stock', '>', 0);
                    break;
                case 'total_low_stock':
                    $query->whereRaw('total_stock <= minimum_stock');
                    break;
                case 'total_out_of_stock':
                    $query->where('total_stock', 0);
                    break;
                case 'total_overstocked':
                    $query->whereRaw('total_stock >= maximum_stock');
                    break;
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'medicine_name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $prescriptionStocks = $query->paginate($perPage);

        // Get all pharmacies for filter dropdown
        $pharmacies = Pharmacy::orderBy('name')->get();

        return view('pages.prescription_stocks.index', compact('prescriptionStocks', 'pharmacies'));
    }
}
