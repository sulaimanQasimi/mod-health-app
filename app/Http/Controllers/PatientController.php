<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Province;
use App\Models\Recipient;
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
        $recipients = Recipient::all();
        $provinces = Province::all();
        $districts = District::all();
        return view('pages.patients.create',compact('recipients','provinces','districts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'last_name' => 'required',
            'phone' => 'required',
            'nid' => 'required',
            'province_id' => 'required',
            'district_id' => 'required',
            'referred_by' => 'required',
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

    public function addImage(Request $request, $id)
    {
        // Find the patient by ID
        $patient = Patient::findOrFail($id);

        // Handle the image upload
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/patients'), $imageName);

            // Update the patient record with the image path
            $patient->image = $imageName;
            $patient->save();

            return redirect()->back()->with('success', 'Image added successfully.');
        }

        return redirect()->back()->with('error', 'No image uploaded.');
    }
}
