<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalChart;
use App\Models\DentalChartMeasurement;
use Illuminate\Http\Request;

class DentalChartAjaxController extends Controller
{
    /**
     * Get charts for a registration
     */
    public function getCharts(DentistRegistration $dentistRegistration)
    {
        try {
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
            $validatedData = $request->validate([
                'tooth_number' => 'required|integer|min:11|max:48',
                'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
                'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
                'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
                'pocket_depth' => 'nullable|numeric|min:0|max:20',
                'bleeding' => 'nullable|boolean',
                'mobility' => 'nullable|in:none,grade1,grade2,grade3',
                'treatment_history' => 'nullable|string',
                'chart_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $validatedData['dentist_registration_id'] = $dentistRegistration->id;
            $chart = DentalChart::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Dental chart created successfully',
                'data' => $chart->load('measurements')
            ]);
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
            $validatedData = $request->validate([
                'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
                'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
                'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
                'pocket_depth' => 'nullable|numeric|min:0|max:20',
                'bleeding' => 'nullable|boolean',
                'mobility' => 'nullable|in:none,grade1,grade2,grade3',
                'treatment_history' => 'nullable|string',
                'chart_date' => 'required|date',
                'notes' => 'nullable|string',
            ]);

            $dentalChart->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Dental chart updated successfully',
                'data' => $dentalChart->load('measurements')
            ]);
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
