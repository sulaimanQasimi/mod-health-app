<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use Illuminate\Http\Request;

class AnesthesiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
            'appointment_id' => 'required',
            'operation_type_id' => 'required',
            'date' => 'required',
            'time' => 'required',
            'plan' => 'required',
            'position_on_bed' => 'required',
            'planned_duration' => 'required',
            'estimated_blood_waste' => 'required',
            'other_problems' => 'required',
            'status' => 'nullable',
            'anesthesia_log_reply' => 'nullable',
        ]);

        // Create a new appointment
        Anesthesia::create($validatedData);

        // Redirect to the appointments index page with a success message
        return redirect()->route('anesthesias.index')->with('success', 'Anesthesia created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $anesthesia)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anesthesia $anesthesia)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anesthesia $anesthesia)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anesthesia $anesthesia)
    {
        //
    }
}
