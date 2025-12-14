<?php

namespace App\Http\Controllers;

use App\Models\DentalChart;
use App\Models\DentalPeriodontalMeasurement;
use Illuminate\Http\Request;

class DentalPeriodontalController extends Controller
{
    /**
     * Store periodontal measurements for a chart
     */
    public function store(Request $request, DentalChart $dentalChart)
    {
        $validatedData = $request->validate([
            'measurements' => 'required|array',
            'measurements.*.measurement_point' => 'required|in:mesial,mid_mesial,mid,mid_distal,distal,lingual,palatal',
            'measurements.*.pocket_depth' => 'required|numeric|min:0|max:20',
            'measurements.*.recession' => 'nullable|numeric|min:0|max:10',
            'measurements.*.bleeding' => 'nullable|boolean',
            'measurements.*.plaque' => 'nullable|boolean',
            'measurements.*.notes' => 'nullable|string',
            'measurement_date' => 'required|date',
        ]);

        // Delete existing measurements for this chart and date
        DentalPeriodontalMeasurement::where('dental_chart_id', $dentalChart->id)
            ->where('measurement_date', $validatedData['measurement_date'])
            ->delete();

        // Create new measurements
        $created = [];
        foreach ($validatedData['measurements'] as $measurement) {
            $measurement['dental_chart_id'] = $dentalChart->id;
            $measurement['measurement_date'] = $validatedData['measurement_date'];
            $created[] = DentalPeriodontalMeasurement::create($measurement);
        }

        return response()->json([
            'success' => true,
            'message' => 'Periodontal measurements saved successfully',
            'data' => $created
        ]);
    }

    /**
     * Update periodontal measurement
     */
    public function update(Request $request, DentalPeriodontalMeasurement $measurement)
    {
        $validatedData = $request->validate([
            'pocket_depth' => 'required|numeric|min:0|max:20',
            'recession' => 'nullable|numeric|min:0|max:10',
            'bleeding' => 'nullable|boolean',
            'plaque' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $measurement->update($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Measurement updated successfully',
            'data' => $measurement
        ]);
    }

    /**
     * Get measurements for a chart
     */
    public function getMeasurements(DentalChart $dentalChart, Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $measurements = $dentalChart->periodontalMeasurements()
            ->where('measurement_date', $date)
            ->get()
            ->groupBy('measurement_point');

        return response()->json([
            'success' => true,
            'data' => $measurements
        ]);
    }
}
