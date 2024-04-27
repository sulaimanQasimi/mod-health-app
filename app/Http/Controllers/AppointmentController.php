<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\LabType;
use App\Models\Room;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retrieve all appointments
        $appointments = Appointment::all();

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
            'date' => 'required',
            'time' => 'required',
        ]);

        // Create a new appointment
        Appointment::create($validatedData);

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

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $labTypes = LabType::all();
        $doctors = Doctor::all();
        $rooms = Room::all();
        $beds = Bed::all();
        return view('pages.appointments.show',compact('appointment','labTypes','doctors','rooms','beds'));
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
        $appointments = Appointment::where('doctor_id', auth()->user()->id)->get();

        return view('pages.appointments.index', compact('appointments'));
    }

}
