<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalTreatment;
use Illuminate\Http\Request;

class DentalTreatmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'treatment_type' => 'required|string',
            'tooth_number' => 'nullable|string',
            'treatment_description' => 'required|string',
            'treatment_date' => 'required|date',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;
        $treatment = DentalTreatment::create($validatedData);

        return redirect()->back()->with('success', localize('global.treatment_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalTreatment $dentalTreatment)
    {
        $validatedData = $request->validate([
            'treatment_type' => 'required|string',
            'tooth_number' => 'nullable|string',
            'treatment_description' => 'required|string',
            'treatment_date' => 'required|date',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $dentalTreatment->update($validatedData);

        return redirect()->back()->with('success', localize('global.treatment_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalTreatment $dentalTreatment)
    {
        $dentalTreatment->delete();

        return redirect()->back()->with('success', localize('global.treatment_deleted_successfully'));
    }
}
