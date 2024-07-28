<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnose;
use Illuminate\Http\Request;

class DiagnoseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $diagnoses = Diagnose::all();
        return view('pages.diagnoses.index',compact('diagnoses'));
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
            'appointment_id' => 'required',
            'type' => 'required',
            'bp' => 'nullable',
            'pr' => 'nullable',
            'weight' => 'nullable',
            't' => 'nullable',
            'spo2' => 'nullable',
            'pain' => 'nullable',
        ]);

        Diagnose::create($data);

        return redirect()->back()->with('success', localize('global.diagnose_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagnose $diagnose)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diagnose $diagnose)
    {
        return view('pages.diagnoses.edit',compact('diagnose'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diagnose $diagnose)
    {
        $data = $request->validate([
            'description' => 'required',
            'patient_id' => 'required',
            'appointment_id' => 'required',
            'type' => 'required',
            'bp' => 'nullable',
            'pr' => 'nullable',
            'weight' => 'nullable',
            't' => 'nullable',
            'spo2' => 'nullable',
            'pain' => 'nullable',
        ]);

        $diagnose->update($data);

        return redirect()->route('appointments.index')->with('success', localize('global.diagnose_updated_successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagnose $diagnose)
    {
        $item = Diagnose::findOrFail($diagnose->id);
        $item->delete();
        return redirect()->back()->with('success', localize('global.diagnose_deleted_successfully.'));

    }

    public function createDiagnose(Appointment $appointment)
    {
        return view('pages.diagnoses.create',compact('appointment'));
    }
}
