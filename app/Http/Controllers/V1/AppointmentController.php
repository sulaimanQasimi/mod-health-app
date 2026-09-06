<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesAppointmentReport;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\RendersInertiaPage;
use App\Jobs\SendNewAppointmentNotification;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\District;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PrintedNumber;
use App\Models\Province;
use App\Models\Relation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AppointmentController extends Controller
{
    use ManagesAppointmentReport;
    use PaginatesInertiaIndex;
    use RendersInertiaPage;

    private const INDEX_FILTER_KEYS = [
        'patient_name',
        'id_card',
        'patient_id',
        'father_name',
        'phone',
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
                'patient:id,name,last_name,father_name,id_card,phone',
                'doctor:id,name',
                'department:id,name',
                'processedBy:id,name,last_name',
                'creator:id,name,last_name',
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

        if ($request->filled('father_name')) {
            $query->whereHas('patient', function ($patientQuery) use ($request) {
                $patientQuery->where('father_name', 'like', '%'.$request->father_name.'%');
            });
        }

        if ($request->filled('phone')) {
            $query->whereHas('patient', function ($patientQuery) use ($request) {
                $patientQuery->where('phone', 'like', '%'.$request->phone.'%');
            });
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

        $canViewListedAppointments = $user->can('viewAny', Appointment::class);

        return Inertia::render('Appointments/Index', [
            'appointments' => [
                'data' => collect($paginator->items())
                    ->map(fn (Appointment $appointment) => $this->transformAppointmentForIndex(
                        $appointment,
                        $canViewListedAppointments,
                        $canViewListedAppointments && (bool) $appointment->patient_id,
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
                'index' => route('appointments.index'),
                'trashed' => route('appointments.trashed'),
                'show' => url('/appointments'),
                'edit' => url('/appointments'),
                'destroy' => url('/appointments'),
                'patientHistory' => url('/patients/history'),
                'patientsIndex' => route('patients.index'),
                'patientsCreate' => route('patients.create'),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $this->authorize('create', Appointment::class);

        $user = $request->user();

        $rules = [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'branch_id' => 'required|exists:branches,id',
            'department_id' => 'required|exists:departments,id',
            'is_completed' => 'nullable',
            'status_remark' => 'nullable|string|max:191',
            'refferal_remarks' => 'nullable|string|max:191',
        ];

        if ($user->clinic_type === 'both') {
            $rules['clinic_type'] = 'required|in:hospital,clinic';
        }

        $validatedData = $request->validate($rules);

        $patient = Patient::query()->findOrFail($validatedData['patient_id']);

        if (! $user->hasRole(['super_admin', 'admin']) && (int) $patient->branch_id !== (int) $user->branch_id) {
            abort(403);
        }

        if (! $user->hasRole(['super_admin', 'admin']) && (int) $validatedData['branch_id'] !== (int) $user->branch_id) {
            abort(403);
        }

        $now = now();
        $validatedData['date'] = $now->format('Y-m-d');
        $validatedData['time'] = $now->format('H:i:s');
        $validatedData['is_completed'] = $validatedData['is_completed'] ?? 0;

        if ($user->clinic_type && $user->clinic_type !== 'both') {
            $validatedData['clinic_type'] = $user->clinic_type;
        }

        if ($request->filled('current_appointment_id')) {
            $currentAppointment = Appointment::query()->findOrFail($request->input('current_appointment_id'));
            $this->authorize('update', $currentAppointment);

            $currentAppointment->update([
                'is_completed' => 1,
                'refferal_remarks' => $request->input('refferal_remarks'),
            ]);
        }

        $appointment = Appointment::create($validatedData);
        $appointment->load(['department:id,name', 'doctor:id,name', 'patient:id,name,last_name']);

        SendNewAppointmentNotification::dispatch($appointment->created_by, $appointment->id);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.appointment_created_successfully'),
                'patient' => [
                    'id' => $appointment->patient->id,
                    'name' => $appointment->patient->name,
                    'last_name' => $appointment->patient->last_name,
                ],
                'appointment' => [
                    'id' => $appointment->id,
                    'department' => $appointment->department->name ?? '',
                    'doctor' => $appointment->doctor->name ?? '',
                    'date' => $now->format('Y-m-d'),
                    'time' => $now->format('H:i:s'),
                    'token_url' => route('appointments.printToken', $appointment),
                ],
            ]);
        }

        return redirect()
            ->route('appointments.index')
            ->with('success', localize('global.appointment_created_successfully'));
    }

    public function printToken(Appointment $appointment): SymfonyResponse|RedirectResponse
    {
        $this->authorize('view', $appointment);

        $patient = $appointment->patient;
        $today = Carbon::today();

        if (! $appointment->department_id) {
            return redirect()->back()->with('error', localize('global.doctor_department_not_found'));
        }

        $departmentId = $appointment->department_id;

        $existingToken = PrintedNumber::query()
            ->where('patient_id', $patient->id)
            ->where('date', $today)
            ->where('department_id', $departmentId)
            ->first();

        if ($existingToken) {
            return response()->view('pages.patients.token', [
                'patient' => $patient,
                'printedNumber' => $existingToken,
                'name' => $appointment->doctor?->name,
                'appointment' => $appointment,
            ]);
        }

        $maxNumber = PrintedNumber::query()
            ->where('date', $today)
            ->where('department_id', $departmentId)
            ->max('number');

        $printedNumber = PrintedNumber::create([
            'patient_id' => $patient->id,
            'number' => ($maxNumber ? $maxNumber : 0) + 1,
            'date' => $today,
            'department_id' => $departmentId,
        ]);

        return response()->view('pages.patients.token', [
            'patient' => $patient,
            'printedNumber' => $printedNumber,
            'name' => $appointment->doctor?->name,
            'appointment' => $appointment,
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
                'department_id' => $appointment->department_id,
                'department_name' => $appointment->department?->name,
                'can_change_doctor' => $appointment->canChangeDoctor(),
                'doctor_reassigned' => (bool) $appointment->doctor_reassigned,
                'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
                'time' => $appointment->time,
                'is_completed' => (bool) $appointment->is_completed,
                'is_processed' => (bool) $appointment->processed_by,
                'processed_by_id' => $appointment->processed_by,
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
                'complete' => $request->user()->can('complete', $appointment),
                'edit' => $request->user()->can('update', $appointment),
                'printToken' => ! $appointment->is_completed,
            ],
            'sectionPermissions' => [
                'underReview' => $request->user()->can('patient-under-review')
                    || $request->user()->can('show-under-review-menu'),
                'hospitalization' => $request->user()->can('show-hospitalizations-menu'),
                'blood' => $request->user()->can('show-blood-request-menu'),
                'icu' => $request->user()->can('refer-to-icu')
                    || $request->user()->can('edit-icus')
                    || $request->user()->can('delete-icus'),
                'pacu' => $request->user()->can('refer-to-pacu')
                    || $request->user()->can('show-pacu-menu'),
                'anesthesia' => $request->user()->can('refer-to-anesthesia')
                    || $request->user()->can('edit-anesthesias')
                    || $request->user()->can('delete-anesthesias')
                    || $request->user()->can('show-anesthesias-menu'),
                'operations' => $request->user()->can('show-operations-menu'),
            ],
            'formData' => [
                'doctorsByDepartment' => url('/patients/doctors-by-department'),
            ],
            'urls' => [
                'index' => route('appointments.index'),
                'edit' => route('appointments.edit', $appointment),
                'printToken' => url("/appointments/{$appointment->id}/printToken"),
                'complete' => route('appointments.complete', $appointment),
                'assignDoctor' => route('appointments.assign-doctor', $appointment),
                'legacyShow' => url("/appointments/show/{$appointment->id}"),
            ],
        ]);
    }

    public function assignDoctor(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        if ($appointment->is_completed) {
            throw ValidationException::withMessages([
                'doctor_id' => [localize('global.appointment_completed')],
            ]);
        }

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $newDoctorId = (int) $validated['doctor_id'];
        $currentDoctorId = $appointment->doctor_id ? (int) $appointment->doctor_id : null;

        if ($currentDoctorId === $newDoctorId) {
            return redirect()->back();
        }

        if ($currentDoctorId !== null) {
            if ($appointment->doctor_reassigned) {
                throw ValidationException::withMessages([
                    'doctor_id' => [localize('global.doctor_can_only_be_changed_once')],
                ]);
            }

            $appointment->update([
                'doctor_id' => $newDoctorId,
                'doctor_reassigned' => true,
            ]);
        } else {
            $appointment->update([
                'doctor_id' => $newDoctorId,
                'processed_by' => $appointment->processed_by ?? $request->user()->id,
            ]);
        }

        return redirect()
            ->back()
            ->with('success', localize('global.doctor_assigned_successfully'));
    }

    public function complete(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('complete', $appointment);

        $validated = $request->validate([
            'status_remark' => 'nullable|string',
        ]);

        $appointment->update([
            'is_completed' => 1,
            'status_remark' => $validated['status_remark'] ?? null,
        ]);

        return redirect()
            ->route('appointments.completed')
            ->with('success', localize('global.appointment_updated_successfully.'));
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
                'doctorsByDepartment' => url('/patients/doctors-by-department'),
            ],
            'permissions' => [
                'delete' => $user->can('delete', $appointment),
            ],
            'urls' => [
                'index' => route('appointments.index'),
                'update' => route('appointments.update', $appointment),
                'destroy' => route('appointments.destroy', $appointment),
                'show' => route('appointments.show', $appointment),
            ],
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        if (
            $appointment->doctor_id
            && $request->doctor_id != $appointment->doctor_id
            && ! $appointment->canChangeDoctor()
        ) {
            throw ValidationException::withMessages([
                'doctor_id' => [localize('global.doctor_can_only_be_changed_once')],
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

        $newDoctorId = $validatedData['doctor_id'] ?? null;
        $currentDoctorId = $appointment->doctor_id;

        if ($newDoctorId && $currentDoctorId && (int) $newDoctorId !== (int) $currentDoctorId) {
            $validatedData['doctor_reassigned'] = true;
        }

        if ($user->clinic_type && $user->clinic_type !== 'both') {
            $validatedData['clinic_type'] = $user->clinic_type;
        }

        $appointment->update($validatedData);

        return redirect()
            ->route('appointments.index')
            ->with('success', localize('global.appointment_updated_successfully'));
    }

    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return redirect()
            ->route('appointments.index')
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
                'index' => route('appointments.index'),
                'trashed' => route('appointments.trashed'),
                'restore' => url('/appointments'),
            ],
        ]);
    }

    public function restore(Request $request, int $appointment): RedirectResponse
    {
        $appointment = Appointment::withTrashed()->findOrFail($appointment);

        $this->authorize('restore', $appointment);

        $appointment->restore();

        return redirect()
            ->route('appointments.trashed')
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
            'doctor_reassigned' => (bool) $appointment->doctor_reassigned,
            'can_change_doctor' => $appointment->canChangeDoctor(),
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

    public function departmentReport(Request $request)
    {
        return redirect()->route('patients.report', array_merge(
            $request->query(),
            ['tab' => 'department']
        ));
    }

    public function department(Request $request): Response
    {
        $this->authorize('viewMyVisits', Appointment::class);

        $user = $request->user();
        $query = $this->buildDepartmentAppointmentsQuery($request, $user);

        $paginator = $query->latest()->paginate(25)->withQueryString();

        return Inertia::render('Appointments/Department', [
            'appointments' => $this->paginatedResponse(
                $paginator,
                fn (Appointment $appointment) => $this->transformDepartmentAppointment($appointment, $user)
            ),
            'filters' => [
                'search' => (string) $request->input('search', ''),
                'token_id' => (string) $request->input('token_id', ''),
                'patient_id' => (string) $request->input('patient_id', ''),
            ],
            'filterOptions' => [
                'departments' => $this->departmentsForUser($user),
            ],
            'permissions' => $this->myVisitPermissions($user),
            'urls' => $this->myVisitUrls(),
        ]);
    }

    public function doctor(Request $request): Response
    {
        $this->authorize('viewMyVisits', Appointment::class);

        $user = $request->user();
        $query = Appointment::query()
            ->where('processed_by', $user->id)
            ->where('is_completed', '0')
            ->with([
                'patient:id,name,last_name,father_name,id_card',
                'doctor:id,name',
                'referringDoctor:id,name',
            ]);

        $this->applyMyVisitFilters($query, $request);

        $paginator = $query->latest()->paginate(25)->withQueryString();

        return Inertia::render('Appointments/Doctor', [
            'appointments' => $this->paginatedResponse(
                $paginator,
                fn (Appointment $appointment) => $this->transformDoctorAppointment($appointment, $user),
            ),
            'filters' => $this->myVisitFiltersFromRequest($request),
            'permissions' => $this->myVisitPermissions($user),
            'urls' => $this->myVisitUrls(),
        ]);
    }

    public function completed(Request $request): Response
    {
        $this->authorize('viewMyVisits', Appointment::class);

        $user = $request->user();
        $query = Appointment::query()
            ->where('processed_by', $user->id)
            ->where('is_completed', '1')
            ->with([
                'patient:id,name,last_name,father_name,id_card',
                'doctor:id,name',
                'referringDoctor:id,name',
            ]);

        $this->applyMyVisitFilters($query, $request, includePatientName: true);

        $paginator = $query->latest()->paginate(25)->withQueryString();

        return Inertia::render('Appointments/Completed', [
            'appointments' => $this->paginatedResponse(
                $paginator,
                fn (Appointment $appointment) => $this->transformDoctorAppointment($appointment, $user),
            ),
            'filters' => $this->myVisitFiltersFromRequest($request, includePatientName: true),
            'permissions' => $this->myVisitPermissions($user),
            'urls' => $this->myVisitUrls(),
        ]);
    }

    public function accept(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('accept', $appointment);

        $userDoctor = Doctor::query()->where('user_id', $request->user()->id)->first();
        $updateData = ['processed_by' => $request->user()->id];

        if ($userDoctor) {
            $updateData['doctor_id'] = $userDoctor->id;
        }

        $appointment->update($updateData);

        return redirect()
            ->back()
            ->with('success', localize('global.appointment_accepted_successfully'));
    }

    public function changeDepartment(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('changeDepartment', $appointment);

        $validated = $request->validate([
            'department_id' => 'required|exists:departments,id',
        ]);

        $appointment->update([
            'department_id' => $validated['department_id'],
        ]);

        if ($appointment->doctor_id) {
            $doctor = Doctor::find($appointment->doctor_id);
            if ($doctor && (int) $doctor->department_id !== (int) $validated['department_id']) {
                $appointment->update(['doctor_id' => null]);
            }
        }

        return redirect()
            ->back()
            ->with('success', localize('global.department_updated_successfully'));
    }

    public function report(Request $request): Response
    {
        $this->authorize('viewAny', Appointment::class);

        $user = $request->user();
        $branchId = (int) $user->branch_id;
        $hasSearch = $this->appointmentReportHasSearch($request);

        $appointments = [
            'data' => [],
            'links' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 25,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
        $summary = [
            'total' => 0,
            'completed' => 0,
            'ongoing' => 0,
            'completion_rate' => 0,
        ];

        $analytics = [
            'by_status' => [],
            'by_doctor' => [],
            'by_date' => [],
            'by_gender' => [],
        ];

        if ($hasSearch) {
            $query = $this->appointmentReportBaseQuery($request, $branchId);
            $summary = $this->appointmentReportSummary($query);
            $analytics = $this->appointmentReportAnalytics($request, $branchId);

            $perPage = $request->input('per_page', '25');
            if ($perPage === 'all') {
                $items = $query->get();
                $appointments = [
                    'data' => $items->map(fn (Appointment $item) => $this->transformAppointmentReportItem($item))->values()->all(),
                    'links' => [],
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => $items->count(),
                        'total' => $items->count(),
                        'from' => $items->count() > 0 ? 1 : null,
                        'to' => $items->count() > 0 ? $items->count() : null,
                    ],
                ];
            } else {
                $paginator = $this->paginateQuery(
                    $query,
                    $request,
                    25,
                    [10, 15, 25, 50, 100],
                );
                $appointments = $this->paginationPayload(
                    $paginator,
                    fn (Appointment $item) => $this->transformAppointmentReportItem($item),
                );
            }
        }

        return Inertia::render('Appointments/Report', [
            'appointments' => $appointments,
            'summary' => $summary,
            'analytics' => $analytics,
            'hasSearch' => $hasSearch,
            'filters' => $this->collectFilters($request, self::APPOINTMENT_REPORT_FILTER_KEYS),
            'filterOptions' => [
                'doctors' => Doctor::query()
                    ->where('active_status', true)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'users' => User::query()
                    ->where('status', 1)
                    ->orderBy('name')
                    ->get(['id', 'name', 'last_name']),
                'provinces' => Province::query()
                    ->orderBy('name_dr')
                    ->get(['id', 'name_dr']),
                'districts' => District::query()
                    ->orderBy('name_dr')
                    ->get(['id', 'name_dr', 'province_id']),
                'relations' => Relation::query()
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
            'urls' => [
                'current' => route('appointments.report'),
                'index' => route('appointments.index'),
                'export' => route('appointments.export-report'),
            ],
        ]);
    }

    /**
     * Mirrors legacy AppointmentController::departmentAppointments() query logic.
     *
     * @return \Illuminate\Database\Eloquent\Builder<Appointment>
     */
    private function buildDepartmentAppointmentsQuery(Request $request, $user)
    {
        $appointmentId = $this->parseNumericFilter($request->input('token_id'));
        $filterPatientId = $this->parseNumericFilter($request->input('patient_id'));
        $userClinicType = $user->clinic_type;
        $filterByClinicType = $userClinicType && $userClinicType !== 'both';

        if ($appointmentId !== null) {
            $query = Appointment::query()->where('id', $appointmentId);

            if ($filterByClinicType) {
                $query->where('clinic_type', $userClinicType);
            }
        } else {
            $query = Appointment::query()
                ->whereNull('doctor_id')
                ->whereNull('processed_by');

            if ($filterByClinicType) {
                $query->where('clinic_type', $userClinicType);
            }

            $query->when($user->doctor, function ($departmentQuery) use ($user) {
                $departmentQuery->where('department_id', $user->doctor->department_id);
            });
        }

        $query->with([
            'patient:id,name,last_name,father_name,id_card',
            'department:id,name',
            'referringDoctor:id,name',
            'processedBy:id,name,last_name',
        ]);

        if ($filterPatientId !== null) {
            $query->where('patient_id', $filterPatientId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($patientQuery) use ($search) {
                $patientQuery->where('name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('id_card', 'like', '%'.$search.'%')
                    ->orWhere('phone', 'like', '%'.$search.'%')
                    ->orWhere('father_name', 'like', '%'.$search.'%')
                    ->orWhere('nid', 'like', '%'.$search.'%');
            });
        }

        return $query;
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Appointment>  $query
     */
    private function applyMyVisitFilters($query, Request $request, bool $includePatientName = false): void
    {
        $appointmentId = $this->parseNumericFilter($request->input('token_id'));
        $filterPatientId = $this->parseNumericFilter($request->input('patient_id'));

        if ($appointmentId !== null) {
            $query->where('id', $appointmentId);
        }

        if ($filterPatientId !== null) {
            $query->where('patient_id', $filterPatientId);
        }

        if ($includePatientName && $request->filled('patient_name')) {
            $term = '%'.$request->patient_name.'%';
            $query->whereHas('patient', function ($patientQuery) use ($term) {
                $patientQuery->where('name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('father_name', 'like', $term);
            });
        }
    }

    private function parseNumericFilter(mixed $value): ?int
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $trimmed = trim((string) $value);

        if (is_numeric($trimmed) && (int) $trimmed > 0) {
            return (int) $trimmed;
        }

        $numericId = preg_replace('/[^0-9]/', '', $trimmed);

        if ($numericId !== '' && is_numeric($numericId) && (int) $numericId > 0) {
            return (int) $numericId;
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private function myVisitFiltersFromRequest(Request $request, bool $includePatientName = false): array
    {
        $filters = [
            'token_id' => (string) $request->input('token_id', ''),
            'patient_id' => (string) $request->input('patient_id', ''),
        ];

        if ($includePatientName) {
            $filters['patient_name'] = (string) $request->input('patient_name', '');
        }

        return $filters;
    }

    /**
     * @return array<string, mixed>
     */
    private function paginatedResponse($paginator, callable $transformer): array
    {
        return [
            'data' => collect($paginator->items())
                ->map($transformer)
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
        ];
    }

    private function departmentsForUser($user): array
    {
        return $this->departmentsForReportUser($user);
    }

    /**
     * @return array<string, bool>
     */
    private function myVisitPermissions($user): array
    {
        return [
            'view' => $user->can('viewAny', Appointment::class),
            'history' => $user->can('viewAny', Appointment::class),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function myVisitUrls(): array
    {
        return [
            'department' => route('appointments.department'),
            'doctor' => route('appointments.doctor'),
            'completed' => route('appointments.completed'),
            'show' => url('/appointments'),
            'patientHistory' => url('/patients/history'),
            'accept' => url('/appointments'),
            'changeDepartment' => url('/appointments'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDepartmentAppointment(Appointment $appointment, $user): array
    {
        $patient = $appointment->patient;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'department_id' => $appointment->department_id,
            'id_card' => $patient?->id_card,
            'patient_name' => $patient?->name,
            'father_name' => $patient?->father_name,
            'department_name' => $appointment->department?->name,
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
            'time' => $appointment->time,
            'is_accepted' => (bool) $appointment->processed_by,
            'refferal_remarks' => $appointment->refferal_remarks,
            'referring_doctor_name' => $appointment->referringDoctor?->name,
            'permissions' => [
                'accept' => $user->can('accept', $appointment),
                'changeDepartment' => $user->can('changeDepartment', $appointment),
                'view' => $user->can('view', $appointment)
                    && (
                        $appointment->processed_by
                        || $user->hasRole(['super_admin', 'admin'])
                    ),
                'history' => $patient && $user->can('view', $patient),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDoctorAppointment(Appointment $appointment, $user): array
    {
        $patient = $appointment->patient;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'id_card' => $patient?->id_card,
            'patient_name' => $patient?->name,
            'father_name' => $patient?->father_name,
            'doctor_name' => $appointment->doctor?->name,
            'referring_doctor_name' => $appointment->referringDoctor?->name,
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
            'time' => $appointment->time,
            'permissions' => [
                'view' => $user->can('view', $appointment),
                'history' => $patient && $user->can('view', $patient),
            ],
        ];
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
        $creator = $appointment->creator;
        $processedByName = $processor
            ? trim("{$processor->name} {$processor->last_name}")
            : null;
        $registeredByName = $creator
            ? trim("{$creator->name} {$creator->last_name}")
            : null;
        $processedBy = $processedByName ?: $registeredByName;

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'id_card' => $appointment->patient?->id_card,
            'patient_name' => $appointment->patient?->name,
            'father_name' => $appointment->patient?->father_name,
            'phone' => $appointment->patient?->phone,
            'doctor_name' => $appointment->doctor?->name,
            'department_name' => $appointment->department?->name,
            'date' => $appointment->date ? verta($appointment->date)->format('Y-m-d') : null,
            'time' => $appointment->time,
            'is_completed' => (bool) $appointment->is_completed,
            'processed_by' => $processedBy ?: null,
            'permissions' => [
                'view' => $canView,
                'history' => $canViewHistory,
                'edit' => $canEdit,
                'delete' => $canDelete,
            ],
        ];
    }
}
