<?php

namespace App\Http\Controllers;

use App\Models\PrescriptionStock;
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
                  ->orWhere('medicine_type_name', 'like', "%{$search}%");
            });
        }

        // Filter by stock status
        if ($request->filled('stock_status')) {
            $status = $request->stock_status;
            switch ($status) {
                case 'low_stock':
                    $query->whereRaw('current_stock <= minimum_stock');
                    break;
                case 'out_of_stock':
                    $query->where('current_stock', 0);
                    break;
                case 'overstocked':
                    $query->whereRaw('current_stock >= maximum_stock');
                    break;
                case 'expired':
                    $query->where('expired_stock', '>', 0);
                    break;
                case 'expiring_soon':
                    $query->where('expiring_soon_stock', '>', 0);
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

        return view('pages.prescription_stocks.index', compact('prescriptionStocks'));
    }
}
