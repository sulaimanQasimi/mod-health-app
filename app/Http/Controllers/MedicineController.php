<?php

namespace App\Http\Controllers;

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
    public function index()
    {
        $medicines = Medicine::latest()->paginate(15);
        return view('pages.medicines.index', compact('medicines'));
    }

    /**
     * Show the form for creating a new medicine.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $medicineTypes = MedicineType::all();
        return view('pages.medicines.create', compact('medicineTypes'));
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
        ]);

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
        return view('pages.medicines.edit', compact('medicine', 'medicineTypes'));
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
