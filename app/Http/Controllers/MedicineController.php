<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\Medicine;
use App\Models\MedicineType;
use Illuminate\Http\Request;

class MedicineController extends Controller
{
    /**
     * Display a listing of the medicines.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Medicine::with(['medicineType']);

        // Search by medicine name
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by medicine type
        if ($request->filled('medicine_type_id')) {
            $query->where('medicine_type_id', $request->medicine_type_id);
        }

        // Filter by disease
        if ($request->filled('disease_id')) {
            $query->where('disease_id', 'like', '%' . $request->disease_id . '%');
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        
        // Validate sort fields
        $allowedSortFields = ['id', 'name', 'medicine_type_id', 'created_at'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'id';
        }
        
        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }

        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $allowedPerPage = [10, 15, 25, 50, 100];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 15;
        }

        $medicines = $query->paginate($perPage)->withQueryString();

        // Get data for filters
        $medicineTypes = MedicineType::all();
        $diseases = Disease::all();

        return view('pages.medicines.index', compact('medicines', 'medicineTypes', 'diseases'));
    }

    /**
     * Show the form for creating a new medicine.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $medicineTypes = MedicineType::all();
        $diseases = Disease::all();
        return view('pages.medicines.create', compact('medicineTypes','diseases'));
    }

    /**
     * Store a newly created medicine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:192',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'disease_id' => 'required'
        ]);

        if(isset($data['disease_id']) && $data['disease_id'] != ''){

            $data['disease_id']  = json_encode($data['disease_id']);
        }

        Medicine::create($data);

        return redirect()->route('medicines.index')->with('success', localize('global.medicine_created_successfully.'));
    }

    /**
     * Display the specified medicine.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\Response
     */
    public function show(Medicine $medicine)
    {
        // return view('pages.medicines.show', compact('medicine'));
    }

    /**
     * Show the form for editing the specified medicine.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\Response
     */
    public function edit(Medicine $medicine)
    {
        $medicineTypes = MedicineType::all();
        $diseases = Disease::all();
        return view('pages.medicines.edit', compact('medicine', 'medicineTypes','diseases'));
    }

    /**
     * Update the specified medicine in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Medicine $medicine)
    {
        $data = $request->validate([
            'name' => 'required|string|max:192',
            'medicine_type_id' => 'required|exists:medicine_types,id',
            'disease_id' => 'required'

        ]);

        $medicine->update($data);

        return redirect()->route('medicines.index')->with('success', localize('global.medicine_updated_successfully.'));
    }

    /**
     * Remove the specified medicine from storage.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\Response
     */
    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return redirect()->route('medicines.index')->with('success', localize('global.medicine_deleted_successfully.'));
    }
}
