<?php

namespace App\Http\Controllers;

use App\Models\PatientComplaint;
use Illuminate\Http\Request;

class PatientComplaintController extends Controller
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
            'description' => 'required',
            'hospitalization_id' => 'nullable',

        ]);

        PatientComplaint::create($data);


        return redirect()->back()->with('success', 'Complaint created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientComplaint $patientComplaint)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientComplaint $patientComplaint)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PatientComplaint $patientComplaint)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientComplaint $patientComplaint)
    {
        //
    }
}
