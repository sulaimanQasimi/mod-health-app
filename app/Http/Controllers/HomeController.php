<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\PatientTestRegistration;
use App\Models\LabType;
use App\Models\Operation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Province;
use App\Models\Room;
use App\Models\User;
use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            try {
                $branchId = auth()->user()->branch_id;
                $today = Carbon::today();
                $yesterday = Carbon::yesterday();

                // Get all counts
                $counts = $this->getDashboardCounts($branchId, $today, $yesterday);

                // Today's statistics
                $todayPatients = $counts['todayPatients'];
                $yesterdayPatients = $counts['yesterdayPatients'];
                $todayPatientsPercentageChange = $this->calculateTodayPercentageChange($todayPatients, $yesterdayPatients);

                // Retrieve data for charts
                $patientsTrendData = $this->getPatientsTrendData($branchId);
                $appointmentsTrendData = $this->getAppointmentsTrendData($branchId);

                // Get all percentage changes
                $percentageChanges = $this->getAllPercentageChanges($branchId);

                // Get word cloud data
                $wordCloudData = $this->getWordCloudData($branchId);

                // Get bed statistics
                $bedStats = $this->getBedStatistics($branchId);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'totalPatients' => $counts['totalPatients'],
                        'totalCheckups' => $counts['totalCheckups'],
                        'totalAppointments' => $counts['totalAppointments'],
                        'totalPrescriptions' => $counts['totalPrescriptions'],
                        'totalConsultations' => $counts['totalConsultations'],
                        'totalOperations' => $counts['totalOperations'],
                        'totalIcuAdmissions' => $counts['totalIcuAdmissions'],
                        'totalInPatientAdmissions' => $counts['totalInPatientAdmissions'],
                        'totalPhysiotherapyProcedures' => $counts['totalPhysiotherapyProcedures'],
                        'todayPatients' => $todayPatients,

                        'totalEmergencyPatients' => Appointment::where('branch_id', $branchId)
                            ->whereDate('created_at', now())
                            ->where("department_id", 1)
                            ->count(),
                        'todayPatientsPercentageChange' => $todayPatientsPercentageChange,
                        'patientsTrendData' => $patientsTrendData,
                        'appointmentsTrendData' => $appointmentsTrendData,
                        'wordCloudData' => $wordCloudData,
                        'patientPercentageChange' => $percentageChanges['patient'],
                        'checkupPercentageChange' => $percentageChanges['checkup'],
                        'appointmentPercentageChange' => $percentageChanges['appointment'],
                        'prescriptionPercentageChange' => $percentageChanges['prescription'],
                        'consultationPercentageChange' => $percentageChanges['consultation'],
                        'operationPercentageChange' => $percentageChanges['operation'],
                        'icuPercentageChange' => $percentageChanges['icu'],
                        'hospitalizationPercentageChange' => $percentageChanges['hospitalization'],
                        'occupied_beds' => $bedStats['occupied'],
                        'free_beds' => $bedStats['free'],
                        'all_beds' => $bedStats['all']
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to load dashboard data',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        // Return view for regular requests
        return view('pages.dashboard.index');
    }

    /**
     * Get all dashboard counts in optimized way
     */
    private function getDashboardCounts($branchId, $today, $yesterday)
    {
        return [
            'totalPatients' => Patient::where('branch_id', $branchId)->count(),
            'totalCheckups' => PatientTestRegistration::where('branch_id', $branchId)->count(),
            'totalAppointments' => Appointment::where('branch_id', $branchId)->count(),
            'totalPrescriptions' => Prescription::where('branch_id', $branchId)->count(),
            'totalConsultations' => Consultation::where('branch_id', $branchId)->count(),
            'totalOperations' => Anesthesia::where('branch_id', $branchId)->where('is_operation_done', '1')->count(),
            'totalIcuAdmissions' => ICU::where('branch_id', $branchId)->count(),
            'totalInPatientAdmissions' => Hospitalization::where('branch_id', $branchId)->count(),
            'totalPhysiotherapyProcedures' => PhysiotherapyProcedure::whereHas('appointment', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            })->count(),
            'todayPatients' => Patient::where('branch_id', $branchId)->whereDate('created_at', $today)->count(),
            'yesterdayPatients' => Patient::where('branch_id', $branchId)->whereDate('created_at', $yesterday)->count(),

        ];
    }

    /**
     * Get all percentage changes in optimized batch
     */
    private function getAllPercentageChanges($branchId)
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentMonthEnd = Carbon::now()->endOfMonth();
        $previousMonth = Carbon::now()->subMonth()->startOfMonth();
        $previousMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // Get current month counts
        $currentCounts = [
            'patient' => Patient::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'checkup' => PatientTestRegistration::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'appointment' => Appointment::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'prescription' => Prescription::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'consultation' => Consultation::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'operation' => Anesthesia::where('branch_id', $branchId)->where('is_operation_done', '1')
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'icu' => ICU::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
            'hospitalization' => Hospitalization::where('branch_id', $branchId)
                ->whereBetween('created_at', [$currentMonth, $currentMonthEnd])->count(),
        ];

        // Get previous month counts
        $previousCounts = [
            'patient' => Patient::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'checkup' => PatientTestRegistration::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'appointment' => Appointment::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'prescription' => Prescription::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'consultation' => Consultation::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'operation' => Anesthesia::where('branch_id', $branchId)->where('is_operation_done', '1')
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'icu' => ICU::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
            'hospitalization' => Hospitalization::where('branch_id', $branchId)
                ->whereBetween('created_at', [$previousMonth, $previousMonthEnd])->count(),
        ];

        // Calculate percentage changes
        $percentageChanges = [];
        foreach ($currentCounts as $key => $currentCount) {
            $previousCount = $previousCounts[$key];
            $percentageChanges[$key] = $previousCount > 0
                ? round(($currentCount - $previousCount) / $previousCount * 100, 2)
                : 0;
        }

        return $percentageChanges;
    }

    /**
     * Optimize word cloud data query - reduce N+1 queries
     */
    private function getWordCloudData($branchId)
    {
        // Get all doctors for the branch with relationship counts
        $doctors = Doctor::where('branch_id', $branchId)
            ->withCount([
                'appointments' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
                'consultation_comments',
                'hospitalizations' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
                'i_c_u_s' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
                'prescriptions' => function ($query) use ($branchId) {
                    $query->where('branch_id', $branchId);
                },
                'visits'
            ])
            ->get();

        // Get all consultations and count by doctor_id JSON field
        $consultations = DB::table('consultations')
            ->where('branch_id', $branchId)
            ->select('doctor_id')
            ->get();

        // Build consultation counts map
        $consultationCountsMap = [];
        foreach ($consultations as $consultation) {
            $doctorIds = json_decode($consultation->doctor_id, true) ?? [];
            foreach ($doctorIds as $doctorId) {
                if (!isset($consultationCountsMap[$doctorId])) {
                    $consultationCountsMap[$doctorId] = 0;
                }
                $consultationCountsMap[$doctorId]++;
            }
        }

        // Get all anesthesias and count by various doctor fields
        $anesthesias = DB::table('anesthesias')
            ->where('branch_id', $branchId)
            ->select(
                'doctor_id',
                'operation_assistants_id',
                'operation_surgion_id',
                'operation_anesthesia_log_id',
                'operation_anesthesist_id',
                'operation_scrub_nurse_id',
                'operation_circulation_nurse_id'
            )
            ->get();

        // Build anesthesia counts map
        $anesthesiaCountsMap = [];
        foreach ($anesthesias as $anesthesia) {
            $doctorIds = [];
            if ($anesthesia->doctor_id) $doctorIds[] = $anesthesia->doctor_id;
            if ($anesthesia->operation_surgion_id) $doctorIds[] = $anesthesia->operation_surgion_id;
            if ($anesthesia->operation_anesthesia_log_id) $doctorIds[] = $anesthesia->operation_anesthesia_log_id;
            if ($anesthesia->operation_anesthesist_id) $doctorIds[] = $anesthesia->operation_anesthesist_id;
            if ($anesthesia->operation_scrub_nurse_id) $doctorIds[] = $anesthesia->operation_scrub_nurse_id;
            if ($anesthesia->operation_circulation_nurse_id) $doctorIds[] = $anesthesia->operation_circulation_nurse_id;

            $assistants = json_decode($anesthesia->operation_assistants_id, true) ?? [];
            $doctorIds = array_merge($doctorIds, $assistants);
            $doctorIds = array_unique($doctorIds);

            foreach ($doctorIds as $doctorId) {
                if (!isset($anesthesiaCountsMap[$doctorId])) {
                    $anesthesiaCountsMap[$doctorId] = 0;
                }
                $anesthesiaCountsMap[$doctorId]++;
            }
        }

        // Calculate weights for each doctor
        return $doctors->map(function ($doctor) use ($consultationCountsMap, $anesthesiaCountsMap) {
            $consultationsCount = $consultationCountsMap[$doctor->id] ?? 0;
            $anesthesiasCount = $anesthesiaCountsMap[$doctor->id] ?? 0;

            $weight = $doctor->appointments_count
                + $anesthesiasCount
                + $consultationsCount
                + $doctor->consultation_comments_count
                + $doctor->hospitalizations_count
                + $doctor->i_c_u_s_count
                + $doctor->prescriptions_count
                + $doctor->visits_count;

            return [
                'name' => $doctor->name,
                'weight' => $weight,
            ];
        })
            ->filter(function ($item) {
                return $item['weight'] > 0; // Only include doctors with activity
            })
            ->values()
            ->toArray();
    }

    /**
     * Get bed statistics in a single query
     */
    private function getBedStatistics($branchId)
    {
        $bedStats = Bed::join('rooms', 'beds.room_id', '=', 'rooms.id')
            ->where('rooms.branch_id', $branchId)
            ->selectRaw('
                COUNT(*) as all_beds,
                SUM(CASE WHEN beds.is_occupied = 1 THEN 1 ELSE 0 END) as occupied_beds,
                SUM(CASE WHEN beds.is_occupied = 0 THEN 1 ELSE 0 END) as free_beds
            ')
            ->first();

        return [
            'all' => $bedStats->all_beds ?? 0,
            'occupied' => $bedStats->occupied_beds ?? 0,
            'free' => $bedStats->free_beds ?? 0,
        ];
    }

    // Helper methods to retrieve trend data
    private function getPatientsTrendData($branchId)
    {
        // Retrieve the patient count data for the last 12 months
        $startDate = Carbon::now()->subYear()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $patientsTrendData = Patient::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->toArray();

        // Prepare the data for the chart
        $labels = array_column($patientsTrendData, 'month');
        $data = array_column($patientsTrendData, 'count');

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getAppointmentsTrendData($branchId)
    {
        // Retrieve the appointment count data for the current month
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $appointmentsTrendData = Appointment::selectRaw('DATE_FORMAT(created_at, "%d") as day, COUNT(*) as count')
            ->where('branch_id', $branchId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%d")'))
            ->orderBy('day')
            ->get()
            ->toArray();

        // Prepare the data for the chart
        $labels = array_column($appointmentsTrendData, 'day');
        $data = array_column($appointmentsTrendData, 'count');

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function calculateTodayPercentageChange($todayCount, $yesterdayCount)
    {
        if ($yesterdayCount > 0) {
            $percentageChange = round(($todayCount - $yesterdayCount) / $yesterdayCount * 100, 2);
        } else {
            $percentageChange = 0;
        }
        return $percentageChange;
    }

    public function changeLanguage($lang)
    {
        Session()->put('language', $lang);

        return redirect()->back();
    }

    public function getRelatedDistricts($provinceId)
    {
        $province = Province::findOrFail($provinceId);
        $districts = $province->districts;
        $options = '<option value = "">Select District</option>';

        foreach ($districts as $district) {
            $options .= '<option value = "' . $district->id . '">' . $district->name_dr . '</option>';
        }

        return $options;
    }

    public function getRelatedDepartments($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $departments = $branch->departments;
        $options = '<option value = "">Select Department</option>';

        foreach ($departments as $department) {
            $options .= '<option value = "' . $department->id . '">' . $department->name . '</option>';
        }

        return $options;
    }

    public function getRelatedSections($depId)
    {
        $department = Department::findOrFail($depId);
        $sections = $department->sections;
        $options = '<option value = "">Select Department</option>';

        foreach ($sections as $section) {
            $options .= '<option value = "' . $section->id . '">' . $section->name . '</option>';
        }

        return $options;
    }

    public function getRelatedDoctors($departmentId)
    {
        $department = Department::findOrFail($departmentId);
        $doctors = $department->doctors;
        $options = '<option value = "">Select Department</option>';

        foreach ($doctors as $doctor) {
            $options .= '<option value = "' . $doctor->id . '">' . $doctor->name . '</option>';
        }

        return $options;
    }

    public function getRelatedLabTypes($labTypeId)
    {
        // Since we removed LabTypeSection, return all lab types
        $labTypes = LabType::all();
        $options = '<option value = "">Select Department</option>';

        foreach ($labTypes as $labType) {
            $options .= '<option value = "' . $labType->id . '">' . $labType->name . '</option>';
        }

        return $options;
    }

    public function getLabTypeTests($labTypeId)
    {
        // Retrieve the lab type tests based on the $labTypeId
        $labTypeTests = LabType::where('parent_id', $labTypeId)->get();

        // Return the lab type tests as JSON response
        return response()->json($labTypeTests);
    }

    public function getBranchDoctors($branchId)
    {
        $branch = Branch::findOrFail($branchId);
        $doctors = $branch->doctors;
        $options = '<option value = "">Select Doctor</option>';

        foreach ($doctors as $doctor) {
            $options .= '<option value = "' . $doctor->id . '">' . $doctor->name . '</option>';
        }

        return $options;
    }

    public function getRelatedBeds($roomId)
    {
        $room = Room::findOrFail($roomId);
        $beds = $room->beds;
        $options = '<option value = "">Select Bed</option>';

        foreach ($beds as $bed) {
            $options .= '<option value = "' . $bed->id . '">' . $bed->number . '</option>';
        }

        return $options;
    }
}
