<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalExamination;
use Illuminate\Http\Request;
use HanifHefaz\Dcter\Dcter;

class DentalExaminationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'examination_date' => 'required|string',
            'chief_complaint' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['examination_date'])) {
            try {
                $validatedData['examination_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['examination_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['examination_date'])) {
                    return redirect()->back()->withErrors(['examination_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

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
            'examination_date' => 'required|string',
            'chief_complaint' => 'nullable|string',
            'clinical_findings' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['examination_date'])) {
            try {
                $validatedData['examination_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['examination_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['examination_date'])) {
                    return redirect()->back()->withErrors(['examination_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

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
