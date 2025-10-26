<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\FoodType;
use App\Models\LabType;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\OperationType;
use App\Models\Relation;
use App\Models\Room;
use App\Models\UnderReview;
use App\Models\DiabetesChart;
use App\Models\Nurse;
use App\Models\NurseNote;
use App\Models\MedicationAdministrationRecord;
use Illuminate\Http\Request;

class UnderReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $under_reviews = UnderReview::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get()
            ->map(function ($under_review) {
                $under_review->jalali_date = \HanifHefaz\Dcter\Dcter::GregorianToJalali($under_review->created_at);
                return $under_review;
            });

                if ($under_reviews) {
                    return response()->json([
                        'data' => $under_reviews,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $under_reviews = UnderReview::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();
        return view('pages.under_reviews.index', compact('under_reviews'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reason' => 'required',
            'remarks' => 'required',
            'room_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'bed_id' => 'required',
            'appointment_id' => 'required',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'branch_id' => 'required',
            'operation_id' => 'nullable',
        ]);

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();
        UnderReview::create($data);

        return redirect()->back()->with('success', localize('global.under_review_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(UnderReview $underReview, Request $request)
    {
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        $medicineUsageTypes = MedicineUsageType::all();

        // Load diabetes charts for this under review
        $diabetesChartsQuery = DiabetesChart::where('diabetes_chartable_type', 'App\\Models\\UnderReview')
            ->where('diabetes_chartable_id', $underReview->id)
            ->with(['nurse', 'medicine']);

        // Search functionality for diabetes charts
        if ($request->filled('diabetes_search')) {
            $search = $request->diabetes_search;
            $diabetesChartsQuery->where(function ($q) use ($search) {
                $q->where('rbs', 'like', "%{$search}%")
                  ->orWhere('fbs', 'like', "%{$search}%")
                  ->orWhere('insulin_dose', 'like', "%{$search}%")
                  ->orWhereHas('nurse', function ($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('medicine', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('diabetes_start_date')) {
            $diabetesChartsQuery->where('date', '>=', $request->diabetes_start_date);
        }
        if ($request->filled('diabetes_end_date')) {
            $diabetesChartsQuery->where('date', '<=', $request->diabetes_end_date);
        }

        // Filter by nurse
        if ($request->filled('diabetes_nurse_id')) {
            $diabetesChartsQuery->where('nurse_id', $request->diabetes_nurse_id);
        }

        // Filter by medicine
        if ($request->filled('diabetes_medicine_id')) {
            $diabetesChartsQuery->where('medicine_id', $request->diabetes_medicine_id);
        }

        $diabetesCharts = $diabetesChartsQuery->orderBy('date', 'desc')
                                             ->orderBy('time', 'desc')
                                             ->get();

        // Load nurse notes for this under review
        $nurseNotesQuery = NurseNote::where('morphable_type', 'App\\Models\\UnderReview')
            ->where('morphable_id', $underReview->id)
            ->with(['nurse', 'createdBy']);

        // Search functionality for nurse notes
        if ($request->filled('nurse_notes_search')) {
            $search = $request->nurse_notes_search;
            $nurseNotesQuery->where(function ($q) use ($search) {
                $q->where('note_am', 'like', "%{$search}%")
                  ->orWhere('note_pm', 'like', "%{$search}%")
                  ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                      $nurseQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by date range
        if ($request->filled('nurse_notes_start_date')) {
            $nurseNotesQuery->where('date', '>=', $request->nurse_notes_start_date);
        }
        if ($request->filled('nurse_notes_end_date')) {
            $nurseNotesQuery->where('date', '<=', $request->nurse_notes_end_date);
        }

        // Filter by nurse
        if ($request->filled('nurse_notes_nurse_id')) {
            $nurseNotesQuery->where('nurse_id', $request->nurse_notes_nurse_id);
        }

        $nurseNotes = $nurseNotesQuery->orderBy('date', 'desc')
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        // Load medication administration records for this under review
        $medicationAdministrationRecordsQuery = MedicationAdministrationRecord::where('morphable_type', 'App\\Models\\UnderReview')
            ->where('morphable_id', $underReview->id)
            ->with(['medicine', 'nurse', 'administrationTimes', 'createdBy']);

        // Search functionality for MARs
        if ($request->filled('mar_search')) {
            $search = $request->mar_search;
            $medicationAdministrationRecordsQuery->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                    $nurseQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by date range
        if ($request->filled('mar_start_date')) {
            $medicationAdministrationRecordsQuery->where('order_date', '>=', $request->mar_start_date);
        }
        if ($request->filled('mar_end_date')) {
            $medicationAdministrationRecordsQuery->where('order_date', '<=', $request->mar_end_date);
        }

        // Filter by nurse
        if ($request->filled('mar_nurse_id')) {
            $medicationAdministrationRecordsQuery->where('nurse_id', $request->mar_nurse_id);
        }

        // Filter by medicine
        if ($request->filled('mar_medicine_id')) {
            $medicationAdministrationRecordsQuery->where('medicine_id', $request->mar_medicine_id);
        }

        $medicationAdministrationRecords = $medicationAdministrationRecordsQuery->orderBy('order_date', 'desc')
                                                                               ->orderBy('created_at', 'desc')
                                                                               ->get();

        // Load vital signs for this under review
        $underReview->load(['vitalSigns.vitalSignType', 'vitalSigns.schedules.nurse']);

        // Load nutrition cares for this under review
        $underReview->load(['nutritionCares.createdBy', 'nutritionCares.updatedBy', 'nutritionCares.nurse']);

        // Load current user's nurse relationship for auto-selection
        $currentUser = auth()->user()->load('nurse');

        return view('pages.under_reviews.show',compact('underReview','operationTypes','labTypes','medicineTypes','medicines','rooms','beds','foodTypes','relations','medicineUsageTypes','diabetesCharts','nurseNotes','medicationAdministrationRecords','currentUser'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnderReview $underReview)
    {
        $rooms = Room::all();
        $beds = Bed::all();
        return view('pages.under_reviews.edit',compact('underReview','rooms','beds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnderReview $underReview)
    {
        $data = $request->validate([
            'is_discharged' => 'required',
            'discharge_remark' => 'required',
        ]);

        $underReview->update($data);

        $occupied_bed = Bed::findOrFail($underReview->bed_id);
        $occupied_bed->update(['is_occupied' => false]);
        $occupied_bed->save();

        return redirect()->route('visits.index')->with('success', localize('global.under_review_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnderReview $underReview)
    {
        $underReview->delete();

        return redirect()->back()->with('success', localize('global.under_review_deleted_successfully.'));
    }

    public function updateUnderReview(Request $request, UnderReview $underReview)
    {
        $validatedData = $request->validate([
            'reason' => 'required',
            'remarks' => 'required',
            'room_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'bed_id' => 'required',
            'appointment_id' => 'required',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'branch_id' => 'required',
            'operation_id' => 'nullable',
        ]);

        $underReview->update($validatedData);

        return redirect()->route('appointments.index')->with('success', localize('global.advice_updated_successfully.'));
    }
}
