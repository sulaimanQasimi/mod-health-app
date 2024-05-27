<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Consultation;
use App\Models\Department;
use App\Models\Diagnose;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\Lab;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Operation;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Province;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    public function index()
    {
        $totalPatients = Patient::count();
        $totalDoctors = User::count();
        $totalAppointments = Appointment::count();
        $totalPrescriptions = Prescription::count();
        $totalConsultations = Consultation::count();
        $totalOperations = Operation::count();
        $totalIcuAdmissions = ICU::count();
        $totalInPatientAdmissions = Hospitalization::count();

        // Retrieve data for charts
        $patientsTrendData = $this->getPatientsTrendData();
        $appointmentsTrendData = $this->getAppointmentsTrendData();
        // ... (similar for other entities)

        // Retrieve models percentage changes
        $patientPercentageChange = $this->getPercentageChange(Patient::class);
        $doctorPercentageChange = $this->getPercentageChange(User::class);
        $appointmentPercentageChange = $this->getPercentageChange(Appointment::class);
        $prescriptionPercentageChange = $this->getPercentageChange(Prescription::class);
        $consultationPercentageChange = $this->getPercentageChange(Consultation::class);
        $operationPercentageChange = $this->getPercentageChange(Anesthesia::class);
        $icuPercentageChange = $this->getPercentageChange(ICU::class);
        $hospitalizationPercentageChange = $this->getPercentageChange(Hospitalization::class);

        $wordCloudData = User::withCount([
            'appointments',
            'consultations',
            'anesthesias',
            'consultation_comments',
            'hospitalizations',
            'i_c_u_s',
            'labs',
            'prescriptions',
            'visits'
        ])
            ->get()
            ->map(function ($user) {
                $consultationsCount = Consultation::whereRaw("JSON_CONTAINS(doctor_id, '\"$user->id\"')")->count();
                // $anesthesiasCount = Anesthesia::whereRaw("JSON_CONTAINS(operation_doctor_id, '\"$user->id\"')")->count();
                $anesthesiasCount = Anesthesia::
                where(function($query) use
                ($user)
                {
                    $query->whereRaw("JSON_CONTAINS(operation_assistants_id, '\"$user->id\"')")
                    ->orWhere('operation_surgion_id', $user->id)
                ->orWhere('operation_anesthesia_log_id', $user->id)
                ->orWhere('operation_anesthesist_id', $user->id)
                ->orWhere('operation_scrub_nurse_id', $user->id)
                ->orWhere('operation_circulation_nurse_id', $user->id);
                })->count();

                return [
                    'name' => $user->name,
                    'weight' => $user->appointments_count + $consultationsCount + $anesthesiasCount + $user->consultation_comments_count + $user->hospitalizations_count + $user->i_c_u_s_count + $user->labs_count + $user->prescriptions_count + $user->visits_count,
                ];
            })
            ->values()
            ->toArray();


        return view('pages.dashboard.index', [
            'totalPatients' => $totalPatients,
            'totalDoctors' => $totalDoctors,
            'totalAppointments' => $totalAppointments,
            'totalPrescriptions' => $totalPrescriptions,
            'totalConsultations' => $totalConsultations,
            'totalOperations' => $totalOperations,
            'totalIcuAdmissions' => $totalIcuAdmissions,
            'totalInPatientAdmissions' => $totalInPatientAdmissions,
            'patientsTrendData' => $patientsTrendData,
            'appointmentsTrendData' => $appointmentsTrendData,
            'wordCloudData' => $wordCloudData,
            'patientPercentageChange' => $patientPercentageChange,
            'doctorPercentageChange' => $doctorPercentageChange,
            'appointmentPercentageChange' => $appointmentPercentageChange,
            'prescriptionPercentageChange' => $prescriptionPercentageChange,
            'consultationPercentageChange' => $consultationPercentageChange,
            'operationPercentageChange' => $operationPercentageChange,
            'icuPercentageChange' => $icuPercentageChange,
            'hospitalizationPercentageChange' => $hospitalizationPercentageChange
        ]);
    }

    // Helper methods to retrieve trend data
    private function getPatientsTrendData()
    {
        // Assuming you want to retrieve the patient count data for the last 12 months
        $now = Carbon::now();
        $startDate = $now->subYear()->startOfMonth();
        $now = Carbon::now();
        $endDate = $now->endOfMonth();


        $patientsTrendData = Patient::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
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


    private function getAppointmentsTrendData()
    {
        // Assuming you want to retrieve the patient count data for the last 12 months
        $now = Carbon::now();
        $startDate = $now->startOfMonth();
        $now = Carbon::now();
        $endDate = $now->endOfMonth();


        $appointmentsTrendData = Appointment::selectRaw('DATE_FORMAT(created_at, "%d") as day, COUNT(*) as count')
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

    private function getPercentageChange($model)
    {
        // Get the current month and year
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');

        // Get the model count for the current month
        $currentMonthCount = $model::whereMonth('created_at', $currentMonth)
                             ->whereYear('created_at', $currentYear)
                             ->count();

        // Get the model count for the previous month
        $previousMonth = Carbon::now()->subMonth(1)->format('m');
        $previousYear = Carbon::now()->subMonth(1)->format('Y');
        $previousMonthCount = $model::whereMonth('created_at', $previousMonth)
                             ->whereYear('created_at', $previousYear)
                             ->count();

        // Calculate the percentage change
        if ($previousMonthCount > 0) {
            $percentageChange = round(($currentMonthCount - $previousMonthCount) / $previousMonthCount * 100, 2);
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
        $labTypeSection = LabTypeSection::findOrFail($labTypeId);
        $labTypes = $labTypeSection->labTypes;
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
