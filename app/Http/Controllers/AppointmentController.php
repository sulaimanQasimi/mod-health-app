<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewAppointmentNotification;
use App\Models\{Appointment, Doctor, Patient, PrintedNumber, FoodType, LabType, Medicine, MedicineType, MedicineUsageType, OperationType, Relation, Room, User};
use App\Models\District;
use App\Models\Province;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use HanifHefaz\Dcter\Dcter;
use Hekmatinasser\Verta\Facades\Verta;
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
         $query = Appointment::where('branch_id', auth()->user()->branch_id)
             ->with(['patient', 'doctor', 'department', 'processedBy']);
     
         // Search by patient name
         if ($request->filled('patient_name')) {
             $query->whereHas('patient', function($q) use ($request) {
                 $q->where('name', 'like', '%' . $request->patient_name . '%')
                   ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
             });
         }
     
        // Search by patient ID card
        if ($request->filled('id_card')) {
            $query->whereHas('patient', function($q) use ($request) {
                $q->where('id_card', 'like', '%' . $request->id_card . '%');
            });
        }
    
        // Filter by patient ID
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
    
        // Filter by doctor
         if ($request->filled('doctor_id')) {
             $query->where('doctor_id', $request->doctor_id);
         }
     
         // Filter by department
         if ($request->filled('department_id')) {
             $query->where('department_id', $request->department_id);
         }
     
         // Filter by completion status
         if ($request->filled('is_completed')) {
             $query->where('is_completed', $request->is_completed);
         }
     
        // Filter by date range
        if ($request->filled('date_from')) {
            // Convert Persian date to Gregorian
            $query->whereDate('date', '>=', Verta::parse($request->date_from)->datetime());
        }
    
        if ($request->filled('date_to')) {
            // Convert Persian date to Gregorian
            $query->whereDate('date', '<=', Verta::parse($request->date_to)->datetime());
        }
     
         // Get paginated results
         $appointments = $query->latest()->paginate(25)->withQueryString();
     
         // Convert dates to Jalali for display
         $appointments->getCollection()->transform(function ($appointment) {
                     $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
                     return $appointment;
                 });
     
         // Get filter data
         $doctors = Doctor::where('branch_id', auth()->user()->branch_id)->get();
         $departments = Department::all();
     
         return view('pages.appointments.index', compact('appointments', 'doctors', 'departments'));
     }

    public function create()
    {
        $doctors = Doctor::all();
        return view('pages.appointments.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        // Validate the input - doctor_id is optional for department referrals
        $rules = [
            'patient_id' => 'required',
            'doctor_id' => 'nullable',
            'branch_id' => 'required',
            'department_id' => 'required',
            'is_completed' => 'nullable',
            'status_remark' => 'nullable',
            'refferal_remarks' => 'nullable',
        ];
        $userClinicType = auth()->user()->clinic_type;
        if ($userClinicType === 'both') {
            $rules['clinic_type'] = 'required|in:hospital,clinic';
        }
        $validatedData = $request->validate($rules);

        // Set current date and time for the appointment
        $now = now();
        $validatedData['date'] = $now->format('Y-m-d');
        $validatedData['time'] = $now->format('H:i:s');

        if ($userClinicType && $userClinicType !== 'both') {
            $validatedData['clinic_type'] = $userClinicType;
        } elseif ($userClinicType === 'both' && !empty($validatedData['clinic_type'])) {
            // already set from request
        }

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
        $doctors = Doctor::where('branch_id', auth()->user()->branch_id)->get();
        $patients = Patient::all();
        $branches = Branch::all();
        
        return view('pages.appointments.edit', compact('appointment', 'doctors', 'patients', 'branches'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        if (
            $appointment->doctor_id
            && $request->doctor_id != $appointment->doctor_id
            && ! $appointment->canChangeDoctor()
        ) {
            return redirect()->back()
                ->withInput()
                ->with('error', localize('global.doctor_can_only_be_changed_once'));
        }

        // Validate the input
        $rules = [
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'date' => 'required|date',
            'time' => 'required',
            'branch_id' => 'required',
            'refferal_remarks' => 'nullable|string',
        ];
        $userClinicType = auth()->user()->clinic_type;
        if ($userClinicType === 'both') {
            $rules['clinic_type'] = 'required|in:hospital,clinic';
        }
        $validatedData = $request->validate($rules);

        if (
            $appointment->doctor_id
            && $validatedData['doctor_id'] != $appointment->doctor_id
        ) {
            $validatedData['doctor_reassigned'] = true;
        }

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
        // Load appointment with necessary relationships
        $appointment->load([
            'hospitalization.labs.labType',
            'hospitalization.labs.results',
            'icu.hospitalization.room',
            'icu.hospitalization.bed'
        ]);
        
        $labTypes = LabType::all();
        $doctors = Doctor::all();
        // Doctors will be loaded via API, no need to pass them here
        // $operation_doctors = Doctor::where('branch_id', auth()->user()->branch_id)
        //     ->where('active_status', true)
        //     ->get();
        $rooms = Room::all();
        $beds = Bed::all();
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
        $physiotherapists = Doctor::query()
            ->where('active_status', true)
            ->where('branch_id', auth()->user()->branch_id)
         
            ->orderBy('name')
            ->get();

        // Load dentist registrations
        $appointment->load('dentistRegistrations.dentist', 'dentistRegistrations.examinations', 'dentistRegistrations.treatments', 'dentistRegistrations.xrays', 'dentistRegistrations.dentalNotes');

        $appointment->load('nephrologyRegistrations.doctor', 'nephrologyRegistrations.patient');

        $appointment->load(['bloodBanks.patient', 'bloodBanks.department']);

        return view('pages.appointments.show', compact('appointment', 'labTypes', 'doctors', 'rooms', 'beds', 'previousDiagnoses', 'branches', 'operationTypes', 'departments', 'medicineTypes', 'medicines', 'foodTypes', 'relations', 'medicineUsageTypes', 'physiotherapyTypes', 'physiotherapists'));
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
        // Get appointments where current user is the assigned doctor
        $query = Appointment::where('processed_by', auth()->user()->id)
            ->where('is_completed', '0')
            ->with(['patient', 'doctor', 'referringDoctor', 'processedBy'])
            ->latest();

        // Extract token_id if provided
        $appointmentId = null;
        if ($request->has('token_id') && $request->token_id !== null && trim($request->token_id) !== '') {
            $tokenId = trim($request->token_id);
            if (is_numeric($tokenId) && $tokenId > 0) {
                $appointmentId = (int)$tokenId;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $tokenId);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $appointmentId = (int)$numericId;
                }
            }
        }

        // Extract patient_id if provided
        $filterPatientId = null;
        if ($request->has('patient_id') && $request->patient_id !== null && trim($request->patient_id) !== '') {
            $patientIdInput = trim($request->patient_id);
            if (is_numeric($patientIdInput) && $patientIdInput > 0) {
                $filterPatientId = (int)$patientIdInput;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $patientIdInput);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $filterPatientId = (int)$numericId;
                }
            }
        }

        // Apply filters
        if ($appointmentId !== null) {
            $query->where('id', $appointmentId);
        }

        if ($filterPatientId !== null) {
            $query->where('patient_id', $filterPatientId);
        }

        if ($request->ajax()) {
            $appointments = $query->get()
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

        $appointments = $query->get();
        return view('pages.appointments.doctor_appointments', compact('appointments'));
    }

    public function completedAppointments(Request $request)
    {
        $query = Appointment::where('processed_by', auth()->user()->id)
            ->where('is_completed', '1')
            ->with(['patient', 'doctor', 'referringDoctor', 'processedBy'])
            ->latest();

        // Extract token_id if provided
        $appointmentId = null;
        if ($request->has('token_id') && $request->token_id !== null && trim($request->token_id) !== '') {
            $tokenId = trim($request->token_id);
            if (is_numeric($tokenId) && $tokenId > 0) {
                $appointmentId = (int)$tokenId;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $tokenId);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $appointmentId = (int)$numericId;
                }
            }
        }

        // Extract patient_id if provided
        $filterPatientId = null;
        if ($request->has('patient_id') && $request->patient_id !== null && trim($request->patient_id) !== '') {
            $patientIdInput = trim($request->patient_id);
            if (is_numeric($patientIdInput) && $patientIdInput > 0) {
                $filterPatientId = (int)$patientIdInput;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $patientIdInput);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $filterPatientId = (int)$numericId;
                }
            }
        }

        // Extract patient_name if provided (search by name, last_name, or father_name)
        $patientName = null;
        if ($request->has('patient_name') && $request->patient_name !== null && trim($request->patient_name) !== '') {
            $patientName = trim($request->patient_name);
        }

        // Apply filters
        if ($appointmentId !== null) {
            $query->where('id', $appointmentId);
        }

        if ($filterPatientId !== null) {
            $query->where('patient_id', $filterPatientId);
        }

        if ($patientName !== null) {
            $term = '%' . $patientName . '%';
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('father_name', 'like', $term);
            });
        }

        $appointments = $query->paginate(25)->withQueryString();
        
        // Add jalali_date to each appointment
        $appointments->getCollection()->transform(function ($appointment) {
            $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
            return $appointment;
        });

        if ($request->ajax()) {
            return response()->json([
                'data' => $appointments->items(),
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ]);
        }

        return view('pages.appointments.completed', compact('appointments'));
    }

    public function departmentAppointments(Request $request)
    {
        // Extract token_id if provided
        $appointmentId = null;
        if ($request->has('token_id') && $request->token_id !== null && trim($request->token_id) !== '') {
            $tokenId = trim($request->token_id);
            if (is_numeric($tokenId) && $tokenId > 0) {
                $appointmentId = (int)$tokenId;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $tokenId);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $appointmentId = (int)$numericId;
                }
            }
        }

        // Extract patient_id if provided
        $filterPatientId = null;
        if ($request->has('patient_id') && $request->patient_id !== null && trim($request->patient_id) !== '') {
            $patientIdInput = trim($request->patient_id);
            if (is_numeric($patientIdInput) && $patientIdInput > 0) {
                $filterPatientId = (int)$patientIdInput;
            } else {
                // Try to extract numeric value
                $numericId = preg_replace('/[^0-9]/', '', $patientIdInput);
                if ($numericId !== '' && is_numeric($numericId) && (int)$numericId > 0) {
                    $filterPatientId = (int)$numericId;
                }
            }
        }

        $userClinicType = auth()->user()->clinic_type;
        $filterByClinicType = $userClinicType && $userClinicType !== 'both';

        // Build base query
        if ($appointmentId !== null) {
            // When searching by token_id, use relaxed constraints to find the appointment
            $query = Appointment::query()
                ->where('id', $appointmentId);
            if ($filterByClinicType) {
                $query->where('clinic_type', $userClinicType);
            }
            $query->with(['patient', 'department', 'referringDoctor', 'processedBy']);
        } else {
            // Normal constraints for regular search
            $query = Appointment::query()
                ->whereNull('doctor_id')
                ->whereNull('processed_by');
            if ($filterByClinicType) {
                $query->where('clinic_type', $userClinicType);
            }
            $query->when(auth()->user()->doctor, function ($q) {
                $q->where('department_id', auth()->user()->doctor->department_id);
            })
                ->with(['patient', 'department', 'referringDoctor', 'processedBy']);
        }

        // Filter by patient ID if provided
        if ($filterPatientId !== null) {
            $query->where('patient_id', $filterPatientId);
        }

        // Search by patient name, id_card, phone, father_name (works with or without token_id)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('last_name', 'like', '%' . $search . '%')
                    ->orWhere('id_card', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhere('father_name', 'like', '%' . $search . '%')
                    ->orWhere('nid', 'like', '%' . $search . '%');
            });
        }

        // Get paginated appointments
        $appointments = $query->latest()
            ->paginate(25)
            ->appends($request->query())
            ->through(function ($appointment) {
                $appointment->jalali_date = Dcter::GregorianToJalali($appointment->date);
                return $appointment;
            });

        // If AJAX request, return JSON with HTML
        if ($request->ajax()) {
            $html = view('pages.appointments.department_table', compact('appointments'))->render();
            return response()->json([
                'html' => $html,
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage()
            ]);
        }

        return view('pages.appointments.department', compact('appointments'));
    }

    public function assignDoctor(Request $request, Appointment $appointment)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        if ($appointment->is_completed) {
            $message = localize('global.appointment_completed');

            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->back()->with('error', $message);
        }

        $newDoctorId = (int) $request->doctor_id;
        $currentDoctorId = $appointment->doctor_id ? (int) $appointment->doctor_id : null;

        if ($currentDoctorId === $newDoctorId) {
            if ($request->ajax()) {
                return response()->json(['success' => true]);
            }

            return redirect()->back();
        }

        if ($currentDoctorId !== null) {
            if ($appointment->doctor_reassigned) {
                $message = localize('global.doctor_can_only_be_changed_once');

                if ($request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return redirect()->back()->with('error', $message);
            }

            $appointment->update([
                'doctor_id' => $newDoctorId,
                'doctor_reassigned' => true,
            ]);
        } else {
            $appointment->update([
                'doctor_id' => $newDoctorId,
                'processed_by' => $appointment->processed_by ?? auth()->id(),
            ]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.doctor_assigned_successfully'),
            ]);
        }

        return redirect()->back()->with('success', localize('global.doctor_assigned_successfully'));
    }
    public function acceptAppointment(Request $request, Appointment $appointment)
    {
        // Check if appointment is already processed
        if ($appointment->processed_by) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.appointment_already_processed')
                ], 400);
            }
            return redirect()->back()->with('error', localize('global.appointment_already_processed'));
        }

        $updateData = [
            'processed_by' => auth()->id()
        ];

        // Check if current user has a doctor assigned
        $userDoctor = Doctor::where('user_id', auth()->id())->first();
        
        if ($userDoctor) {
            // Assign the doctor to the appointment
            $updateData['doctor_id'] = $userDoctor->id;
        }

        // Update appointment with processed_by and doctor (if user has one)
        $appointment->update($updateData);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.appointment_accepted_successfully'),
                'doctor_assigned' => $userDoctor ? true : false
            ]);
        }

        return redirect()->back()
            ->with('success', localize('global.appointment_accepted_successfully'));
    }

    public function getDoctorsByClinicType(Request $request)
    {
        try {
            $clinicType = auth()->user()->clinic_type;
            $departmentId = $request->input('department_id', null);
            
            // Call the stored procedure with department_id parameter
            $results = DB::select('CALL only_get_docters_base_on_clinic_type(?, ?, ?, ?)', [
                $clinicType,
                1,
                null,
                $departmentId
            ]);
            
            // Map the results to the expected format
            $doctors = array_map(function ($doctor) {
                // Use full_name from stored procedure if available, otherwise concatenate
                $fullName = $doctor->full_name ?? trim(($doctor->name ?? '') . ' ' . ($doctor->last_name ?? ''));
                
                return [
                    'id' => $doctor->id,
                    'name' => $fullName,
                    'email' => $doctor->email ?? ''
                ];
            }, $results);
            
            return response()->json([
                'success' => true,
                'doctors' => $doctors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading doctors: ' . $e->getMessage(),
                'doctors' => []
            ], 500);
        }
    }

    public function getDepartments()
    {
        try {
            $departments = Department::all()->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name
                ];
            });
            
            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading departments: ' . $e->getMessage(),
                'departments' => []
            ], 500);
        }
    }

    public function changeDepartment(Request $request, Appointment $appointment)
    {
        // Validate the request
        $request->validate([
            'department_id' => 'required|exists:departments,id'
        ]);

        // Update the appointment's department
        $appointment->update([
            'department_id' => $request->department_id
        ]);

        // If appointment was assigned to a doctor, unassign if doctor is not in new department
        if ($appointment->doctor_id) {
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor && $doctor->department_id != $request->department_id) {
                $appointment->update([
                    'doctor_id' => null
                ]);
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.department_updated_successfully')
            ]);
        }

        return redirect()->back()
            ->with('success', localize('global.department_updated_successfully'));
    }

    public function report(Request $request)
    {
        $doctors = Doctor::where('active_status', true)->orderBy('name')->get();
        $users = User::where('status', 1)->orderBy('name')->get();
        $provinces = Province::orderBy('name_dr')->get();
        $districts = District::orderBy('name_dr')->get();
        $relations = Relation::orderBy('name')->get();

        $items = null;

        // Only query if there are search parameters
        if ($request->hasAny(['patient_name', 'doctor_id', 'processed_by', 'start', 'end',
                              'date', 'time', 'is_completed', 'per_page', 'clinic_type', 'registered_by',
                              'job', 'job_type', 'gender', 'rank', 'relation_id', 'province_id', 'district_id'])) {
            $perPage = $request->get('per_page', 15);
            
            $query = Appointment::with(['patient.relation', 'patient.province', 'patient.district', 'creator', 'doctor', 'processedBy', 'branch'])
                ->select([
                    'appointments.id',
                    'appointments.patient_id',
                    'appointments.doctor_id',
                    'appointments.branch_id',
                    'appointments.clinic_type',
                    'appointments.is_completed',
                    'appointments.status_remark',
                    'appointments.refferal_remarks',
                    'appointments.date',
                    'appointments.time',
                    'appointments.processed_by',
                    'appointments.created_by',
                ]);

            // Apply filters
            $this->applyAppointmentReportFilters($query, $request);

            // Handle pagination with "all" option
            if ($perPage === 'all') {
                $items = $query->get();
            } else {
                $items = $query->paginate((int) $perPage);
                
                // Preserve query parameters in pagination links
                $items->appends($request->query());
            }
        }

        return view('pages.appointments.reports.index', compact('doctors', 'users', 'provinces', 'districts', 'relations', 'items'));
    }

    /**
     * Apply filters to the appointment query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyAppointmentReportFilters($query, Request $request)
    {
        // Patient filters (name, job, job_type, gender, rank, relation, province, district)
        $hasPatientFilter = $request->filled('patient_name') || $request->filled('job')
            || $request->filled('job_type') || ($request->filled('gender') && $request->gender !== '')
            || $request->filled('rank') || $request->filled('relation_id') || $request->filled('province_id') || $request->filled('district_id');
        if ($hasPatientFilter) {
            $query->whereHas('patient', function ($q) use ($request) {
                if ($request->filled('patient_name')) {
                    $q->where('name', 'like', '%' . $request->patient_name . '%');
                }
                if ($request->filled('job')) {
                    $q->where('job', 'like', '%' . $request->job . '%');
                }
                if ($request->filled('job_type')) {
                    $q->where('job_type', $request->job_type);
                }
                if ($request->filled('gender') && $request->gender !== '') {
                    $q->where('gender', $request->gender);
                }
                if ($request->filled('rank')) {
                    $q->where('rank', 'like', '%' . $request->rank . '%');
                }
                if ($request->filled('relation_id')) {
                    $q->where('relation_id', $request->relation_id);
                }
                if ($request->filled('province_id')) {
                    $q->where('province_id', $request->province_id);
                }
                if ($request->filled('district_id')) {
                    $q->where('district_id', $request->district_id);
                }
            });
        }

        // Who created this appointment (users.id)
        if ($request->filled('registered_by')) {
            $query->where('appointments.created_by', $request->registered_by);
        }

        // Clinic type filter (appointment)
        if ($request->filled('clinic_type')) {
            $query->where('appointments.clinic_type', $request->clinic_type);
        }

        // Doctor filter
        if ($request->filled('doctor_id')) {
            $query->where('appointments.doctor_id', $request->doctor_id);
        }

        // Processed by filter
        if ($request->filled('processed_by')) {
            $query->where('appointments.processed_by', $request->processed_by);
        }

        // Date range filter - Convert Persian to Gregorian
        if ($request->filled('start') && $request->filled('end')) {
            try {
                $startDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->start)->datetime();
                $endDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->end)->datetime();
                
                $query->whereDate('appointments.date', '>=', $startDate)
                      ->whereDate('appointments.date', '<=', $endDate);
            } catch (\Exception $e) {
                // Invalid date format, skip date filter
            }
        } elseif ($request->filled('date')) {
            // Single date filter (backward compatibility)
            $query->whereDate('appointments.date', $request->date);
        }

        // Time filter
        if ($request->filled('time')) {
            $query->where('appointments.time', $request->time);
        }

        // Status filter
        if ($request->filled('is_completed')) {
            $query->where('appointments.is_completed', $request->is_completed);
        }

        // Order by date and time descending
        $query->orderBy('appointments.date', 'desc')
              ->orderBy('appointments.time', 'desc');
    }

    public function exportReport(Request $request)
    {
        // Build query with same filters as report method
        $query = DB::table('appointments as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('users as u', 'a.processed_by', '=', 'u.id')
            ->leftJoin('users as uc', 'a.created_by', '=', 'uc.id')
            ->leftJoin('relations as rel', 'p.relation_id', '=', 'rel.id')
            ->leftJoin('provinces as prov', 'p.province_id', '=', 'prov.id')
            ->leftJoin('districts as dist', 'p.district_id', '=', 'dist.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'd.name as doctor_name',
                'b.name as branch_name',
                'a.clinic_type',
                'a.is_completed',
                'a.status_remark',
                'a.refferal_remarks',
                'a.date',
                'a.time',
                'a.processed_by',
                'u.name as processed_by_name',
                'uc.name as registered_by_name',
                'p.job',
                'p.job_type',
                'p.gender',
                'p.rank',
                'rel.name as relation_name',
                'prov.name_dr as province_name',
                'dist.name_dr as district_name'
            );

        // If data parameter is provided (from form submission) and valid, use it
        $useDataFilter = false;
        if ($request->filled('data')) {
            $data = json_decode($request->data, true);
            if (is_array($data) && !empty($data)) {
                $query->whereIn('a.id', $data);
                $useDataFilter = true;
            }
        }
        
        // If data filter wasn't used, apply the same filters as the report method
        if (!$useDataFilter) {
            // Patient name filter
            if ($request->filled('patient_name')) {
                $query->where('p.name', 'like', '%' . $request->patient_name . '%');
            }

            // Patient filters
            if ($request->filled('registered_by')) {
                $query->where('a.created_by', $request->registered_by);
            }
            if ($request->filled('job')) {
                $query->where('p.job', 'like', '%' . $request->job . '%');
            }
            if ($request->filled('job_type')) {
                $query->where('p.job_type', $request->job_type);
            }
            if ($request->filled('gender') && $request->gender !== '') {
                $query->where('p.gender', $request->gender);
            }
            if ($request->filled('rank')) {
                $query->where('p.rank', 'like', '%' . $request->rank . '%');
            }
            if ($request->filled('relation_id')) {
                $query->where('p.relation_id', $request->relation_id);
            }
            if ($request->filled('province_id')) {
                $query->where('p.province_id', $request->province_id);
            }
            if ($request->filled('district_id')) {
                $query->where('p.district_id', $request->district_id);
            }

            // Clinic type filter
            if ($request->filled('clinic_type')) {
                $query->where('a.clinic_type', $request->clinic_type);
            }

            // Doctor filter
            if ($request->filled('doctor_id')) {
                $query->where('a.doctor_id', $request->doctor_id);
            }

            // Processed by filter
            if ($request->filled('processed_by')) {
                $query->where('a.processed_by', $request->processed_by);
            }

            // Date range filter - Convert Persian to Gregorian
            if ($request->filled('start') && $request->filled('end')) {
                try {
                    $startDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->start)->datetime();
                    $endDate = \Hekmatinasser\Verta\Facades\Verta::parse($request->end)->datetime();
                    
                    $query->whereDate('a.date', '>=', $startDate)
                          ->whereDate('a.date', '<=', $endDate);
                } catch (\Exception $e) {
                    // Invalid date format, skip date filter
                }
            } elseif ($request->filled('date')) {
                // Single date filter (backward compatibility)
                $query->whereDate('a.date', $request->date);
            }

            // Time filter
            if ($request->filled('time')) {
                $query->where('a.time', $request->time);
            }

            // Status filter
            if ($request->filled('is_completed')) {
                $query->where('a.is_completed', $request->is_completed);
            }
        }

        // Order by date and time descending
        $query->orderBy('a.date', 'desc')
              ->orderBy('a.time', 'desc');

        $items = $query->get();

        // Check if there are any items to export
        if ($items->isEmpty()) {
            return redirect()->route('appointments.report', $request->except(['data', 'type']))
                ->with('error', localize('global.no_item_is_found'));
        }

        try {
            $exportType = $request->input('type');
            
            if ($exportType === 'pdf') {
                $html = view('pages.appointments.reports.pdf_report', ['items' => $items])->render();
                $mpdf = new Mpdf(['format' => 'A4-L']);
                $mpdf->WriteHTML($html);
                $mpdf->Output('appointments_report.pdf', 'D');
                exit; // Mpdf handles the output, exit to prevent any further output
            } else {
                // Excel export
                $reader = new Xlsx();
                $spreadsheet = $reader->load('report_templates/appointment_report.xlsx');
                $sheet = $spreadsheet->getActiveSheet();
                $row = 3;

                // Set column widths once (outside the loop)
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(20);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(20);
                $sheet->getColumnDimension('G')->setWidth(20);
                $sheet->getColumnDimension('H')->setWidth(15);
                $sheet->getColumnDimension('I')->setWidth(12);
                $sheet->getColumnDimension('J')->setWidth(10);
                $sheet->getColumnDimension('K')->setWidth(12);
                $sheet->getColumnDimension('L')->setWidth(15);
                $sheet->getColumnDimension('M')->setWidth(20);
                $sheet->getColumnDimension('N')->setWidth(20);
                $sheet->getColumnDimension('O')->setWidth(20);
                $sheet->getColumnDimension('P')->setWidth(20);
                $sheet->getColumnDimension('Q')->setWidth(12);

                foreach ($items as $index => $item) {
                    $status = $item->is_completed == '1' ? 'ملاقات های تکمیل شده' : 'ملاقات های در حال اجراٰ';
                    $clinicType = $item->clinic_type === 'hospital' ? __('global.hospital') : ($item->clinic_type === 'clinic' ? __('global.clinic') : '');
                    $jobType = $item->job_type ? __('global.' . $item->job_type) : '';
                    $gender = isset($item->gender) ? ($item->gender == '1' ? __('global.female') : __('global.male')) : '';

                    $sheet->setCellValue('A' . $row, $index + 1);
                    $sheet->setCellValue('B' . $row, $item->patient_name ?? '');
                    $sheet->setCellValue('C' . $row, $item->doctor_name ?? '');
                    $sheet->setCellValue('D' . $row, $item->branch_name ?? '');
                    $sheet->setCellValue('E' . $row, $clinicType);
                    $sheet->setCellValue('F' . $row, $item->processed_by_name ?? '');
                    $sheet->setCellValue('G' . $row, $item->registered_by_name ?? '');
                    $sheet->setCellValue('H' . $row, $item->job ?? '');
                    $sheet->setCellValue('I' . $row, $jobType);
                    $sheet->setCellValue('J' . $row, $gender);
                    $sheet->setCellValue('K' . $row, $item->rank ?? '');
                    $sheet->setCellValue('L' . $row, $item->relation_name ?? '');
                    $sheet->setCellValue('M' . $row, $item->province_name ?? '');
                    $sheet->setCellValue('N' . $row, $item->district_name ?? '');
                    $sheet->setCellValue('O' . $row, $status);
                    $sheet->setCellValue('P' . $row, $item->date ?? '');
                    $sheet->setCellValue('Q' . $row, $item->time ?? '');
                    $row++;
                }

                // Apply text wrapping to all data rows
                if ($row > 3) {
                    $sheet->getStyle('A3:Q' . ($row - 1))
                        ->getAlignment()
                        ->setWrapText(true);
                }

                return $this->exportResponse($spreadsheet);
            }
        } catch (\Exception $e) {
            \Log::error('Appointment export error: ' . $e->getMessage());
            return redirect()->route('appointments.report', $request->except(['data', 'type']))
                ->with('error', 'خطا در صادرات گزارش: ' . $e->getMessage());
        }
    }

    public function exportResponse($spreadsheet)
    {
        $writer = new WriterXlsx($spreadsheet);
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $filename = 'appointments_report_' . date('Y-m-d_His') . '.xlsx';
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $filename . '"');
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

    public function departmentReport(Request $request)
    {
        $departments = Department::all();
        $appointments = collect();
        
        // Only query if filters are provided
        if ($request->filled('department_id')) {
            $query = Appointment::where('branch_id', auth()->user()->branch_id)
                ->with(['patient', 'department'])
                ->where('department_id', $request->department_id);
            
            // Filter by date range on created_at
            if ($request->filled('date_from')) {
                try {
                    $startDate = Verta::parse($request->date_from)->datetime();
                    $query->whereDate('created_at', '>=', $startDate);
                } catch (\Exception $e) {
                    // Invalid date format, skip date filter
                }
            }
            
            if ($request->filled('date_to')) {
                try {
                    $endDate = Verta::parse($request->date_to)->datetime();
                    $query->whereDate('created_at', '<=', $endDate);
                } catch (\Exception $e) {
                    // Invalid date format, skip date filter
                }
            }
            
            // Load all results when filters are applied (no pagination)
            $appointments = $query->orderBy('created_at', 'desc')->get();
            
            // Add Persian date formatting to each appointment
            $appointments->transform(function ($appointment) {
                $appointment->persian_created_at = $appointment->created_at 
                    ? Verta::instance($appointment->created_at)->format('Y/m/d H:i')
                    : '—';
                return $appointment;
            });
        }
        
        // Handle AJAX requests
        if ($request->ajax() || $request->expectsJson()) {
            $html = view('pages.appointments.department_report_table', compact('appointments'))->render();
            return response()->json([
                'html' => $html,
                'count' => $appointments->count()
            ]);
        }
        
        return view('pages.appointments.department_report', compact('departments', 'appointments'));
    }
}
