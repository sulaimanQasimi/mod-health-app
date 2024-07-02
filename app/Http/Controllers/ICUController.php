<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewICUNotification;
use App\Models\Branch;
use App\Models\Department;
use App\Models\FoodType;
use App\Models\ICU;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\User;
use Illuminate\Http\Request;

class ICUController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $icus = ICU::where('branch_id',auth()->user()->branch_id)->with('patient')->get();

                if ($icus) {
                    return response()->json([
                        'data' => $icus,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $icus = ICU::where('branch_id',auth()->user()->branch_id)->with(['patient'])->get();
        return view('pages.icus.index', compact('icus'));
    }

    public function new()
    {
        $icus = ICU::where('status', 'new')->latest()->paginate(10);

        return view('pages.icus.new', compact('icus'));
    }

    public function approved()
    {
        $icus = ICU::where('status', 'approved')->latest()->paginate(10);

        return view('pages.icus.approved', compact('icus'));
    }

    public function rejected()
    {
        $icus = ICU::where('status', 'rejected')->latest()->paginate(10);

        return view('pages.icus.rejected', compact('icus'));
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
        // Validate the input
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'description' => 'required',
            'operation_id' => 'nullable',
            'icu_enterance_note' => 'nullable',
            'icu_reject_reason' => 'nullable',
        ]);

        // Create a new appointment
        $icu = ICU::create($validatedData);

        SendNewICUNotification::dispatch($icu->created_by, $icu->id);
        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', 'ICU created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ICU $icu)
    {
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        $previousDiagnoses = $icu->patient->diagnoses;
        $previousLabs = $icu->patient->labs;
        $branches = Branch::all();
        $departments = Department::all();
        $doctors = User::all();
        $foodTypes = FoodType::all();
        return view('pages.icus.show',compact('icu','previousDiagnoses','previousLabs','labTypes','labTypeSections','branches','departments','doctors','foodTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ICU $icu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ICU $icu)
    {
        $data = $request->validate([
            'icu_enterance_note' => 'nullable',
            'status' => 'nullable',
            'icu_reject_reason' => 'nullable',

        ]);

        $icu->update($data);


        return redirect()->route('icus.new')->with('success', 'ICU updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICU $icu)
    {
        //
    }
}
