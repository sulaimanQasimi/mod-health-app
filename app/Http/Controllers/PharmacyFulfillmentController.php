<?php

namespace App\Http\Controllers;

use App\Models\PharmacyFulfillment;
use App\Models\Medicine;
use App\Models\Pharmacy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PharmacyFulfillmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PharmacyFulfillment::with(['medicine', 'pharmacy', 'user', 'createdBy']);

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
                ->orWhere('form_no', 'like', "%{$search}%")
                ->orWhere('unit_type', 'like', "%{$search}%");
            });
        }

        // Filter by pharmacy (for admin users who can see all pharmacies)
        if ($request->filled('pharmacy_id') && $user->hasRole('admin')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_from)->datetime();
            $query->whereDate('date', '>=', $fromDate);
        }
        if ($request->filled('date_to')) {
            $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->date_to)->datetime();
            $query->whereDate('date', '<=', $toDate);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $fulfillments = $query->paginate($perPage);

        // Get all pharmacies for admin filter
        $pharmacies = null;
        if ($user->hasRole('admin')) {
            $pharmacies = Pharmacy::orderBy('name')->get();
        }

        return view('pages.pharmacy_fulfillments.index', compact('fulfillments', 'pharmacies', 'userPharmacies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('pharmacy_fulfillments.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

        $medicines = Medicine::orderBy('name')->get();
        
        return view('pages.pharmacy_fulfillments.create', compact('medicines', 'userPharmacy'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $userPharmacy = $user->activePharmacies()->first();

        // Check if user has a pharmacy assigned
        if (!$userPharmacy) {
            return redirect()->route('pharmacy_fulfillments.index')
                ->with('warning', 'You are not assigned to any pharmacy. Please contact your administrator.');
        }

        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'unit_type' => 'nullable|string|max:255',
            'amount' => 'required|string|max:191',
            'form_no' => 'required|string|max:191',
            'date' => 'required|date',
            'form' => 'nullable|file|mimes:pdf,PDF|max:10240',
        ]);

        $data = $request->all();
        $data['pharmacy_id'] = $userPharmacy->id;
        $data['user_id'] = $user->id;

        // Handle file upload
        if ($request->hasFile('form')) {
            $file = $request->file('form');
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
            $data['form'] = $path;
        }

        PharmacyFulfillment::create($data);

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $pharmacyFulfillment->load(['medicine', 'pharmacy', 'user', 'createdBy', 'updatedBy']);

        return view('pages.pharmacy_fulfillments.show', compact('pharmacyFulfillment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $medicines = Medicine::orderBy('name')->get();
        $userPharmacy = $pharmacyFulfillment->pharmacy;

        return view('pages.pharmacy_fulfillments.edit', compact('pharmacyFulfillment', 'medicines', 'userPharmacy'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'unit_type' => 'nullable|string|max:255',
            'amount' => 'required|string|max:191',
            'form_no' => 'required|string|max:191',
            'date' => 'required|date',
            'form' => 'nullable|file|mimes:pdf,PDF|max:10240',
        ]);

        $data = $request->except(['form']);

        // Handle file upload
        if ($request->hasFile('form')) {
            // Delete old file if exists
            if ($pharmacyFulfillment->form && Storage::disk('public')->exists($pharmacyFulfillment->form)) {
                Storage::disk('public')->delete($pharmacyFulfillment->form);
            }

            $file = $request->file('form');
            $filename = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('pharmacy_fulfillments', $filename, 'public');
            $data['form'] = $path;
        }

        $pharmacyFulfillment->update($data);

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PharmacyFulfillment $pharmacyFulfillment)
    {
        // Check if user has access to this fulfillment's pharmacy
        $user = Auth::user();
        $userPharmacies = $user->activePharmacies->pluck('id');

        if (!$user->hasRole('admin') && !$userPharmacies->contains($pharmacyFulfillment->pharmacy_id)) {
            abort(403, 'Unauthorized access');
        }

        $pharmacyFulfillment->delete();

        return redirect()->route('pharmacy_fulfillments.index')
            ->with('success', localize('global.pharmacy_fulfillment_deleted_successfully'));
    }
}
