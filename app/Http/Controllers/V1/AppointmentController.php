<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    use RendersInertiaPage;

    private const INDEX_FILTER_KEYS = [
        'patient_name',
        'id_card',
        'patient_id',
        'doctor_id',
        'department_id',
        'is_completed',
        'date_from',
        'date_to',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Appointment::class);

        $user = $request->user();

        $query = Appointment::query()
            ->where('branch_id', $user->branch_id)
            ->with([
                'patient:id,name,last_name,father_name,id_card',
                'doctor:id,name',
                'department:id,name',
                'processedBy:id,name,last_name',
            ]);

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($patientQuery) use ($request) {
                $patientQuery->where('name', 'like', '%'.$request->patient_name.'%')
                    ->orWhere('last_name', 'like', '%'.$request->patient_name.'%');
            });
        }

        if ($request->filled('id_card')) {
            $query->whereHas('patient', function ($patientQuery) use ($request) {
                $patientQuery->where('id_card', 'like', '%'.$request->id_card.'%');
            });
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('is_completed')) {
            $query->where('is_completed', $request->is_completed);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', verta()->parse($request->date_from)->datetime());
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', verta()->parse($request->date_to)->datetime());
        }

        $paginator = $query->latest()->paginate(25)->withQueryString();

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        $canViewPatient = fn ($patient) => $patient && $user->can('view', $patient);

        return Inertia::render('Appointments/Index', [
            'appointments' => [
                'data' => collect($paginator->items())
                    ->map(fn (Appointment $appointment) => $this->transformAppointmentForIndex(
                        $appointment,
                        $user->can('view', $appointment),
                        $canViewPatient($appointment->patient),
                    ))
                    ->values()
                    ->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $filters,
            'filterOptions' => [
                'doctors' => Doctor::query()
                    ->where('branch_id', $user->branch_id)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'departments' => $user->category_id
                    ? Department::query()->where('category_id', $user->category_id)->orderBy('name')->get(['id', 'name'])
                    : Department::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => [
                'create' => $user->can('create', Appointment::class),
                'view' => $user->can('viewAny', Appointment::class),
                'updateStatus' => $user->hasPermissionTo('update-appointment-status')
                    || $user->hasRole(['super_admin', 'admin']),
            ],
            'urls' => [
                'index' => route('react.appointments.index'),
                'show' => url('/appointments/show'),
                'patientHistory' => url('/patients/history'),
                'patientsIndex' => route('react.patients.index'),
                'patientsCreate' => route('react.patients.create'),
            ],
        ]);
    }

    public function departmentReport()
    {
        return $this->renderPage('global.department_report');
    }

    public function department()
    {
        return $this->renderPage('global.department_appointments');
    }

    public function doctor()
    {
        return $this->renderPage('global.ongoing_appointments');
    }

    public function completed()
    {
        return $this->renderPage('global.completed_appointments');
    }

    public function report()
    {
        return $this->renderPage('global.reports');
    }

    /**
     * @return array<string, mixed>
     */
    private function transformAppointmentForIndex(
        Appointment $appointment,
        bool $canView = false,
        bool $canViewHistory = false,
    ): array {
        $processor = $appointment->processedBy;
        $processedBy = $processor
            ? trim("{$processor->name} {$processor->last_name}")
            : null;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'id_card' => $appointment->patient?->id_card,
            'patient_name' => $appointment->patient?->name,
            'father_name' => $appointment->patient?->father_name,
            'doctor_name' => $appointment->doctor?->name,
            'department_name' => $appointment->department?->name,
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
            'time' => $appointment->time,
            'is_completed' => (bool) $appointment->is_completed,
            'processed_by' => $processedBy,
            'permissions' => [
                'view' => $canView,
                'history' => $canViewHistory,
            ],
        ];
    }
}
