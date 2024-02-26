<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::latest()->paginate(10);
        return view('pages.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('pages.patients.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
        ]);

        $patient = Patient::create($data);


        return redirect()->route('patients.index')->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $doctors = Doctor::all();
        return view('pages.patients.show', compact('patient','doctors'));
    }

    public function edit(Patient $patient)
    {
        return view('pages.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
        ]);

        $patient->update($data);

        return redirect()->route('patients.index')->with('success', 'Patient updated successfully.');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', 'Patient deleted successfully.');
    }

    public function printCard(Patient $patient)
    {
        return view('pages.patients.print_card', compact('patient'));
    }
}
