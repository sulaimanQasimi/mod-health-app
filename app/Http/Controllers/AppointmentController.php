<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewAppointmentNotification;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Diagnose;
use App\Models\Doctor;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\OperationType;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all appointments
        $appointments = Appointment::where('branch_id', auth()->user()->branch_id)->latest()->paginate(15);

        return view('pages.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $doctors = Doctor::all();
        return view('pages.appointments.create',compact('doctors'));
    }

    public function store(Request $request)
    {
        // Validate the input
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'is_completed' => 'nullable',
            'date' => 'required',
            'time' => 'required',
            'status_remark' => 'nullable',
        ]);

        // Create a new appointment
        $appointment = Appointment::create($validatedData);

        SendNewAppointmentNotification::dispatch($appointment->created_by, $appointment->id);

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.index')->with('success', 'Appointment created successfully.');
    }

    public function edit(Appointment $appointment)
    {
        // Show the form to edit an existing appointment
        return view('pages.appointments.edit', compact('appointment'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Validate the input
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'appointment_date' => 'required',
            // Add any other validation rules as needed
        ]);

        // Update the appointment
        $appointment->update($validatedData);

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.index')->with('success', 'Appointment updated successfully.');
    }

    public function changeStatus(Request $request, Appointment $appointment)
    {
        // Validate the input
        $validatedData = $request->validate([
            'is_completed' => 'required',
            'status_remark' => 'nullable',
            // Add any other validation rules as needed
        ]);

        // Update the appointment
        $appointment->update($validatedData);

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.completedAppointments')->with('success', 'Appointment updated successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $labTypes = LabType::all();
        $doctors = User::all();
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $rooms = Room::all();
        $beds = Bed::all();
        $labTypeSections = LabTypeSection::all();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $branches = Branch::all();
        $patient = $appointment->patient;
        $previousDiagnoses = $patient->diagnoses;
        return view('pages.appointments.show',compact('appointment','labTypes','doctors','rooms','beds','previousDiagnoses','labTypeSections','branches','operationTypes','operation_doctors'));
    }

    public function destroy(Appointment $appointment)
    {
        // Delete the appointment
        $appointment->delete();

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.index')->with('success', 'Appointment deleted successfully.');
    }

    public function doctorAppointments()
    {
        $appointments = Appointment::where('doctor_id', auth()->user()->id)->where('is_completed','0')->latest()->paginate(10);

        return view('pages.appointments.index', compact('appointments'));
    }

    public function completedAppointments()
    {
        $appointments = Appointment::where('doctor_id', auth()->user()->id)->where('is_completed','1')->latest()->paginate(10);

        return view('pages.appointments.index', compact('appointments'));
    }

}
