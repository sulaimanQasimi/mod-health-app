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
        $prescriptions = Prescription::where('branch_id',auth()->user()->branch_id)->where('is_delivered',false)->latest()->paginate(10);
        return view('pages.prescriptions.index',compact('prescriptions'));
    }

    /**
     * Display a listing of the resource.
     */
    public function delivered()
    {
        $prescriptions = Prescription::where('branch_id',auth()->user()->branch_id)->where('is_delivered',true)->latest()->paginate(10);
        return view('pages.prescriptions.delivered',compact('prescriptions'));
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
            'type' => 'required',
            'is_delivered' => 'required',
        ]);
        $data['description'] = json_encode($data['description']);
        $data['dosage'] = json_encode($data['dosage']);
        $data['frequency'] = json_encode($data['frequency']);
        $data['amount'] = json_encode($data['amount']);
        $data['type'] = json_encode($data['type']);


        Prescription::create($data);


        return redirect()->back()->with('success', 'Prescription created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Prescription $prescription)
    {
        return view('pages.prescriptions.show',compact('prescription'));
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

    public function updateStatus($prescriptionId, $key)
{
    // Find the prescription by ID
    $prescription = Prescription::findOrFail($prescriptionId);

    // Update the status of the specified key
    $statuses = is_array($prescription->is_delivered) ? $prescription->is_delivered : json_decode($prescription->is_delivered, true);
    $updatedStatus = $statuses[$key] === 0 ? 1 : 0;
    $statuses[$key] = $updatedStatus;
    $prescription->is_delivered = json_encode($statuses);
    $prescription->save();

    // Return a response
    return response()->json(['status' => 'success']);
}
}
