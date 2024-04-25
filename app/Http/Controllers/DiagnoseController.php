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
        ]);

        Diagnose::create($data);

        return redirect()->back()->with('success', 'Diagnose created successfully.');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diagnose $diagnose)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagnose $diagnose)
    {
        //
    }

    public function createDiagnose(Appointment $appointment)
    {
        return view('pages.diagnoses.create',compact('appointment'));
    }
}
