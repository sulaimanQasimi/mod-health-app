<?php

namespace App\Http\Controllers;

use App\Models\Hospitalization;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();

                if ($hospitalizations) {
                    return response()->json([
                        'data' => $hospitalizations,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();
        return view('pages.hospitalizations.index', compact('hospitalizations'));
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
            'description' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'hospitalization_id' => 'nullable',
            'i_c_u_id' => 'nullable',
            'p_a_c_u_id' => 'nullable',
            'under_review_id' => 'nullable',
            'food_type_id' => 'nullable',
            'bp' => 'nullable',
            'pr' => 'nullable',
            'rr' => 'nullable',
            't' => 'nullable',
            'spo2' => 'nullable',
            'pain' => 'nullable',
            'intake' => 'nullable',
            'output' => 'nullable',
            'antibiotic' => 'nullable',

        ]);
        if(isset($data['food_type_id']) && $data['food_type_id'] != ''){

            $data['food_type_id']  = json_encode($data['food_type_id']);
        }

        Visit::create($data);


        return redirect()->back()->with('success', localize('global.visit_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Visit $visit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function editUnderReviewVisit(Visit $visit)
    {
        return view('pages.under_reviews.visits_edit',compact('visit'));
    }

    public function updateUnderReviewVisit(Request $request, Visit $visit)
    {
        $data = $request->validate([
            'description' => 'required',
        ]);

        $visit->update($data);

        return redirect()->route('under_reviews.show', $visit->under_review->id)->with('success', localize('global.visit_updated_successfully.'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Visit $visit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroyUnderReviewVisit(Visit $visit)
    {
        $visit->delete();
        return redirect()->route('under_reviews.show', $visit->under_review->id)->with('success', localize('global.visit_deleted_successfully.'));

    }
}
