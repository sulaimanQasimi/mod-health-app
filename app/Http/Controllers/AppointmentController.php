<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewAppointmentNotification;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrintedNumber;
use App\Models\FoodType;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\OperationType;
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use HanifHefaz\Dcter\Dcter;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Mpdf\Mpdf;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */

     public function index(Request $request)
     {
         if ($request->ajax()) {
             $appointments = Appointment::where('branch_id', auth()->user()->branch_id)
                 ->with(['patient', 'doctor'])
                 ->latest()
                 ->get()
                 ->map(function ($appointment) {
                     $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
                     return $appointment;
                 });
     
             return response()->json([
                 'data' => $appointments,
             ]);
         }
     
         $appointments = Appointment::where('branch_id', auth()->user()->branch_id)
             ->with('patient', 'doctor')
             ->latest()
             ->get();
         return view('pages.appointments.index', compact('appointments'));
     }

    public function create()
    {
        $doctors = Doctor::all();
        return view('pages.appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        // Validate the input - doctor_id is optional for department referrals
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'nullable',
            'branch_id' => 'required',
            'department_id' => 'required',
            'is_completed' => 'nullable',
            'status_remark' => 'nullable',
            'refferal_remarks' => 'nullable',
        ]);

        // Set current date and time for the appointment
        $now = now();
        $validatedData['date'] = $now->format('Y-m-d');
        $validatedData['time'] = $now->format('H:i:s');

        if ($request->has('current_appointment_id')) {
            $current_appointmentId = $request->input('current_appointment_id');

            $current_appointment = Appointment::findOrFail($current_appointmentId);

            // Mark the current appointment as completed with referral remarks
            $current_appointment->update([
                'is_completed' => '1', 
                'refferal_remarks' => $request->refferal_remarks
            ]);
            
            // Create the new appointment for the referred department (without doctor_id)
            $appointment = Appointment::create($validatedData);

            SendNewAppointmentNotification::dispatch($appointment->created_by, $appointment->id);
            return redirect()->route('appointments.completedAppointments')->with('success', localize('global.patient_referred_successfully'));
        } else {
            // Create a new appointment
            $appointment = Appointment::create($validatedData);

            SendNewAppointmentNotification::dispatch($appointment->created_by, $appointment->id);
        }

        // Handle AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            $patient = Patient::find($appointment->patient_id);
            $now = now();
            
            $response = [
                'success' => true,
                'message' => localize('global.appointment_created_successfully'),
                'patient' => [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'last_name' => $patient->last_name,
                ],
                'appointment' => [
                    'id' => $appointment->id,
                    'department' => $appointment->department->name ?? '',
                    'doctor' => $appointment->doctor->name ?? '',
                    'date' => $now->format('Y-m-d'),
                    'time' => $now->format('H:i:s'),
                    'token_url' => route('appointments.printToken', $appointment->id)
                ]
            ];
            return response()->json($response);
        }

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.index')->with('success', localize('global.appointment_created_successfully'));
    }

    public function edit(Appointment $appointment)
    {
        // Get necessary data for the edit form
        $doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $patients = Patient::all();
        $branches = Branch::all();
        
        return view('pages.appointments.edit', compact('appointment', 'doctors', 'patients', 'branches'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        // Validate the input
        $validatedData = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'branch_id' => 'required',
            'refferal_remarks' => 'nullable|string',
            // Add any other validation rules as needed
        ]);
        
        // Update the appointment
        $appointment->update($validatedData);

        // Redirect to the appointments show page with a success message
        return redirect()->route('appointments.show', $appointment)->with('success', localize('global.appointment_updated_successfully'));
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
        return redirect()->route('appointments.completedAppointments')->with('success', localize('global.appointment_updated_successfully.'));
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
        $departments = Department::all();
        $patient = $appointment->patient;
        $previousDiagnoses = $patient->diagnoses;
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        $medicineUsageTypes = MedicineUsageType::all();
        
        // Add physiotherapy data
        $physiotherapyTypes = \App\Models\PhysiotherapyType::all();
        $physiotherapists = User::where('branch_id', auth()->user()->branch_id)->get();

        return view('pages.appointments.show', compact('appointment', 'labTypes', 'doctors', 'rooms', 'beds', 'previousDiagnoses', 'labTypeSections', 'branches', 'operationTypes', 'operation_doctors', 'departments', 'medicineTypes', 'medicines', 'foodTypes', 'relations', 'medicineUsageTypes', 'physiotherapyTypes', 'physiotherapists'));
    }

    public function destroy(Appointment $appointment)
    {
        // Delete the appointment
        $appointment->delete();

        // Redirect to the appointments index page with a success message
        return redirect()->route('appointments.index')->with('success', localize('global.appointment_deleted_successfully'));
    }

    public function doctorAppointments(Request $request)
    {
        if ($request->ajax()) {
            $appointments = Appointment::where('doctor_id', auth()->user()->id)
                ->where('is_completed', '0')
                ->with(['patient', 'doctor', 'referringDoctor'])
                ->latest()
                ->get()
                ->map(function ($appointment) {
                    $appointment->jalali_date =Dcter::GregorianToJalali($appointment->date);
                    return $appointment;
                });

                
            if ($appointments) {
                return response()->json([
                    'data' => $appointments,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $appointments = Appointment::where('doctor_id', auth()->user()->id)
            ->where('is_completed', '0')
            ->with(['patient', 'doctor', 'referringDoctor'])
            ->latest()
            ->get();
        return view('pages.appointments.doctor_appointments', compact('appointments'));
    }

    public function completedAppointments(Request $request)
    {
        if ($request->ajax()) {
            $appointments = Appointment::where('doctor_id', auth()->user()->id)
                ->where('is_completed', '1')
                ->with(['patient', 'doctor'])
                ->latest()
                ->get()
                ->map(function ($appointment) {
                    $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
                    return $appointment;
                });

            if ($appointments) {
                return response()->json([
                    'data' => $appointments,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $appointments = Appointment::where('doctor_id', auth()->user()->id)
            ->where('is_completed', '1')
            ->with(['patient', 'doctor'])
            ->latest()
            ->get();
        return view('pages.appointments.completed', compact('appointments'));
    }

    public function departmentAppointments(Request $request)
    {
        // Get the current user's department
        $userDepartment = auth()->user()->department_id;
        
        if (!$userDepartment) {
            return redirect()->back()->with('error', localize('global.no_department_assigned'));
        }

        if ($request->ajax()) {
            $appointments = Appointment::where('department_id', $userDepartment)
                ->whereNull('doctor_id') // Only appointments without assigned doctor
                ->with(['patient', 'department', 'referringDoctor'])
                ->latest()
                ->get()
                ->map(function ($appointment) {
                    $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
                    return $appointment;
                });

            if ($appointments) {
                return response()->json([
                    'data' => $appointments,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $appointments = Appointment::where('department_id', $userDepartment)
            ->whereNull('doctor_id')
            ->with(['patient', 'department', 'referringDoctor'])
            ->latest()
            ->get();
        return view('pages.appointments.department', compact('appointments'));
    }

    public function acceptAppointment(Request $request, Appointment $appointment)
    {
        // Check if the appointment belongs to the user's department
        if ($appointment->department_id !== auth()->user()->department_id) {
            return redirect()->back()->with('error', localize('global.unauthorized_access'));
        }

        // Check if appointment is already assigned to a doctor
        if ($appointment->doctor_id) {
            return redirect()->back()->with('error', localize('global.appointment_already_assigned'));
        }

        // Assign the current doctor to the appointment
        $appointment->update([
            'doctor_id' => auth()->user()->id
        ]);

        return redirect()->route('appointments.show', $appointment->id)
            ->with('success', localize('global.appointment_accepted_successfully'));
    }

    public function report()
    {
        return view('pages.appointments.reports.index');
    }
    public function reportSearch(Request $request)
    {
        $query = DB::table('appointments as a')->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')->select('a.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'a.is_completed', 'a.status_remark', 'a.refferal_remarks', 'a.date', 'a.time');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%' . $request->patient_name . '%');
        }

        if ($request->filled('date')) {
            $query->where('a.date', $request->date);
        }

        if ($request->filled('time')) {
            $query->where('a.time', $request->time);
        }

        if ($request->filled('is_completed')) {
            $query->where('a.is_completed', $request->is_completed);
        }

        $items = $query->get();
        return view('pages.appointments.reports.report', ['items' => $items]);
    }

    public function exportReport(Request $request)
    {
        $data = json_decode($request->data, true);

        $items = DB::table('appointments as a')->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')->select('a.id', 'p.name as patient_name', 'd.name as doctor_name', 'b.name as branch_name', 'a.is_completed', 'a.status_remark', 'a.refferal_remarks', 'a.date', 'a.time')->whereIn('a.id', $data)->get();
        $reader = new Xlsx();
        $spreadsheet = $reader->load('report_templates/appointment_report.xlsx');
        $sheet = $spreadsheet->getActiveSheet();
        $html = view('pages.appointments.reports.pdf_report', ['items' => $items])->render();
        if ($request->type == 'pdf') {
            $mpdf = new Mpdf(['format' => 'A4-L']);
            $mpdf->WriteHTML($html);
            $mpdf->Output('pdf_report.pdf', 'D');
        } else {
            $spreadsheet = $reader->load('report_templates/appointment_report.xlsx');
            $sheet = $spreadsheet->getActiveSheet();
            $row = 3;

            foreach ($items as $index => $item) {
                $sheet
                    ->getStyle('A2:G' . $sheet->getHighestRow())
                    ->getAlignment()
                    ->setWrapText(true);
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(40);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(20);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(40);
                $styleArray = [
                    'font' => [
                        'name' => 'B Nazanin',
                        'color' => 15,
                        'bold' => true,
                    ],
                ];

                $status = '';
                if ($item->is_completed == '1') {
                    $status = 'ملاقات های تکمیل شده';
                } else {
                    $status = 'ملاقات های در حال اجراٰ';
                }
                $sheet->setCellValue('A' . $row . '', ++$index);
                $sheet->setCellValue('B' . $row . '', $item->patient_name);
                $sheet->setCellValue('C' . $row . '', $item->doctor_name);
                $sheet->setCellValue('D' . $row . '', $item->branch_name);
                $sheet->setCellValue('E' . $row . '', $status);
                $sheet->setCellValue('F' . $row . '', $item->date);
                $sheet->setCellValue('G' . $row . '', $item->time);
                $row++;
            }

            return $this->exportResponse($spreadsheet);
        }
    }

    public function exportResponse($spreadsheet)
    {
        $writer = new WriterXlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="item_report.xls"');
        $response->headers->set('Cache-Control', 'max-age=0');
        return $response;
    }
    
    public function printToken(Appointment $appointment)
    {
        $patient = $appointment->patient;
        $today = Carbon::today();
        
        
        // Check if doctor has department_id
        if (!$appointment->department_id) {
            return redirect()->back()->with('error', localize('global.doctor_department_not_found'));
        }
        
        $departmentId = $appointment->department_id;

        // Check if patient already has a token for today in this department
        $existingToken = PrintedNumber::where('patient_id', $patient->id)
            ->where('date', $today)
            ->where('department_id', $departmentId)
            ->first();

        // If token already exists, return the existing token
        if ($existingToken) {
            $printedNumber = $existingToken;
            return view('pages.patients.token', [
                'patient'=>$patient, 
                'printedNumber'=>$printedNumber,
                'name'=>$appointment->doctor?->name,
                'appointment'=>$appointment
                ]);
        }

        // Get the maximum printed number for today for this specific department
        $maxNumber = PrintedNumber::where('date', $today)
            ->where('department_id', $departmentId)
            ->max('number');

        // Assign the next number for this department
        $newNumber = ($maxNumber ? $maxNumber : 0) + 1;

        // Store the new printed number for the patient with department ID
        PrintedNumber::create([
            'patient_id' => $patient->id,
            'number' => $newNumber,
            'date' => $today,
            'department_id' => $departmentId,
        ]);

        // Retrieve the printed number for the view
        $printedNumber = PrintedNumber::where('patient_id', $patient->id)
            ->where('date', $today)
            ->where('department_id', $departmentId)
            ->latest() // Get the latest entry for today
            ->firstOrFail(); // Ensure it retrieves today's printed number
        return view('pages.patients.token',[
'patient'=>$patient, 
'printedNumber'=>$printedNumber,
'name'=>$appointment->doctor?->name,
'appointment'=>$appointment

]);
    }
}
