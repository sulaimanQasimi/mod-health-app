<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use HanifHefaz\Dcter\Dcter;

class DentalChartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, DentistRegistration $dentistRegistration)
    {
        $query = $dentistRegistration->dentalCharts()->with(['measurements', 'creator']);

        // Filter by tooth number
        if ($request->filled('tooth_number')) {
            $query->where('tooth_number', $request->tooth_number);
        }

        // Filter by condition
        if ($request->filled('tooth_condition')) {
            $query->where('tooth_condition', $request->tooth_condition);
        }

        // Filter by date
        if ($request->filled('chart_date')) {
            $query->where('chart_date', $request->chart_date);
        }

        $charts = $query->orderBy('chart_date', 'desc')
                       ->orderBy('tooth_number', 'asc')
                       ->paginate(32)->withQueryString();

        return view('pages.dentist.charts.index', compact('dentistRegistration', 'charts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(DentistRegistration $dentistRegistration)
    {
        return view('pages.dentist.charts.create', compact('dentistRegistration'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, DentistRegistration $dentistRegistration)
    {
        $validatedData = $request->validate([
            'tooth_number' => 'required|integer|min:11|max:48',
            'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
            'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
            'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
            'pocket_depth' => 'nullable|numeric|min:0|max:20',
            'bleeding' => 'nullable|boolean',
            'mobility' => 'nullable|in:none,grade1,grade2,grade3',
            'treatment_history' => 'nullable|string',
            'chart_date' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['chart_date'])) {
            try {
                $validatedData['chart_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['chart_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['chart_date'])) {
                    return response()->json([
                        'success' => false,
                        'message' => localize('global.invalid_date_format'),
                        'errors' => ['chart_date' => [localize('global.invalid_date_format')]]
                    ], 422);
                }
            }
        }

        $validatedData['dentist_registration_id'] = $dentistRegistration->id;
        $chart = DentalChart::create($validatedData);
        $chart->load('images', 'periodontalMeasurements');

        // Always return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => localize('global.dental_chart_created_successfully'),
                'data' => $chart
            ]);
        }

        return redirect()->route('dentist-registrations.show', $dentistRegistration)
            ->with('success', localize('global.dental_chart_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load('dentalCharts.measurements', 'dentalCharts.images', 'dentalCharts.periodontalMeasurements');
        
        // Get all teeth data for the visual chart
        $allTeeth = [];
        for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null; // Upper right
        for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null; // Upper left
        for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null; // Lower left
        for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null; // Lower right

        // Get latest chart entry for each tooth
        $latestCharts = $dentistRegistration->dentalCharts()
            ->with(['images', 'periodontalMeasurements'])
            ->orderBy('chart_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('tooth_number')
            ->keyBy('tooth_number');

        foreach ($latestCharts as $toothNumber => $chart) {
            $allTeeth[$toothNumber] = $chart;
        }

        return view('pages.dentist.charts.show', compact('dentistRegistration', 'allTeeth', 'latestCharts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DentalChart $dentalChart)
    {
        $dentalChart->load('dentistRegistration', 'measurements', 'images', 'periodontalMeasurements');
        // Convert chart_date to Persian format for display
        $dentalChart->persian_chart_date = Dcter::GregorianToJalali($dentalChart->chart_date->format('Y-m-d'));
        return view('pages.dentist.charts.edit', compact('dentalChart'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalChart $dentalChart)
    {
        $validatedData = $request->validate([
            'tooth_condition' => 'required|in:healthy,cavity,filling,crown,bridge,extraction,missing,impacted,root_canal,implant,decay,fractured',
            'gum_health' => 'nullable|in:healthy,gingivitis,periodontitis,recession,bleeding',
            'oral_hygiene_score' => 'nullable|numeric|min:0|max:10',
            'pocket_depth' => 'nullable|numeric|min:0|max:20',
            'bleeding' => 'nullable|boolean',
            'mobility' => 'nullable|in:none,grade1,grade2,grade3',
            'treatment_history' => 'nullable|string',
            'chart_date' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        // Convert Persian date to Gregorian
        if (!empty($validatedData['chart_date'])) {
            try {
                $validatedData['chart_date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['chart_date']));
            } catch (\Exception $e) {
                // If conversion fails, try to validate as Gregorian date
                if (!strtotime($validatedData['chart_date'])) {
                    return response()->json([
                        'success' => false,
                        'message' => localize('global.invalid_date_format'),
                        'errors' => ['chart_date' => [localize('global.invalid_date_format')]]
                    ], 422);
                }
            }
        }

        $dentalChart->update($validatedData);

        // Always return JSON for AJAX requests
        if ($request->ajax() || $request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => localize('global.dental_chart_updated_successfully'),
                'data' => $dentalChart->load('images', 'periodontalMeasurements')
            ]);
        }

        return redirect()->route('dentist-registrations.show', $dentalChart->dentistRegistration)
            ->with('success', localize('global.dental_chart_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalChart $dentalChart)
    {
        $dentistRegistration = $dentalChart->dentistRegistration;
        $dentalChart->delete();

        return redirect()->route('dentist-registrations.show', $dentistRegistration)
            ->with('success', localize('global.dental_chart_deleted_successfully'));
    }

    /**
     * Show chart history timeline
     */
    public function history(Request $request, DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load('appointment.patient', 'dentist');
        
        // Get all unique chart dates
        $chartDates = $dentistRegistration->dentalCharts()
            ->select('chart_date')
            ->distinct()
            ->orderBy('chart_date', 'desc')
            ->pluck('chart_date');

        // Get selected date or default to latest
        $selectedDate = $request->get('date', $chartDates->first());

        // Get charts for selected date
        $charts = $dentistRegistration->dentalCharts()
            ->where('chart_date', $selectedDate)
            ->with(['images', 'periodontalMeasurements', 'creator'])
            ->orderBy('tooth_number', 'asc')
            ->get()
            ->keyBy('tooth_number');

        // Get all teeth data for the visual chart
        $allTeeth = [];
        for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null;
        for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null;
        for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null;
        for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null;

        foreach ($charts as $toothNumber => $chart) {
            $allTeeth[$toothNumber] = $chart;
        }

        return view('pages.dentist.charts.history', compact(
            'dentistRegistration',
            'chartDates',
            'selectedDate',
            'charts',
            'allTeeth'
        ));
    }

    /**
     * Compare charts from two dates
     */
    public function compare(Request $request, DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load('appointment.patient', 'dentist');
        
        $date1 = $request->get('date1', now()->format('Y-m-d'));
        $date2 = $request->get('date2', now()->subDays(30)->format('Y-m-d'));

        // Get charts for both dates
        $charts1 = $dentistRegistration->dentalCharts()
            ->where('chart_date', $date1)
            ->with(['images', 'periodontalMeasurements'])
            ->orderBy('tooth_number', 'asc')
            ->get()
            ->keyBy('tooth_number');

        $charts2 = $dentistRegistration->dentalCharts()
            ->where('chart_date', $date2)
            ->with(['images', 'periodontalMeasurements'])
            ->orderBy('tooth_number', 'asc')
            ->get()
            ->keyBy('tooth_number');

        // Get all unique chart dates for dropdown
        $chartDates = $dentistRegistration->dentalCharts()
            ->select('chart_date')
            ->distinct()
            ->orderBy('chart_date', 'desc')
            ->pluck('chart_date');

        return view('pages.dentist.charts.compare', compact(
            'dentistRegistration',
            'date1',
            'date2',
            'charts1',
            'charts2',
            'chartDates'
        ));
    }

    /**
     * Export chart as PDF
     */
    public function exportPdf(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load([
            'appointment.patient',
            'dentist',
            'dentalCharts.images',
            'dentalCharts.periodontalMeasurements',
            'dentalCharts.creator'
        ]);

        // Get latest chart entry for each tooth
        $latestCharts = $dentistRegistration->dentalCharts()
            ->orderBy('chart_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('tooth_number')
            ->keyBy('tooth_number');

        // Get all teeth data
        $allTeeth = [];
        for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null;
        for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null;
        for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null;
        for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null;

        foreach ($latestCharts as $toothNumber => $chart) {
            $allTeeth[$toothNumber] = $chart;
        }

        $html = View::make('pages.dentist.charts.export', compact(
            'dentistRegistration',
            'allTeeth',
            'latestCharts'
        ))->render();

        // Try to use DomPDF if available
        try {
            if (class_exists('Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                return $pdf->download('dental-chart-' . $dentistRegistration->ref_no . '.pdf');
            }
        } catch (\Exception $e) {
            // Fall through to HTML view if PDF generation fails
        }

        // Fallback to HTML view
        return view('pages.dentist.charts.export', compact(
            'dentistRegistration',
            'allTeeth',
            'latestCharts'
        ));
    }

    /**
     * Print-optimized view
     */
    public function printView(DentistRegistration $dentistRegistration)
    {
        $dentistRegistration->load([
            'appointment.patient',
            'dentist',
            'dentalCharts.images',
            'dentalCharts.periodontalMeasurements'
        ]);

        // Get latest chart entry for each tooth
        $latestCharts = $dentistRegistration->dentalCharts()
            ->orderBy('chart_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('tooth_number')
            ->keyBy('tooth_number');

        // Get all teeth data
        $allTeeth = [];
        for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null;
        for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null;
        for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null;
        for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null;

        foreach ($latestCharts as $toothNumber => $chart) {
            $allTeeth[$toothNumber] = $chart;
        }

        return view('pages.dentist.charts.print', compact(
            'dentistRegistration',
            'allTeeth',
            'latestCharts'
        ));
    }
}
