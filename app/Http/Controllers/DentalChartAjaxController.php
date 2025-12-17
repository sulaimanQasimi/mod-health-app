<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalChart;
use App\Models\DentalChartMeasurement;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use HanifHefaz\Dcter\Dcter;

class DentalChartAjaxController extends Controller
{
    /**
     * Get charts for a registration
     */
    public function getCharts(DentistRegistration $dentistRegistration)
    {
        try {
            $dentistRegistration->assignToCurrentDentistIfMissing();

            $charts = $dentistRegistration->dentalCharts()
                ->with(['measurements', 'creator'])
                ->orderBy('chart_date', 'desc')
                ->orderBy('tooth_number', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $charts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dental charts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart for a specific tooth
     */
    public function getToothChart(DentistRegistration $dentistRegistration, $toothNumber)
    {
        try {
            $dentistRegistration->assignToCurrentDentistIfMissing();

            $chart = $dentistRegistration->dentalCharts()
                ->where('tooth_number', $toothNumber)
                ->with(['measurements', 'creator'])
                ->orderBy('chart_date', 'desc')
                ->first();

            return response()->json([
                'success' => true,
                'data' => $chart
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tooth chart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store chart via AJAX
     */
    public function storeChart(Request $request, DentistRegistration $dentistRegistration)
    {
        try {
            $dentistRegistration->assignToCurrentDentistIfMissing();

            $isImplant = $request->input('tooth_condition') === 'implant';

            $rules = [
                'tooth_number' => 'required|integer|min:11|max:48',
                'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
                'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
                'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
                'pocket_depth' => 'nullable|numeric|min:0|max:20',
                'bleeding' => 'nullable|boolean',
                'mobility' => 'nullable|in:none,grade1,grade2,grade3',
                'treatment_history' => 'nullable|string',
                'notes' => 'nullable|string',
            ];

            if ($isImplant) {
                $rules = array_merge($rules, [
                    'implant_system_brand' => 'nullable|string|max:255',
                    'implant_diameter' => 'nullable|numeric|min:0',
                    'implant_length' => 'nullable|numeric|min:0',
                    'implant_status' => 'nullable|in:planned,placed,failed,removed',
                    'implant_notes' => 'nullable|string',
                ]);
            }

            $validatedData = $request->validate($rules);

            $implantDetails = [];
            if ($isImplant) {
                // Auto-set implant date on backend (use chart date / today)
                $implantDate = now()->format('Y-m-d');

                $implantDetails = array_filter([
                    'implant_date' => $implantDate,
                    'implant_system_brand' => $validatedData['implant_system_brand'] ?? null,
                    'implant_diameter' => $validatedData['implant_diameter'] ?? null,
                    'implant_length' => $validatedData['implant_length'] ?? null,
                    'implant_status' => $validatedData['implant_status'] ?? null,
                    'implant_notes' => $validatedData['implant_notes'] ?? null,
                ], function ($v) {
                    return !is_null($v) && $v !== '';
                });
            }

            // Automatically set chart_date to today
            $validatedData['chart_date'] = now()->format('Y-m-d');
            $validatedData['dentist_registration_id'] = $dentistRegistration->id;

            // Persist implant details into JSON `measurements['implant']`
            if ($isImplant && !empty($implantDetails)) {
                $validatedData['measurements'] = array_merge($validatedData['measurements'] ?? [], [
                    'implant' => $implantDetails,
                ]);
            }

            $chart = DentalChart::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Dental chart created successfully',
                'data' => $chart->load('measurements')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create dental chart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update chart via AJAX
     */
    public function updateChart(Request $request, DentalChart $dentalChart)
    {
        try {
            if ($dentalChart->dentistRegistration) {
                $dentalChart->dentistRegistration->assignToCurrentDentistIfMissing();
            }

            $isImplant = $request->input('tooth_condition') === 'implant';

            $rules = [
                'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
                'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
                'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
                'pocket_depth' => 'nullable|numeric|min:0|max:20',
                'bleeding' => 'nullable|boolean',
                'mobility' => 'nullable|in:none,grade1,grade2,grade3',
                'treatment_history' => 'nullable|string',
                'notes' => 'nullable|string',
            ];

            if ($isImplant) {
                $rules = array_merge($rules, [
                    'implant_system_brand' => 'nullable|string|max:255',
                    'implant_diameter' => 'nullable|numeric|min:0',
                    'implant_length' => 'nullable|numeric|min:0',
                    'implant_status' => 'nullable|in:planned,placed,failed,removed',
                    'implant_notes' => 'nullable|string',
                ]);
            }

            $validatedData = $request->validate($rules);

            $implantDetails = [];
            if ($isImplant) {
                // Auto-set implant date on backend:
                // - keep existing implant_date if already set
                // - otherwise fallback to chart_date
                $existingImplantDate = $dentalChart->implant_details['implant_date'] ?? null;
                $chartDateRaw = $dentalChart->chart_date;
                $chartDateString = null;
                if ($chartDateRaw instanceof \Carbon\CarbonInterface) {
                    $chartDateString = $chartDateRaw->format('Y-m-d');
                } elseif (!empty($chartDateRaw)) {
                    $chartDateString = (string) $chartDateRaw;
                }
                $implantDate = $existingImplantDate ?: ($chartDateString ?: now()->format('Y-m-d'));

                $implantDetails = array_filter([
                    'implant_date' => $implantDate,
                    'implant_system_brand' => $validatedData['implant_system_brand'] ?? null,
                    'implant_diameter' => $validatedData['implant_diameter'] ?? null,
                    'implant_length' => $validatedData['implant_length'] ?? null,
                    'implant_status' => $validatedData['implant_status'] ?? null,
                    'implant_notes' => $validatedData['implant_notes'] ?? null,
                ], function ($v) {
                    return !is_null($v) && $v !== '';
                });
            }

            // Keep existing chart_date (don't update it)
            // chart_date is automatically set to today when creating, but preserved when updating

            $dentalChart->fill($validatedData);
            if ($isImplant) {
                $dentalChart->implant_details = $implantDetails;
            } else {
                // If condition changes away from implant, clear implant JSON
                $dentalChart->implant_details = [];
            }
            $dentalChart->save();

            return response()->json([
                'success' => true,
                'message' => 'Dental chart updated successfully',
                'data' => $dentalChart->load('measurements')
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update dental chart',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store measurement via AJAX
     */
    public function storeMeasurement(Request $request, DentalChart $dentalChart)
    {
        try {
            $validatedData = $request->validate([
                'measurement_type' => 'required|string',
                'measurement_value' => 'nullable|numeric',
                'measurement_unit' => 'nullable|string|max:20',
                'measurement_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $validatedData['dental_chart_id'] = $dentalChart->id;
            $measurement = DentalChartMeasurement::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Measurement created successfully',
                'data' => $measurement
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create measurement',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
