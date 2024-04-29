<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
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
            'appointment_id' => 'required',
            'patient_id' => 'required',
            'branch_id' => 'required',
            'doctor_id' => 'required',
            'dosage' => 'required',
            'frequency' => 'required',
            'amount' => 'required',
        ]);
        $data['description'] = json_encode($data['description']);
        $data['dosage'] = json_encode($data['dosage']);
        $data['frequency'] = json_encode($data['frequency']);
        $data['amount'] = json_encode($data['amount']);


        Prescription::create($data);


        return redirect()->back()->with('success', 'Patient created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prescription $prescription)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Prescription $prescription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Prescription $prescription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Prescription $prescription)
    {
        //
    }

    public function printCard($appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);

        $prescriptions = Prescription::where('appointment_id', $appointmentId)->get();

        $patient = $appointment->patient;

        return view('pages.prescriptions.print_card', compact('appointment','prescriptions','patient'));
    }
}
