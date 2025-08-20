<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::with(['medicine', 'createdBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('batch_number', 'like', "%{$search}%")
                ->orWhere('supplier_name', 'like', "%{$search}%")
                ->orWhere('invoice_number', 'like', "%{$search}%");
            });
        }

        // Filter by income type
        if ($request->filled('income_type')) {
            $query->where('income_type', $request->income_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('purchase_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('purchase_date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $incomes = $query->paginate($perPage);

        return view('pages.incomes.index', compact('incomes'));
    }

    public function create()
    {
        $medicines = Medicine::orderBy('name')->get();
        $incomeTypes = ['purchase', 'return', 'donation', 'transfer'];
        
        return view('pages.incomes.create', compact('medicines', 'incomeTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'amount' => 'required|integer|min:1',
            'batch_number' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date|after:today',
            'supplier_name' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'income_type' => 'required|in:purchase,return,donation,transfer',
            'notes' => 'nullable|string'
        ]);

        Income::create($request->all());

        return redirect()->route('incomes.index')
            ->with('success', 'Income record created successfully.');
    }
}
