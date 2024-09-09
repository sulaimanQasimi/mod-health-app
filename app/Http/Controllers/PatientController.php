<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrintedNumber;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\Relation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\DB;
use Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

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
        $relations = Relation::all();
        return view('pages.patients.create',compact('relations'));
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
            'referral_by' => 'nullable',
            'referral_id_card' => 'nullable',
            'referral_phone' => 'nullable',
            'referral_recipient' => 'nullable',
            'type' => 'nullable',
            'id_card' => 'nullable',
            'job_category' => 'nullable',
            'referred_by' => 'nullable',
            'relation_id' => 'nullable'
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
        return view('pages.patients.create',compact('recipients','provinces','districts','relations','patient'));
    }

    public function update(Request $request, Patient $patient)
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
            'referral_by' => 'nullable',
            'referral_id_card' => 'nullable',
            'referral_phone' => 'nullable',
            'referral_recipient' => 'nullable',
            'type' => 'nullable',
            'id_card' => 'nullable',
            'job_category' => 'nullable',
            'referred_by' => 'nullable',
            'relation_id' => 'nullable'

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
        $patient_id = $request->patient_id;

        if($patient_id !=''){
            $patient = Patient::find($patient_id);

            if($tab_type == 'first'){
                return view('pages.patients.tab1',compact('recipients','provinces','districts','relations','patient'));
            }elseif($tab_type == 'second'){
                return view('pages.patients.tab2',compact('recipients','provinces','districts','relations','patient'));
            }elseif($tab_type == 'third'){
                return view('pages.patients.tab3',compact('recipients','provinces','districts','relations','patient'));
            }
        }

        if($tab_type == 'first'){
            return view('pages.patients.tab1',compact('recipients','provinces','districts','relations'));
        }elseif($tab_type == 'second'){
            return view('pages.patients.tab2',compact('recipients','provinces','districts','relations'));
        }elseif($tab_type == 'third'){
            return view('pages.patients.tab3',compact('recipients','provinces','districts','relations'));
        }
    }


    public function report()
    {
        $provinces = Province::all();
        $districts = District::all();
        $recipients = Recipient::all();
        return view('pages.patients.reports.index', compact('provinces', 'districts', 'recipients'));
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('patients as p')
        ->leftJoin('provinces as pr', 'p.province_id' , '=', 'pr.id')
        ->leftJoin('districts as d', 'p.district_id' , '=', 'd.id')
        ->leftJoin('recipients as r', 'p.referred_by' , '=', 'r.id')
        ->select('p.id','p.name as patient_name','p.nid', 'p.id_card', 'p.referral_name', 'p.age', 'p.gender',
        'p.job_category', 'p.type', 'r.name as referred_by', 'pr.name_dr as province_name','d.name_dr as district_name');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('nid')) {
            $query->where('p.nid', 'like', '%' . $request->nid . '%');
        }

        if ($request->filled('id_card')) {
            $query->where('p.id_card', $request->id_card);
        }

        if ($request->filled('referral_name')) {
            $query->where('p.referral_name', 'like', '%' . $request->referral_name . '%');
        }

        if ($request->filled('job_category')) {
            $query->where('p.job_category', $request->job_category);
        }

        if ($request->filled('type')) {
            $query->where('p.type', $request->type);
        }

        if ($request->filled('referred_by')) {
            $query->where('p.referred_by', $request->referred_by);
        }
        if ($request->filled('age')) {
            $query->where('p.age', $request->age);
        }

        if ($request->filled('gender')) {
            $query->where('p.gender', $request->gender);
        }

        if ($request->filled('province_id')) {
            $query->where('p.province_id', $request->province_id);
        }

        if ($request->filled('district_id')) {
            $query->where('p.district_id', $request->district_id);
        }



        $items = $query->get();
    return view('pages.patients.reports.report', ['items' => $items]);

    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items =  DB::table('patients as p')
        ->leftJoin('provinces as pr', 'p.province_id' , '=', 'pr.id')
        ->leftJoin('districts as d', 'p.district_id' , '=', 'd.id')
        ->leftJoin('recipients as r', 'p.referred_by' , '=', 'r.id')
        ->select('p.id','p.name as patient_name','p.nid', 'p.id_card', 'p.referral_name', 'p.age', 'p.gender',
        'p.job_category', 'p.type', 'r.name as referred_by', 'pr.name_dr as province_name','d.name_dr as district_name')
        ->whereIn('p.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/reception_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.patients.reports.pdf_report',  ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        }else {
            $spreadsheet = $reader->load("report_templates/reception_report.xlsx");
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {


                $sheet->getStyle('A2:G' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(20);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(20);
                $sheet->getColumnDimension('K')->setWidth(20);
                $sheet->getColumnDimension('L')->setWidth(20);
                $styleArray = array(
                    'font' => array(
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true

                    ),
                );

                $gender = '';
                if ($item->gender == '0') {
                    $gender = 'مرد';
                } else {
                    $gender = 'زن';
                }

                $job_category = '';
                if ($item->job_category == '0') {
                    $job_category = 'نظامی';
                } else {
                    $job_category = 'ملکی';
                }

                $type = '';
                if ($item->type == '0') {
                    $type = 'وزارت دفاع ملی';
                } elseif($item->type == '1') {
                    $type = 'سایر دارات';
                } else{
                    $type = 'اعضای فامیل و سایرین';
                }
                    $sheet->setCellValue('A' . $row . '', ++$index);
                    $sheet->setCellValue('B' . $row . '', $item->patient_name);
                    $sheet->setCellValue('C' . $row . '', $item->nid);
                    $sheet->setCellValue('D' . $row . '', $item->id_card);
                    $sheet->setCellValue('E' . $row . '', $item->referral_name);
                    $sheet->setCellValue('F' . $row . '', $item->age);
                    $sheet->setCellValue('G' . $row . '', $gender);
                    $sheet->setCellValue('H' . $row . '', $job_category);
                    $sheet->setCellValue('I' . $row . '', $type);
                    $sheet->setCellValue('J' . $row . '', $item->referred_by);
                    $sheet->setCellValue('K' . $row . '', $item->province_name);
                    $sheet->setCellValue('L' . $row . '', $item->district_name);

                $row++;
            }

return $this->exportResponse($spreadsheet);
}
    }


    public function exportResponse($spreadsheet){
        $writer = new WriterXlsx($spreadsheet);
        $response =  new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="item_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;

    }


public function printToken($patientId)
{
    $patient = Patient::findOrFail($patientId);
    $today = Carbon::today();

    // Get the maximum printed number for today across all patients
    $maxNumber = PrintedNumber::where('date', $today)->max('number');

    // Assign the next number
    $newNumber = ($maxNumber ? $maxNumber : 0) + 1;

    // Store the new printed number for the patient
    PrintedNumber::create([
        'patient_id' => $patientId,
        'number' => $newNumber,
        'date' => $today,
    ]);

    // Retrieve the printed number for the view
    $printedNumber = PrintedNumber::where('patient_id', $patientId)
        ->where('date', $today)
        ->latest() // Get the latest entry for today
        ->firstOrFail(); // Ensure it retrieves today's printed number
    return view('pages.patients.token', compact('patient', 'printedNumber'));
}
}
