<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;


class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::where('branch_id',auth()->user()->branch_id)->latest()->paginate(10);
        return view('pages.patients.index', compact('patients'));
    }

    public function create()
    {
        $recipients = Recipient::all();
        $provinces = Province::all();
        $districts = District::all();
        $relations = Relation::all();
        return view('pages.patients.create',compact('recipients','provinces','districts','relations'));
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
            'relation_id' => 'required',
            'referred_by' => 'required',
            'branch_id' => 'required',
            'job' => 'nullable',
            'rank' => 'nullable',
        ]);

        $patient = Patient::create($data);


        return redirect()->route('patients.index')->with('success', 'Patient created successfully.');
    }

    public function show(Patient $patient)
    {
        $departments = Department::all();
        $doctors = Doctor::all();
        return view('pages.patients.show', compact('patient','departments','doctors'));
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

    public function webcam(Patient $patient)
    {
        return view('pages.patients.webcam', compact('patient'));
    }



public function addImage(Request $request, $id)
{
    $patient = Patient::findOrFail($id);
    $img = $request->image;

    $folderPath = "images/patients/";

    $image_parts = explode(";base64,", $img);
    $image_type_aux = explode("image/", $image_parts[0]);
    $image_type = $image_type_aux[1];

    $image_base64 = base64_decode($image_parts[1]);

    $fileName = uniqid() . '.png';

    $file = $folderPath . $fileName;

    // Save the image to the public folder
    $publicPath = public_path($file);
    File::put($publicPath, $image_base64);

    // Update the patient's image column with the image path
    $patient->image = $file;
    $patient->save();

    return redirect()->route('patients.show',$patient)->with('success', 'Image added successfully.');
}


    public function scanQrCode(Request $request)
    {
        // Get the scanned QR code data
        $qrCodeData = $request->input('qrCodeData');

        // Find the patient based on the QR code data
        $patient = Patient::where('id', $qrCodeData)->where('branch_id',auth()->user()->branch_id)->first();

        if ($patient) {
            // Redirect to the patient's show page
            return redirect()->route('patients.show', $patient->id);
        } else {
            // Handle the case when the patient is not found
            return redirect()->back()->with('error', localize('global.patient_not_found'));
        }
    }

    public function scanCode()
    {
        return view('pages.patients.scan');
    }
}
