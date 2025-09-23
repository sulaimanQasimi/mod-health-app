<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Income::with(['medicine', 'createdBy', 'pharmacy']);

        // Get current user's pharmacies
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies;

        // Filter by user's pharmacies if user has any
        if ($userPharmacies->isNotEmpty()) {
            $query->whereIn('pharmacy_id', $userPharmacies->pluck('id'));
        }

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

        // Filter by pharmacy (for admin users who can see all pharmacies)
        if ($request->filled('pharmacy_id') && $user->hasRole('admin')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_from)->datetime();
            $query->where('purchase_date', '>=', $fromDate);
        }
        if ($request->filled('date_to')) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_to)->datetime();
            $query->where('purchase_date', '<=', $toDate);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $incomes = $query->paginate($perPage);

        // Get all pharmacies for admin filter
        $pharmacies = null;
        if ($user->hasRole('admin')) {
            $pharmacies = Pharmacy::orderBy('name')->get();
        }

        return view('pages.incomes.index', compact('incomes', 'pharmacies', 'userPharmacies'));
    }

    public function create()
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('incomes.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

        $medicines = Medicine::orderBy('name')->get();
        $incomeTypes = ['purchase', 'return', 'donation', 'transfer'];
        
        return view('pages.incomes.create', compact('medicines', 'incomeTypes', 'userPharmacy'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('incomes.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

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

        // Add pharmacy_id to the request data
        $data = $request->all();
        $data['pharmacy_id'] = $userPharmacy->id;

        Income::create($data);

        return redirect()->route('incomes.index')
            ->with('success', localize('global.income_record_created_successfully'));
    }
}
