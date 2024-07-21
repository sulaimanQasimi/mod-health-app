<?php

namespace App\Http\Controllers;

use App\Models\MedicineUsageType;
use Illuminate\Http\Request;

class MedicineUsageTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $medicineUsageTypes = MedicineUsageType::all();
        return view('pages.medicine_usage_types.index', compact('medicineUsageTypes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.medicine_usage_types.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medicineUsageType = MedicineUsageType::create($validatedData);

        return redirect()->route('medicine_usage_types.index')
                         ->with('success', 'Medicine usage type created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MedicineUsageType  $medicineUsageType
     * @return \Illuminate\Http\Response
     */
    public function show(MedicineUsageType $medicineUsageType)
    {
        return view('pages.medicine_usage_types.show', compact('medicineUsageType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MedicineUsageType  $medicineUsageType
     * @return \Illuminate\Http\Response
     */
    public function edit(MedicineUsageType $medicineUsageType)
    {
        return view('pages.medicine_usage_types.edit', compact('medicineUsageType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MedicineUsageType  $medicineUsageType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MedicineUsageType $medicineUsageType)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $medicineUsageType->update($validatedData);

        return redirect()->route('medicine_usage_types.index')
                         ->with('success', 'Medicine usage type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MedicineUsageType  $medicineUsageType
     * @return \Illuminate\Http\Response
     */
    public function destroy(MedicineUsageType $medicineUsageType)
    {
        $medicineUsageType->delete();
        return redirect()->route('medicine_usage_types.index')
                         ->with('success', 'Medicine usage type deleted successfully.');
    }
}