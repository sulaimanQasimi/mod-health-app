<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewICUNotification;
use App\Models\ICU;
use App\Models\LabType;
use App\Models\LabTypeSection;
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
        return view('pages.icus.show',compact('icu','previousDiagnoses','previousLabs','labTypes','labTypeSections'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICU $icu)
    {
        //
    }
}
