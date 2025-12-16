<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalTreatment;
use App\Models\DentalChart;
use Illuminate\Http\Request;
use HanifHefaz\Dcter\Dcter;

class DentalTreatmentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'treatment_type' => 'required|string',
            'tooth_number' => 'nullable|integer|min:11|max:48',
            'treatment_description' => 'required|string',
            'treatment_date' => 'required|string',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['treatment_date'])) {
            try {
                $validatedData['treatment_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['treatment_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['treatment_date'])) {
                    return redirect()->back()->withErrors(['treatment_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;
        
        // Auto-populate tooth_number from chart if dental_chart_id is provided
        if (!empty($validatedData['dental_chart_id']) && empty($validatedData['tooth_number'])) {
            $chart = DentalChart::find($validatedData['dental_chart_id']);
            if ($chart && $chart->dentist_registration_id == $dentistRegistration->id) {
                $validatedData['tooth_number'] = $chart->tooth_number;
            }
        }
        
        $treatment = DentalTreatment::create($validatedData);

        return redirect()->back()->with('success', localize('global.treatment_created_successfully'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalTreatment $dentalTreatment)
    {
        $validatedData = $request->validate([
            'dental_chart_id' => 'nullable|exists:dental_charts,id',
            'treatment_type' => 'required|string',
            'tooth_number' => 'nullable|integer|min:11|max:48',
            'treatment_description' => 'required|string',
            'treatment_date' => 'required|string',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['treatment_date'])) {
            try {
                $validatedData['treatment_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['treatment_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['treatment_date'])) {
                    return redirect()->back()->withErrors(['treatment_date' => localize('global.invalid_date_format')])->withInput();
                }
            }
        }

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
