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
    public function index(Request $request)
    {

        if ($request->ajax()) {
            $patients = Patient::where('branch_id',auth()->user()->branch_id)->with('province')->latest()->get();

                if ($patients) {
                    return response()->json([
                        'data' => $patients,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $patients = Patient::where('branch_id',auth()->user()->branch_id)->latest()->get();
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
            'last_name' => 'nullable',
            'father_name' => 'nullable',
            'phone' => 'nullable',
            'age' => 'required',
            'nid' => 'required',
            'province_id' => 'required',
            'district_id' => 'required',
            'relation_id' => 'nullable',
            'branch_id' => 'required',
            'job' => 'nullable',
            'rank' => 'nullable',
            'age' => 'nullable',
            'job_type' => 'nullable',
            'gender' => 'required',
            'referral_name' => 'nullable',
            'referral_last_name' => 'nullable',
            'referral_father_name' => 'nullable',
            'referral_nid' => 'nullable',
            'referral_id_card' => 'nullable',
            'referral_phone' => 'nullable',
            'referral_recipient' => 'nullable',
            'type' => 'nullable',
            'id_card' => 'nullable',
            'job_category' => 'nullable',
            'referred_by' => 'nullable'
        ]);


        $patient = Patient::create($data);


        return redirect()->route('patients.index')->with('success', localize('global.patient_created_successfully.'));
    }

    public function show(Patient $patient)
    {
        $departments = Department::all();
        $doctors = Doctor::all();
        $previousDiagnoses = $patient->diagnoses;
        return view('pages.patients.show', compact('patient','departments','doctors','previousDiagnoses'));
    }

    public function edit(Patient $patient)
    {
        $recipients = Recipient::all();
        $provinces = Province::all();
        $districts = District::all();
        $relations = Relation::all();
        return view('pages.patients.edit',compact('recipients','provinces','districts','relations','patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $data = $request->validate([
            'name' => 'required',
            'last_name' => 'nullable',
            'father_name' => 'nullable',
            'phone' => 'nullable',
            'age' => 'nullable',
            'nid' => 'nullable',
            'province_id' => 'nullable',
            'district_id' => 'nullable',
            'relation_id' => 'nullable',
            'referred_by' => 'nullable',
            'branch_id' => 'nullable',
            'job' => 'nullable',
            'rank' => 'nullable',
            'age' => 'nullable',
            'job_type' => 'nullable',

        ]);

        $patient->update($data);

        return redirect()->route('patients.index')->with('success', localize('global.patient_updated_successfully.'));
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index')->with('success', localize('global.patient_deleted_successfully.'));
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

    return redirect()->route('patients.show',$patient)->with('success', localize('global.patient_image_created_successfully.'));
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

    public function history(Patient $patient)
    {
        $appointments = $patient->appointments;
        $previousDiagnoses = $patient->diagnoses;
        $previousConsultations = $patient->consultations;
        $previousAnesthesias = $patient->anesthesias;
        $previousHospitalizations = $patient->hospitalizations;
        $previousLabs = $patient->labs;
        $previousPrescriptions = $patient->prescriptions;
        $previousIcus = $patient->icus;
        return view('pages.patients.history',compact('patient','previousDiagnoses','previousConsultations','previousAnesthesias',
    'previousHospitalizations','previousLabs','previousPrescriptions','previousIcus','appointments'));
    }

    public function getTab(Request $request){
        $recipients = Recipient::all();
        $provinces = Province::all();
        $districts = District::all();
        $relations = Relation::all();

        $tab_type = $request->tab_type;

        if($tab_type == 'first'){
            return view('pages.patients.tab1',compact('recipients','provinces','districts','relations'));
        }elseif($tab_type == 'second'){
            return view('pages.patients.tab2',compact('recipients','provinces','districts','relations'));
        }elseif($tab_type == 'third'){
            return view('pages.patients.tab3',compact('recipients','provinces','districts','relations'));
        }
    }
}
