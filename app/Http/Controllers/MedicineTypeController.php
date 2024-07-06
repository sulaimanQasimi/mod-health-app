<?php

namespace App\Http\Controllers;

use App\Models\MedicineType;
use Illuminate\Http\Request;

class MedicineTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicineTypes = MedicineType::all();
        return view('pages.medicine_types.index',compact('medicineTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.medicine_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required',
        ]);

        MedicineType::create($data);

        return redirect()->route('medicine_types.index')->with('success', localize('global.medicine_type_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicineType $medicineType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicineType $medicineType)
    {
        return view('pages.medicine_types.edit',compact('medicineType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicineType $medicineType)
{
    $data = $request->validate([
        'type' => 'required',
    ]);

    $medicineType->update($data);

    return redirect()->route('medicine_types.index')->with('success', localize('global.medicine_type_updated_successfully.'));
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicineType $medicineType)
    {
        //
    }
}
