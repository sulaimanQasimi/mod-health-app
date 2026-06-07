<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
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
                        $user->can('update', $appointment),
                        $user->can('delete', $appointment),
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
            'permissions' => $this->appointmentPermissions($user),
            'urls' => [
                'index' => route('react.appointments.index'),
                'trashed' => route('react.appointments.trashed'),
                'show' => url('/react/appointments'),
                'edit' => url('/react/appointments'),
                'destroy' => url('/react/appointments'),
                'patientHistory' => url('/patients/history'),
                'patientsIndex' => route('react.patients.index'),
                'patientsCreate' => route('react.patients.create'),
            ],
        ]);
    }

    public function show(Request $request, Appointment $appointment): Response
    {
        $this->authorize('view', $appointment);

        $appointment->load([
            'patient:id,name,last_name,father_name,id_card',
            'doctor:id,name',
            'department:id,name',
            'processedBy:id,name,last_name',
        ]);

        $patient = $appointment->patient;
        $previousDiagnoses = $patient?->diagnoses()->orderByDesc('created_at')->get() ?? collect();

        return Inertia::render('Appointments/Show', [
            'appointment' => [
                'id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'patient_name' => $patient?->name,
                'patient_last_name' => $patient?->last_name,
                'id_card' => $patient?->id_card,
                'doctor_id' => $appointment->doctor_id,
                'doctor_name' => $appointment->doctor?->name,
                'department_name' => $appointment->department?->name,
                'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
                'time' => $appointment->time,
                'is_completed' => (bool) $appointment->is_completed,
                'processed_by' => (bool) $appointment->processed_by,
            ],
            'patientHistory' => [
                'primary' => $previousDiagnoses->where('type', 0)->values()->map(fn ($d) => [
                    'description' => $d->description,
                    'date' => $d->created_at ? verta($d->created_at)->format('Y-m-d') : null,
                ])->all(),
                'final' => $previousDiagnoses->where('type', 1)->values()->map(fn ($d) => [
                    'description' => $d->description,
                    'date' => $d->created_at ? verta($d->created_at)->format('Y-m-d') : null,
                ])->all(),
            ],
            'permissions' => [
                'updateStatus' => $request->user()->can('updateStatus', $appointment),
                'edit' => $request->user()->can('update', $appointment),
                'printToken' => ! $appointment->is_completed,
            ],
            'urls' => [
                'index' => route('react.appointments.index'),
                'edit' => route('react.appointments.edit', $appointment),
                'printToken' => url("/appointments/{$appointment->id}/printToken"),
                'changeStatus' => route('appointments.changeStatus', $appointment),
                'legacyShow' => url("/appointments/show/{$appointment->id}"),
            ],
        ]);
    }

    public function edit(Request $request, Appointment $appointment): Response
    {
        $this->authorize('update', $appointment);

        $user = $request->user();

        $appointment->load([
            'patient:id,name,last_name,father_name,id_card',
            'doctor:id,name',
            'department:id,name',
        ]);

        return Inertia::render('Appointments/Edit', [
            'appointment' => $this->transformAppointmentForForm($appointment),
            'formData' => [
                'clinicType' => $user->clinic_type,
                'doctorsByDepartment' => url('/react/patients/doctors-by-department'),
            ],
            'permissions' => [
                'delete' => $user->can('delete', $appointment),
            ],
            'urls' => [
                'index' => route('react.appointments.index'),
                'update' => route('react.appointments.update', $appointment),
                'destroy' => route('react.appointments.destroy', $appointment),
                'show' => route('react.appointments.show', $appointment),
            ],
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        if ($appointment->processed_by && $request->doctor_id != $appointment->doctor_id) {
            throw ValidationException::withMessages([
                'doctor_id' => [localize('global.cannot_change_doctor_after_acceptance')],
            ]);
        }

        $user = $request->user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'date' => 'required|string',
            'time' => 'required',
            'branch_id' => 'required|exists:branches,id',
            'refferal_remarks' => 'nullable|string',
        ];

        if ($user->clinic_type === 'both') {
            $rules['clinic_type'] = 'required|in:hospital,clinic';
        }

        $validatedData = $request->validate($rules);

        $validatedData['date'] = verta()->parse($validatedData['date'])->datetime()->format('Y-m-d');

        if ($appointment->processed_by) {
            $validatedData['doctor_id'] = $appointment->doctor_id;
        }

        if ($user->clinic_type && $user->clinic_type !== 'both') {
            $validatedData['clinic_type'] = $user->clinic_type;
        }

        $appointment->update($validatedData);

        return redirect()
            ->route('react.appointments.index')
            ->with('success', localize('global.appointment_updated_successfully'));
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return redirect()
            ->route('react.appointments.index')
            ->with('success', localize('global.appointment_deleted_successfully'));
    }

    public function trashed(Request $request): Response
    {
        $user = $request->user();

        $this->authorize('viewAny', Appointment::class);

        if (! $user->hasRole(['super_admin', 'admin'])
            && ! $user->hasPermissionTo('restore-appointments')) {
            abort(403);
        }

        $query = Appointment::onlyTrashed()
            ->where('branch_id', $user->branch_id)
            ->with([
                'patient:id,name,last_name,father_name,id_card',
                'doctor:id,name',
                'department:id,name',
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

        $paginator = $query->latest('deleted_at')->paginate(25)->withQueryString();

        return Inertia::render('Appointments/Trashed', [
            'appointments' => [
                'data' => collect($paginator->items())
                    ->map(fn (Appointment $appointment) => $this->transformTrashedAppointment(
                        $appointment,
                        $user->can('restore', $appointment),
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
            'filters' => [
                'patient_name' => (string) $request->input('patient_name', ''),
                'id_card' => (string) $request->input('id_card', ''),
            ],
            'permissions' => $this->appointmentPermissions($user),
            'urls' => [
                'index' => route('react.appointments.index'),
                'trashed' => route('react.appointments.trashed'),
                'restore' => url('/react/appointments'),
            ],
        ]);
    }

    public function restore(Request $request, int $appointment): RedirectResponse
    {
        $appointment = Appointment::withTrashed()->findOrFail($appointment);

        $this->authorize('restore', $appointment);

        $appointment->restore();

        return redirect()
            ->route('react.appointments.trashed')
            ->with('success', localize('global.appointment_restored_successfully'));
    }

    /**
     * @return array<string, bool>
     */
    private function appointmentPermissions($user): array
    {
        return [
            'create' => $user->can('create', Appointment::class),
            'view' => $user->can('viewAny', Appointment::class),
            'edit' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('edit-appointments'),
            'delete' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('delete-appointments'),
            'restore' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('restore-appointments'),
            'updateStatus' => $user->hasPermissionTo('update-appointment-status')
                || $user->hasRole(['super_admin', 'admin']),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformAppointmentForForm(Appointment $appointment): array
    {
        $patient = $appointment->patient;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'patient_name' => $patient
                ? trim("{$patient->name} {$patient->last_name}")
                : null,
            'id_card' => $patient?->id_card,
            'father_name' => $patient?->father_name,
            'department_id' => $appointment->department_id,
            'department_name' => $appointment->department?->name,
            'doctor_id' => $appointment->doctor_id ? (string) $appointment->doctor_id : '',
            'doctor_name' => $appointment->doctor?->name,
            'branch_id' => $appointment->branch_id,
            'clinic_type' => $appointment->clinic_type ?? '',
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : '',
            'time' => $appointment->time ? substr((string) $appointment->time, 0, 5) : '',
            'refferal_remarks' => $appointment->refferal_remarks ?? '',
            'is_completed' => (bool) $appointment->is_completed,
            'processed_by' => (bool) $appointment->processed_by,
            'can_change_doctor' => ! $appointment->processed_by,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformTrashedAppointment(Appointment $appointment, bool $canRestore): array
    {
        $patient = $appointment->patient;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'id_card' => $patient?->id_card,
            'patient_name' => $patient?->name,
            'father_name' => $patient?->father_name,
            'doctor_name' => $appointment->doctor?->name,
            'department_name' => $appointment->department?->name,
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
            'time' => $appointment->time,
            'deleted_at' => $appointment->deleted_at
                ? verta($appointment->deleted_at)->format('Y-m-d H:i')
                : null,
            'permissions' => [
                'restore' => $canRestore,
            ],
        ];
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
        bool $canEdit = false,
        bool $canDelete = false,
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
                'edit' => $canEdit,
                'delete' => $canDelete,
            ],
        ];
    }
}
