<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalExamination;
use Illuminate\Http\Request;

class DentalExaminationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'examination_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;
        $examination = DentalExamination::create($validatedData);

        return redirect()->back()->with('success', localize('global.examination_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalExamination $dentalExamination)
    {
        $validatedData = $request->validate([
            'examination_date' => 'required|date',
            'chief_complaint' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $dentalExamination->update($validatedData);

        return redirect()->back()->with('success', localize('global.examination_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalExamination $dentalExamination)
    {
        $dentalExamination->delete();

        return redirect()->back()->with('success', localize('global.examination_deleted_successfully'));
    }
}
