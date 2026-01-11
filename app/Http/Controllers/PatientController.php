<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Department;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrintedNumber;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\Relation;
use App\Models\MiliteryType;
use App\Models\User;
use Carbon\Carbon;
use HanifHefaz\Dcter\Dcter;
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
use App\Jobs\SendNewAppointmentNotification;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        // get all patients with militery type, province, district
        $query = Patient::where('branch_id', auth()->user()->branch_id)
            ->with(['militeryType', 'province', 'district', 'creator']);


        // Filter by name
        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Filter by father name
        if ($request->filled('father_name')) {
            $query->where('father_name', 'like', '%' . $request->father_name . '%');
        }

        // Filter by last name
        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', '%' . $request->last_name . '%');
        }

        // Filter by phone number
        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->phone . '%');
        }

        // Search by card (id_card)
        if ($request->filled('card_search')) {
            $cardSearch = $request->card_search;
            $query->where('id_card', 'like', '%' . $cardSearch . '%');
        }

        // Filter by militery type
        if ($request->filled('militery_type_id')) {
            $query->where('militery_type_id', $request->militery_type_id);
        }

        // Filter by province
        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by gender 0 for male and 1 for female
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filter by job category 0 for civilian and 1 for military
        if ($request->filled('job_category')) {
            $query->where('job_category', $request->job_category);
        }

        $patients = $query->latest()->paginate(15);
        if ($request->hasAny(['name', 'father_name', 'last_name', 'nid', 'job_category'])) {
            $patients->appends($request->query());
        }

        // Get data for filters militery type, province
        $militeryTypes = MiliteryType::all();
        $provinces = Province::all();

        return view('pages.patients.index', compact('patients', 'militeryTypes', 'provinces'));
    }

    public function create()
    {
        $relations = Relation::all();
        ;
        return view('pages.patients.create', compact('relations'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'militery_type_id' => 'nullable|exists:militery_types,id',
            'name' => 'required',
            'last_name' => 'nullable',
            'father_name' => 'nullable',
            'phone' => 'nullable',
            'age' => 'nullable',
            'age_day' => 'nullable|integer|min:0|max:31',
            'age_month' => 'nullable|integer|min:0|max:11',
            'age_year' => 'nullable|integer|min:0|max:150',
            'nid' => 'required',
            'province_id' => 'required',
            'district_id' => 'required',
            'relation_id' => 'nullable',
            'branch_id' => 'required',
            'job' => 'nullable',
            'rank' => 'nullable',
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
            'id_card' => 'nullable|string',
            'job_category' => 'nullable',
            'referred_by' => 'nullable',
            // Appointment validation
            'appointment_doctor_id' => 'nullable|exists:doctors,id',
            'appointment_department_id' => 'required_with:appointment_doctor_id|exists:departments,id'
        ]);

        // Format age from dropdowns if provided (priority: year > month > day)
        if (!$data['age'] || empty($data['age'])) {
            if ($request->filled('age_year') && $request->age_year !== '') {
                $data['age'] = $request->age_year . ' ساله';
            } elseif ($request->filled('age_month') && $request->age_month !== '') {
                $data['age'] = $request->age_month . ' ماه';
            } elseif ($request->filled('age_day') && $request->age_day !== '') {
                $data['age'] = $request->age_day . ' روز';
            }
        }

        // Ensure age is required
        if (empty($data['age'])) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.validation_error'),
                    'errors' => ['age' => ['The age field is required.']]
                ], 422);
            }
            return redirect()->back()->withErrors(['age' => 'The age field is required.'])->withInput();
        }

        // Remove age_day, age_month, age_year from data as they're not in the model
        unset($data['age_day'], $data['age_month'], $data['age_year']);

        $patient = Patient::create($data);
        $appointment = null;

        // Create appointment if doctor and department are selected
        if ($request->filled('appointment_doctor_id')
         || 
        $request->filled('appointment_department_id')) {
            $now = now();
            $appointmentData = [
                'patient_id' => $patient->id,
                'doctor_id' => $request->appointment_doctor_id,
                'department_id' => $request->appointment_department_id,
                'branch_id' => $patient->branch_id,
                'date' => $now->format('Y-m-d'),
                'time' => $now->format('H:i:s'),
                'is_completed' => 0
            ];

            $appointment = Appointment::create($appointmentData);
            
            // Send notification for new appointment
            SendNewAppointmentNotification::dispatch($appointment->created_by, $appointment->id);
        }

        // Handle AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            $response = [
                'success' => true,
                'message' => localize('global.patient_created_successfully.'),
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'last_name' => $patient->last_name,
                ]
            ];

            if ($appointment) {
                $response['appointment'] = [
                    'id' => $appointment->id,
                    'department' => $appointment->department->name ?? '',
                    'doctor' => $appointment->doctor->name ?? '',
                    'date' => $appointment->date,
                    'time' => $appointment->time,
                    'token_url' => route('appointments.printToken', $appointment->id)
                ];
            }

            return response()->json($response);
        }

        // Handle non-AJAX requests (backward compatibility)
        if ($appointment) {
            return redirect()->route('appointments.show', $appointment->id)->with('success', localize('global.patient_created_successfully.'));
        }

        return redirect()->route('patients.show', $patient->id)->with('success', localize('global.patient_created_successfully.'));
    }

    public function show(Patient $patient)
    {
        $departments = auth()->user()->category_id 
            ? Department::where('category_id', auth()->user()->category_id)->get()
            : Department::all();
        $doctors = Doctor::all();
        $previousDiagnoses = $patient->diagnoses;
        return view('pages.patients.show', compact('patient', 'departments', 'doctors', 'previousDiagnoses'));
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

        return redirect()->route('patients.show', $patient)->with('success', localize('global.patient_image_created_successfully.'));
    }


    public function scanQrCode(Request $request)
    {
        // Get the scanned QR code data
        $qrCodeData = $request->input('qrCodeData');

        // Find the patient based on the QR code data
        $patient = Patient::where('id', $qrCodeData)->where('branch_id', auth()->user()->branch_id)->first();

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
        try {
            // Eager load appointments with their labs and related data
            $appointments = $patient->appointments()->with([
                'labs.labType',
                'labs.results.parameter',
                'doctor'
            ])->get();
            
            // Load all related data with proper error handling
            $previousDiagnoses = $patient->diagnoses()->get();
            $previousConsultations = $patient->consultations()->with('associated_departments')->get();
            $previousAnesthesias = $patient->anesthesias()->with(['operationType', 'surgion'])->get();
            $previousHospitalizations = $patient->hospitalizations()->with(['room', 'bed'])->get();
            $previousLabs = $patient->labs; // This is an accessor, returns collection
            $previousPrescriptions = $patient->prescriptions()->with(['doctor', 'prescriptionItems.medicineType', 'prescriptionItems.medicine'])->get();
            $previousIcus = $patient->icus()->get();
            
            return view('pages.patients.history', compact(
                'patient',
                'previousDiagnoses',
                'previousConsultations',
                'previousAnesthesias',
                'previousHospitalizations',
                'previousLabs',
                'previousPrescriptions',
                'previousIcus',
                'appointments'
            ));
        } catch (\Exception $e) {
            // Log the error and return with empty data
            \Log::error('Error loading patient history: ' . $e->getMessage());
            
            return view('pages.patients.history', [
                'patient' => $patient,
                'previousDiagnoses' => collect(),
                'previousConsultations' => collect(),
                'previousAnesthesias' => collect(),
                'previousHospitalizations' => collect(),
                'previousLabs' => collect(),
                'previousPrescriptions' => collect(),
                'previousIcus' => collect(),
                'appointments' => collect(),
            ]);
        }
    }

    public function getTab(Request $request)
    {
        $recipients = Recipient::all();
        $provinces = Province::all();
        $districts = District::all();
        $relations = Relation::all();
        $doctors = Doctor::all();
        $departments = Department::where('category_id', auth()->user()->category_id)->get();

        $tab_type = $request->tab_type;
        $patient_id = $request->patient_id;

        if ($patient_id != '') {
            $patient = Patient::find($patient_id);

            if ($tab_type == 'first') {
                return view('pages.patients.tab1', compact('recipients', 'provinces', 'districts', 'relations', 'patient', 'doctors', 'departments'));
            } elseif ($tab_type == 'second') {
                return view('pages.patients.tab2', compact('recipients', 'provinces', 'districts', 'relations', 'patient', 'doctors', 'departments'));
            } elseif ($tab_type == 'third') {
                return view('pages.patients.tab3', compact('recipients', 'provinces', 'districts', 'relations', 'patient', 'doctors', 'departments'));
            }
        }

        if ($tab_type == 'first') {
            return view('pages.patients.tab1', compact('recipients', 'provinces', 'districts', 'relations', 'doctors', 'departments'));
        } elseif ($tab_type == 'second') {
            return view('pages.patients.tab2', compact('recipients', 'provinces', 'districts', 'relations', 'doctors', 'departments'));
        } elseif ($tab_type == 'third') {
            return view('pages.patients.tab3', compact('recipients', 'provinces', 'districts', 'relations', 'doctors', 'departments'));
        }
    }

    public function getDoctorsByDepartment($departmentId)
    {
            $doctors = Doctor::where('department_id', $departmentId)
                ->where('active_status', true)
                ->get();
            
            return response()->json([
                'success' => true,
                'doctors' => $doctors
            ]);
    }

    public function report()
    {
        $provinces = Province::with('districts')->get();
        $recipients = Recipient::all();
        return view('pages.patients.reports.index', compact('provinces', 'recipients'));
    }
    /**
     * Search and filter patients for reports with pagination
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function reportSearch(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        
        $query = Patient::with(['province', 'district', 'recipient'])
            ->select([
                'patients.id',
                'patients.name',
                'patients.nid',
                'patients.id_card',
                'patients.referral_name',
                'patients.age',
                'patients.gender',
                'patients.job_category',
                'patients.type',
                'patients.referred_by',
                'patients.province_id',
                'patients.district_id',
                'patients.registration_date'
            ]);

        // Apply filters
        $this->applyReportFilters($query, $request);

        // Get paginated results with query string preservation
        $items = $query->paginate($perPage);
        
        // Preserve query parameters in pagination links
        if ($request->hasAny(['patient_name', 'nid', 'id_card', 'referral_name', 'job_category', 
                              'type', 'referred_by', 'age', 'gender', 'province_id', 'district_id', 
                              'from', 'to', 'per_page'])) {
            $items->appends($request->query());
        }

        return view('pages.patients.reports.report', compact('items'));
    }

    /**
     * Apply filters to the patient query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyReportFilters($query, Request $request)
    {
        // Text search filters
        if ($request->filled('patient_name')) {
            $query->where('patients.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('nid')) {
            $query->where('patients.nid', 'like', '%' . $request->nid . '%');
        }

        if ($request->filled('id_card')) {
            $query->where('patients.id_card', $request->id_card);
        }

        if ($request->filled('referral_name')) {
            $query->where('patients.referral_name', 'like', '%' . $request->referral_name . '%');
        }

        // Exact match filters
        if ($request->filled('job_category')) {
            $query->where('patients.job_category', $request->job_category);
        }

        if ($request->filled('type')) {
            $query->where('patients.type', $request->type);
        }

        if ($request->filled('referred_by')) {
            $query->where('patients.referred_by', $request->referred_by);
        }

        if ($request->filled('age')) {
            $query->where('patients.age', $request->age);
        }

        if ($request->filled('gender')) {
            $query->where('patients.gender', $request->gender);
        }

        if ($request->filled('province_id')) {
            $query->where('patients.province_id', $request->province_id);
        }

        if ($request->filled('district_id')) {
            $query->where('patients.district_id', $request->district_id);
        }

        // Date range filter - Convert Persian to Gregorian
        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->from)->datetime();
                $toDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->to)->datetime();
                
                $query->whereDate('patients.registration_date', '>=', $fromDate)
                      ->whereDate('patients.registration_date', '<=', $toDate);
                                           $query->whereHas('appointments', function ($q) use ($fromDate, $toDate) {
                        $q->whereDate('date', '>=', $fromDate)
                          ->whereDate('date', '<=', $toDate);
                    });
     
            } catch (\Exception $e) {
                // Invalid date format, skip date filter
            }
        }

        // Order by registration date descending
        $query->orderBy('patients.registration_date', 'desc');
    }


    public function exportReport(Request $request)
    {

        $data = json_decode($request->data, true);

        $items = DB::table('patients as p')
            ->leftJoin('provinces as pr', 'p.province_id', '=', 'pr.id')
            ->leftJoin('districts as d', 'p.district_id', '=', 'd.id')
            ->leftJoin('recipients as r', 'p.referred_by', '=', 'r.id')
            ->select(
                'p.id',
                'p.name as patient_name',
                'p.nid',
                'p.id_card',
                'p.referral_name',
                'p.age',
                'p.gender',
                'p.job_category',
                'p.type',
                'r.name as referred_by',
                'pr.name_dr as province_name',
                'd.name_dr as district_name'
            )
            ->whereIn('p.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load("report_templates/reception_report.xlsx");
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.patients.reports.pdf_report', ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
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
                } elseif ($item->type == '1') {
                    $type = 'سایر دارات';
                } else {
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


    public function exportResponse($spreadsheet)
    {
        $writer = new WriterXlsx($spreadsheet);
        $response = new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            }
        );
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="item_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;

    }


}
