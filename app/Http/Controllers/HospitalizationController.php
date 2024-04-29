<?php

namespace App\Http\Controllers;

use App\Models\Hospitalization;
use Illuminate\Http\Request;

class HospitalizationController extends Controller
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
        $data = $request->validate([
            'reason' => 'required',
            'remarks' => 'required',
            'room_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'bed_id' => 'required',
            'appointment_id' => 'required',
        ]);

        Hospitalization::create($data);

        return redirect()->back()->with('success', 'Hospitalization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hospitalization $hospitalization)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospitalization $hospitalization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hospitalization $hospitalization)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospitalization $hospitalization)
    {
        //
    }
}
