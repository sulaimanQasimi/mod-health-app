<?php

namespace App\Http\Controllers;

use App\Models\DiabetesChart;
use App\Models\Nurse;
use App\Models\Medicine;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DiabetesChartController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DiabetesChart::with(['nurse', 'medicine', 'diabetesChartable', 'createdBy', 'updatedBy']);

        // Filter by chartable type and ID
        if ($request->filled('chartable_type') && $request->filled('chartable_id')) {
            $query->where('diabetes_chartable_type', $request->chartable_type)
                  ->where('diabetes_chartable_id', $request->chartable_id);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by nurse
        if ($request->filled('nurse_id')) {
            $query->where('nurse_id', $request->nurse_id);
        }

        // Filter by medicine
        if ($request->filled('medicine_id')) {
            $query->where('medicine_id', $request->medicine_id);
        }

        // Search by blood sugar values
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('rbs', 'like', "%{$search}%")
                  ->orWhere('fbs', 'like', "%{$search}%")
                  ->orWhere('insulin_dose', 'like', "%{$search}%");
            });
        }

        $diabetesCharts = $query->orderBy('date', 'desc')
                               ->orderBy('time', 'desc')
                               ->paginate(15);

        $nurses = Nurse::active()->get();
        $medicines = Medicine::all();

        return view('pages.diabetes-charts.index', compact('diabetesCharts', 'nurses', 'medicines'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $this->authorize('create', DiabetesChart::class);

        // Get the current user's nurse record if they have one
        $currentUserNurse = auth()->user()->nurse;

        // If user doesn't have a nurse record, show permission denied message
        if (!$currentUserNurse) {
            return view('pages.diabetes-charts.create', compact('currentUserNurse'));
        }

        $nurses = Nurse::active()->get();
        $medicines = Medicine::all();

        // Pre-fill chartable data if provided
        $chartableType = $request->get('chartable_type');
        $chartableId = $request->get('chartable_id');
        $chartable = null;

        if ($chartableType && $chartableId) {
            if ($chartableType === 'App\\Models\\UnderReview') {
                $chartable = UnderReview::with('patient')->find($chartableId);
            } elseif ($chartableType === 'App\\Models\\Hospitalization') {
                $chartable = Hospitalization::with('patient')->find($chartableId);
            }
        }

        return view('pages.diabetes-charts.create', compact('nurses', 'medicines', 'chartable', 'chartableType', 'chartableId', 'currentUserNurse'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', DiabetesChart::class);

        // Get the current user's nurse record
        $currentUserNurse = auth()->user()->nurse;

        // If user doesn't have a nurse record, deny access
        if (!$currentUserNurse) {
            return redirect()->back()
                           ->with('error', 'You do not have permission to create diabetes charts. Only users with nurse accounts can create diabetes chart records.')
                           ->withInput();
        }

        $validator = Validator::make($request->all(), [
            'nurse_id' => 'nullable|exists:nurses,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'insulin_dose' => 'nullable|numeric|min:0|max:999.99',
            'rbs' => 'nullable|numeric|min:0|max:999.99',
            'fbs' => 'nullable|numeric|min:0|max:999.99',
            'unit' => 'nullable|string|max:20',
            'time' => 'nullable|date_format:H:i',
            'date' => 'required|date|before_or_equal:today',
            'diabetes_chartable_type' => 'required|in:App\\Models\\UnderReview,App\\Models\\Hospitalization',
            'diabetes_chartable_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Verify the chartable record exists
        $chartableType = $request->diabetes_chartable_type;
        $chartableId = $request->diabetes_chartable_id;

        if ($chartableType === 'App\\Models\\UnderReview') {
            $chartable = UnderReview::find($chartableId);
        } elseif ($chartableType === 'App\\Models\\Hospitalization') {
            $chartable = Hospitalization::find($chartableId);
        }

        if (!$chartable) {
            return redirect()->back()
                           ->with('error', 'Invalid chartable record.')
                           ->withInput();
        }

        DB::beginTransaction();
        try {
            // Prepare data for creation
            $data = $request->all();
            
            // Automatically set the nurse_id from the current user's nurse record
            $data['nurse_id'] = $currentUserNurse->id;
            
            $diabetesChart = DiabetesChart::create($data);

            DB::commit();

            return redirect()->route('diabetes-charts.index')
                           ->with('success', 'Diabetes chart record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to create diabetes chart record.')
                           ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(DiabetesChart $diabetesChart)
    {
        $this->authorize('view', $diabetesChart);

        $diabetesChart->load(['nurse', 'medicine', 'diabetesChartable.patient', 'createdBy', 'updatedBy']);
        return view('pages.diabetes-charts.show', compact('diabetesChart'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DiabetesChart $diabetesChart)
    {
        $this->authorize('update', $diabetesChart);

        $nurses = Nurse::active()->get();
        $medicines = Medicine::all();
        $diabetesChart->load('diabetesChartable');

        return view('pages.diabetes-charts.edit', compact('diabetesChart', 'nurses', 'medicines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DiabetesChart $diabetesChart)
    {
        $this->authorize('update', $diabetesChart);

        $validator = Validator::make($request->all(), [
            'nurse_id' => 'nullable|exists:nurses,id',
            'medicine_id' => 'nullable|exists:medicines,id',
            'insulin_dose' => 'nullable|numeric|min:0|max:999.99',
            'rbs' => 'nullable|numeric|min:0|max:999.99',
            'fbs' => 'nullable|numeric|min:0|max:999.99',
            'unit' => 'nullable|string|max:20',
            'time' => 'nullable|date_format:H:i',
            'date' => 'required|date|before_or_equal:today',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        DB::beginTransaction();
        try {
            $diabetesChart->update($request->all());

            DB::commit();

            return redirect()->route('diabetes-charts.index')
                           ->with('success', 'Diabetes chart record updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to update diabetes chart record.')
                           ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DiabetesChart $diabetesChart)
    {
        $this->authorize('delete', $diabetesChart);

        DB::beginTransaction();
        try {
            $diabetesChart->delete();

            DB::commit();

            return redirect()->route('diabetes-charts.index')
                           ->with('success', 'Diabetes chart record deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                           ->with('error', 'Failed to delete diabetes chart record.');
        }
    }

    /**
     * Show the print page for diabetes chart
     */
    public function print(Request $request)
    {
        $diabetesCharts = collect();
        $patient = null;
        $chartableType = null;
        $chartableId = null;

        // If specific under_review or hospitalization is requested
        if ($request->filled('chartable_type') && $request->filled('chartable_id')) {
            $chartableType = $request->chartable_type;
            $chartableId = $request->chartable_id;

            // Load diabetes charts for the specific record
            $diabetesCharts = DiabetesChart::with(['nurse', 'medicine'])
                ->where('diabetes_chartable_type', $chartableType)
                ->where('diabetes_chartable_id', $chartableId)
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->get();

            // Get patient information based on chartable type
            if ($chartableType === 'App\\Models\\UnderReview') {
                $underReview = UnderReview::with(['patient'])->find($chartableId);
                if ($underReview && $underReview->patient) {
                    $patient = $underReview->patient;
                }
            } elseif ($chartableType === 'App\\Models\\Hospitalization') {
                $hospitalization = Hospitalization::with(['patient'])->find($chartableId);
                if ($hospitalization && $hospitalization->patient) {
                    $patient = $hospitalization->patient;
                }
            }
        } else {
            // Load all diabetes charts if no specific record is requested
            $diabetesCharts = DiabetesChart::with(['nurse', 'medicine', 'diabetesChartable'])
                ->orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->limit(25)
                ->get();
        }

        return view('pages.diabetes-charts.print', compact('diabetesCharts', 'patient', 'chartableType', 'chartableId'));
    }

    /**
     * Get diabetes charts for API (for select dropdowns)
     */
    public function getDiabetesChartsForSelect(Request $request)
    {
        $query = DiabetesChart::with(['nurse', 'medicine']);

        if ($request->filled('chartable_type') && $request->filled('chartable_id')) {
            $query->where('diabetes_chartable_type', $request->chartable_type)
                  ->where('diabetes_chartable_id', $request->chartable_id);
        }

        $diabetesCharts = $query->orderBy('date', 'desc')
                               ->orderBy('time', 'desc')
                               ->limit(50)
                               ->get();

        return response()->json([
            'results' => $diabetesCharts->map(function ($chart) {
                $label = $chart->date->format('Y-m-d');
                if ($chart->time) {
                    $label .= ' ' . $chart->time->format('H:i');
                }
                if ($chart->rbs) {
                    $label .= ' - RBS: ' . $chart->rbs . ' ' . $chart->unit;
                }
                if ($chart->fbs) {
                    $label .= ' - FBS: ' . $chart->fbs . ' ' . $chart->unit;
                }
                if ($chart->insulin_dose) {
                    $label .= ' - Insulin: ' . $chart->insulin_dose;
                }

                return [
                    'id' => $chart->id,
                    'text' => $label,
                ];
            })
        ]);
    }
}