<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewConsultationNotification;
use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $userId = auth()->user()->id;

        $consultations = Consultation::whereRaw("JSON_CONTAINS(doctor_id, '\"$userId\"')")->paginate(10);

        // return $consultations;
        return view('pages.consultations.index',compact('consultations'));
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
            'title' => 'required',
            'appointment_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);

        $data['doctor_id'] = json_encode($data['doctor_id']);

        $consultation = Consultation::create($data);

        SendNewConsultationNotification::dispatch($consultation->created_by, $consultation->id);

        return redirect()->back()->with('success', 'Consultation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Consultation $consultation)
    {
        $patient = $consultation->appointment->patient;
        $appointment = $consultation->appointment;
        $previousDiagnoses = $patient->diagnoses;
        return view('pages.consultations.show',compact('consultation','previousDiagnoses','appointment'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Consultation $consultation)
    {
        return view('pages.consultations.edit',compact('consultation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Consultation $consultation)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Consultation $consultation)
    {
        //
    }
}
