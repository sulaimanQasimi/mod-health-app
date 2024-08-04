<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\FoodType;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\OperationType;
use App\Models\Relation;
use App\Models\Room;
use App\Models\UnderReview;
use Illuminate\Http\Request;

class UnderReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $under_reviews = UnderReview::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();

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
    public function show(UnderReview $underReview)
    {

        $labTypeSections = LabTypeSection::all();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        $medicineUsageTypes = MedicineUsageType::all();

        return view('pages.under_reviews.show',compact('underReview','labTypeSections','operationTypes','labTypes','medicineTypes','medicines','rooms','beds','foodTypes','relations','medicineUsageTypes'));
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
