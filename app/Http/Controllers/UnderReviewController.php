<?php

namespace App\Http\Controllers;

use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\OperationType;
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
        ]);

        UnderReview::create($data);

        return redirect()->back()->with('success', 'Under Review created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(UnderReview $underReview)
    {

        $labTypeSections = LabTypeSection::all();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();

        return view('pages.under_reviews.show',compact('underReview','labTypeSections','operationTypes','labTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnderReview $underReview)
    {
        //
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

        return redirect()->route('visits.index')->with('success', 'Under Review updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnderReview $underReview)
    {
        //
    }
}
