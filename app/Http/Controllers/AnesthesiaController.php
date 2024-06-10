<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewAnesthesiaNotification;
use App\Jobs\SendNewOperationNotification;
use App\Models\Anesthesia;
use App\Models\User;
use Illuminate\Http\Request;

class AnesthesiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function new()
    {
        $anesthesias = Anesthesia::where('status', 'new')->latest()->paginate(10);

        return view('pages.anesthesias.new', compact('anesthesias'));
    }

    public function approved()
    {
        $anesthesias = Anesthesia::where('status', 'approved')->latest()->paginate(10);

        return view('pages.anesthesias.approved', compact('anesthesias'));
    }

    public function rejected()
    {
        $anesthesias = Anesthesia::where('status', 'rejected')->latest()->paginate(10);

        return view('pages.anesthesias.rejected', compact('anesthesias'));
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
        $data = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'required',
            'operation_type_id' => 'required',
            'hospitalization_id' => 'nullable',
            'date' => 'required',
            'time' => 'required',
            'plan' => 'required',
            'position_on_bed' => 'required',
            'planned_duration' => 'required',
            'estimated_blood_waste' => 'required',
            'other_problems' => 'required',
            'status' => 'nullable',
            'anesthesia_type' => 'nullable',
            'operation_status' => 'nullable',
            'anesthesia_log_reply' => 'nullable',
            'is_operation_done' => 'nullable',
            'operation_assistants_id' => 'nullable',
            'operation_surgion_id' => 'nullable',
            'operation_anesthesia_log_id' => 'nullable',
            'operation_anesthesist_id' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable',
            'operation_circulation_nurse_id' => 'nullable',
            'anesthesia_plan' => 'nullable',
            'operation_expense_remarks' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',
            'is_reserved' => 'nullable',
            'reserve_reason' => 'nullable',
        ]);

        $data['operation_assistants_id'] = json_encode($data['operation_assistants_id']);

        // Create a new appointment
        $anesthesia = Anesthesia::create($data);

        SendNewAnesthesiaNotification::dispatch($anesthesia->created_by, $anesthesia->id);


        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', 'Anesthesia created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $anesthesia)
    {
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        return view('pages.anesthesias.show', compact('anesthesia','operation_doctors'));
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
        $data = $request->validate([
            'anesthesia_log_reply' => 'required',
            'status' => 'nullable',
            'anesthesia_type' => 'nullable',
            'is_operation_done' => 'nullable',
            'operation_remark' => 'nullable',
            'anesthesia_plan' => 'nullable',
            'operation_anesthesia_log_id' => 'nullable',
            'operation_anesthesist_id' => 'nullable',

        ]);

        $anesthesia->update($data);

        if ($data['status'] == 'approved') {
            SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);
        }


        return redirect()->route('anesthesias.new')->with('success', 'Anesthesia updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anesthesia $anesthesia)
    {
        //
    }
}
