<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PatientController as LegacyPatientController;
use App\Http\Controllers\V1\Concerns\ManagesAppointmentReport;
use App\Http\Controllers\V1\Concerns\ManagesPatientReport;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\District;
use App\Models\MiliteryType;
use App\Models\Patient;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\RecipientPart;
use App\Models\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    use ManagesAppointmentReport;
    use ManagesPatientReport;
    use PaginatesInertiaIndex;
    private const INDEX_FILTER_KEYS = [
        'patient_id',
        'name',
        'father_name',
        'last_name',
        'phone',
        'card_search',
        'militery_type_id',
        'province_id',
        'gender',
        'job_category',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Patient::class);

        $user = $request->user();

        $query = Patient::query()
            ->where('branch_id', $user->branch_id)
            ->with([
                'militeryType:id,name',
                'province:id,name_dr',
                'district:id,name_dr',
                'creator:id,name,last_name',
            ]);

        if ($request->filled('patient_id')) {
            $query->where('id', $request->patient_id);
        }

        if ($request->filled('name')) {
            $query->where('name', 'like', '%'.$request->name.'%');
        }

        if ($request->filled('father_name')) {
            $query->where('father_name', 'like', '%'.$request->father_name.'%');
        }

        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', '%'.$request->last_name.'%');
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%'.$request->phone.'%');
        }

        if ($request->filled('card_search')) {
            $query->where('id_card', 'like', '%'.$request->card_search.'%');
        }

        if ($request->filled('militery_type_id')) {
            $query->where('militery_type_id', $request->militery_type_id);
        }

        if ($request->filled('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('job_category')) {
            $query->where('job_category', $request->job_category);
        }

        $paginator = $query->latest()->paginate(15)->withQueryString();

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Patients/Index', [
            'patients' => [
                'data' => collect($paginator->items())
                    ->map(fn (Patient $patient) => $this->transformPatientForIndex($patient))
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
                'militeryTypes' => MiliteryType::query()->orderBy('name')->get(['id', 'name']),
                'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
            ],
            'permissions' => $this->patientPermissions($user),
            'urls' => [
                'index' => route('patients.index'),
                'create' => route('patients.create'),
                'show' => url('/patients'),
                'edit' => url('/patients'),
                'destroy' => url('/patients'),
            ],
        ]);
    }

    public function show(Request $request, Patient $patient): Response
    {
        $this->authorize('view', $patient);

        $user = $request->user();

        $patient->load([
            'province:id,name_dr',
            'district:id,name_dr',
            'militeryType:id,name',
            'relation:id,name',
            'recipient:id,name',
            'recipientPart:id,name,code,recipient_id',
            'referralRecipientPart:id,name,code,recipient_id',
            'creator:id,name,last_name',
            'appointments.doctor:id,name',
            'diagnoses' => fn ($query) => $query->orderByDesc('created_at'),
        ]);

        $canAccessNephrology = $user->hasPermissionTo('access-nephrology-registrations');

        $nephrologyRegistrations = [];
        $hemodialysisSessions = [];

        if ($canAccessNephrology) {
            $nephrologyRegistrations = $patient->nephrologyRegistrations()
                ->with(['doctor:id,name', 'disease:id,name'])
                ->latest('visit_date')
                ->limit(10)
                ->get()
                ->map(fn ($registration) => [
                    'id' => $registration->id,
                    'ref_no' => $registration->ref_no,
                    'visit_date' => $registration->visit_date
                        ? verta($registration->visit_date)->format('Y-m-d')
                        : null,
                    'doctor_name' => $registration->doctor?->name,
                    'diagnosis' => $registration->displayDiagnosis(),
                    'show_url' => route('nephrology-registrations.show', $registration),
                ])
                ->values()
                ->all();

            $hemodialysisSessions = $patient->hemodialysisSessions()
                ->latest('session_date')
                ->limit(10)
                ->get()
                ->map(fn ($session) => [
                    'id' => $session->id,
                    'ref_no' => $session->ref_no,
                    'session_date' => $session->session_date
                        ? verta($session->session_date)->format('Y-m-d')
                        : null,
                    'duration_minutes' => $session->duration_minutes,
                    'status' => $session->status,
                    'show_url' => route('hemodialysis-sessions.show', $session),
                ])
                ->values()
                ->all();
        }

        $diagnoses = $patient->diagnoses;
        $primaryDiagnoses = $diagnoses->where('type', 0)->values()->map(fn ($diagnose) => [
            'id' => $diagnose->id,
            'description' => $diagnose->description,
            'date' => verta($diagnose->created_at)->format('Y-m-d'),
        ])->all();
        $finalDiagnoses = $diagnoses->where('type', 1)->values()->map(fn ($diagnose) => [
            'id' => $diagnose->id,
            'description' => $diagnose->description,
            'date' => verta($diagnose->created_at)->format('Y-m-d'),
        ])->all();

        $appointments = $patient->appointments
            ->sortByDesc('created_at')
            ->values()
            ->map(fn ($appointment, $index) => [
                'id' => $appointment->id,
                'number' => $index + 1,
                'doctor_name' => $appointment->doctor?->name,
                'date' => verta($appointment->created_at)->format('Y-m-d H:i'),
            ])
            ->all();

        return Inertia::render('Patients/Show', [
            'patient' => $this->transformPatientForShow($patient),
            'appointments' => $appointments,
            'diagnoses' => [
                'primary' => $primaryDiagnoses,
                'final' => $finalDiagnoses,
            ],
            'nephrologyRegistrations' => $nephrologyRegistrations,
            'hemodialysisSessions' => $hemodialysisSessions,
            'appointmentForm' => [
                'branchId' => $patient->branch_id,
                'clinicType' => $user->clinic_type,
                'departments' => $user->category_id
                    ? Department::query()->where('category_id', $user->category_id)->orderBy('name')->get(['id', 'name'])
                    : Department::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => [
                'edit' => $user->can('update', $patient),
                'delete' => $user->can('delete', $patient),
                'printCard' => $user->can('printCard', $patient),
                'createAppointment' => $user->hasPermissionTo('create-appointment'),
                'uploadImage' => $user->can('uploadImage', $patient),
                'nephrology' => $canAccessNephrology,
                'foreignCountryReferral' => $user->can('viewAny', \App\Models\ForeignCountryReferral::class),
            ],
            'urls' => [
                'index' => route('patients.index'),
                'edit' => route('patients.edit', $patient),
                'destroy' => route('patients.destroy', $patient),
                'printCard' => route('patients.print-card', $patient),
                'webcam' => route('patients.webcam', $patient),
                'appointmentStore' => route('appointments.store'),
                'doctorsByDepartment' => url('/patients/doctors-by-department'),
                'hemodialysisCreate' => route('hemodialysis-sessions.create', ['patient_id' => $patient->id]),
                'hemodialysisIndex' => route('hemodialysis-sessions.index', ['patient_id' => $patient->id]),
            ],
        ]);
    }

    public function edit(Request $request, Patient $patient): Response
    {
        $this->authorize('update', $patient);

        $user = $request->user();

        $patient->load(['province:id,name_dr', 'district:id,name_dr']);

        return Inertia::render('Patients/Edit', [
            'mode' => 'edit',
            'patient' => $this->transformPatientForForm($patient),
            'formData' => $this->buildFormData($user, $patient),
            'permissions' => [
                'delete' => $user->can('delete', $patient),
            ],
            'urls' => $this->buildFormUrls($patient),
        ]);
    }

    public function update(Request $request, Patient $patient)
    {
        $this->authorize('update', $patient);

        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $request->headers->set('Accept', 'application/json');

        return app(LegacyPatientController::class)->update($request, $patient);
    }

    public function destroy(Request $request, Patient $patient)
    {
        $this->authorize('delete', $patient);

        $patient->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.patient_deleted_successfully.'),
            ]);
        }

        return redirect()
            ->route('patients.index')
            ->with('success', localize('global.patient_deleted_successfully.'));
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Patient::class);

        $user = $request->user();

        return Inertia::render('Patients/Create', [
            'mode' => 'create',
            'formData' => $this->buildFormData($user),
            'urls' => $this->buildFormUrls(),
            'canAccessVip' => $user->hasRole(['super_admin', 'admin'])
                || $user->can('access-to-vip'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Patient::class);

        $proxy = $request->duplicate();
        $proxy->headers->set('X-Requested-With', 'XMLHttpRequest');
        $proxy->headers->set('Accept', 'application/json');

        return app(LegacyPatientController::class)->store($proxy);
    }

    public function districts(int $provinceId): JsonResponse
    {
        $this->authorize('viewAny', Patient::class);

        $districts = District::query()
            ->where('province_id', $provinceId)
            ->orderBy('name_dr')
            ->get(['id', 'name_dr']);

        return response()->json([
            'success' => true,
            'districts' => $districts,
        ]);
    }

    public function recipientParts(int $recipientId): JsonResponse
    {
        $this->authorize('viewAny', Patient::class);

        $parts = RecipientPart::query()
            ->where('recipient_id', $recipientId)
            ->orderBy('name')
            ->get(['id', 'name', 'code']);

        return response()->json([
            'success' => true,
            'recipientParts' => $parts,
        ]);
    }

    public function doctorsByDepartment(int $departmentId, Request $request): JsonResponse
    {
        $this->authorize('viewAny', Patient::class);

        return app(LegacyPatientController::class)->getDoctorsByDepartment($departmentId, $request);
    }

    public function report(Request $request): Response
    {
        $this->authorize('viewAny', Patient::class);

        $user = $request->user();
        $branchId = (int) $user->branch_id;
        $canAccessDepartment = $user->can('viewAny', Appointment::class);

        $tab = $request->input('tab', 'patients') === 'department' && $canAccessDepartment
            ? 'department'
            : 'patients';

        $patientsTab = null;
        $departmentTab = null;

        if ($tab === 'patients') {
            $patientsTab = $this->buildPatientsReportTab($request, $branchId);
        } else {
            $departmentTab = $this->buildDepartmentReportTab($request, $user, $branchId);
        }

        return Inertia::render('Patients/Report', [
            'tab' => $tab,
            'permissions' => [
                'department' => $canAccessDepartment,
            ],
            'patientsTab' => $patientsTab,
            'departmentTab' => $departmentTab,
            'urls' => [
                'current' => route('patients.report'),
                'index' => route('patients.index'),
                'export' => route('patients.export-report'),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPatientsReportTab(Request $request, int $branchId): array
    {
        $hasSearch = $this->patientReportHasSearch($request);

        $patients = [
            'data' => [],
            'links' => [],
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'per_page' => 15,
                'total' => 0,
                'from' => null,
                'to' => null,
            ],
        ];
        $summary = [
            'total' => 0,
            'male' => 0,
            'female' => 0,
            'military' => 0,
            'civilian' => 0,
        ];
        $analytics = [
            'by_gender' => [],
            'by_type' => [],
            'by_date' => [],
        ];

        if ($hasSearch) {
            $query = $this->patientReportBaseQuery($request, $branchId);
            $summary = $this->patientReportSummary($query);
            $analytics = $this->patientReportAnalytics($query);

            $perPage = $request->input('per_page', '15');
            if ($perPage === 'all') {
                $items = $query->get();
                $patients = [
                    'data' => $items->map(fn (Patient $item) => $this->transformPatientReportItem($item))->values()->all(),
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
                $paginator = $this->paginateQuery($query, $request, 15, [10, 15, 25, 50, 100]);
                $patients = $this->paginationPayload(
                    $paginator,
                    fn (Patient $item) => $this->transformPatientReportItem($item),
                );
            }
        }

        return [
            'patients' => $patients,
            'summary' => $summary,
            'analytics' => $analytics,
            'hasSearch' => $hasSearch,
            'filters' => $this->collectFilters($request, $this->patientReportFilterKeys()),
            'filterOptions' => [
                'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
                'districts' => District::query()->orderBy('name_dr')->get(['id', 'name_dr', 'province_id']),
                'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
            ],
        ];
    }

    /**
     * @param  \App\Models\User  $user
     * @return array<string, mixed>
     */
    private function buildDepartmentReportTab(Request $request, $user, int $branchId): array
    {
        $hasSearch = $this->departmentReportHasSearch($request);

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
            'male' => 0,
            'female' => 0,
        ];
        $analytics = [
            'by_gender' => [],
        ];

        if ($hasSearch) {
            $query = $this->departmentReportBaseQuery($request, $branchId);
            $analytics = $this->departmentReportAnalytics($query);
            $summary = [
                'total' => $analytics['total'],
                'male' => $analytics['male'],
                'female' => $analytics['female'],
            ];

            $paginator = $this->paginateQuery($query, $request, 25, [10, 25, 50, 100]);
            $appointments = $this->paginationPayload(
                $paginator,
                fn (Appointment $item) => $this->transformDepartmentReportItem($item),
            );
        }

        return [
            'appointments' => $appointments,
            'summary' => $summary,
            'analytics' => $analytics,
            'hasSearch' => $hasSearch,
            'filters' => $this->collectFilters($request, $this->departmentReportFilterKeys()),
            'filterOptions' => [
                'departments' => $this->departmentsForReportUser($user),
            ],
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function patientPermissions($user): array
    {
        return [
            'create' => $user->can('create', Patient::class),
            'edit' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('edit-patients'),
            'delete' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('delete-patients'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData($user, ?Patient $patient = null): array
    {
        $departments = $user->category_id
            ? Department::query()->where('category_id', $user->category_id)->orderBy('name')->get(['id', 'name'])
            : Department::query()->orderBy('name')->get(['id', 'name']);

        $registrationDate = $patient?->registration_date
            ? verta($patient->registration_date)->format('Y-m-d')
            : verta()->format('Y-m-d');

        $districts = $patient?->province_id
            ? District::query()
                ->where('province_id', $patient->province_id)
                ->orderBy('name_dr')
                ->get(['id', 'name_dr'])
            : [];

        $recipientParts = $patient?->referred_by
            ? RecipientPart::query()
                ->where('recipient_id', $patient->referred_by)
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
            : [];

        $referralRecipientParts = $patient?->referral_recipient
            ? RecipientPart::query()
                ->where('recipient_id', $patient->referral_recipient)
                ->orderBy('name')
                ->get(['id', 'name', 'code'])
            : [];

        return [
            'branchId' => $patient?->branch_id ?? $user->branch_id,
            'clinicType' => $user->clinic_type,
            'registrationDate' => $registrationDate,
            'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
            'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
            'relations' => Relation::query()->orderBy('name')->get(['id', 'name']),
            'militeryTypes' => MiliteryType::query()->orderBy('name')->get(['id', 'name']),
            'departments' => $departments,
            'districts' => $districts,
            'recipientParts' => $recipientParts,
            'referralRecipientParts' => $referralRecipientParts,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildFormUrls(?Patient $patient = null): array
    {
        $urls = [
            'districts' => url('/patients/districts'),
            'recipientParts' => url('/patients/recipient-parts'),
            'doctorsByDepartment' => url('/patients/doctors-by-department'),
            'back' => route('patients.index'),
        ];

        if ($patient) {
            $urls['update'] = route('patients.update', $patient);
            $urls['show'] = route('patients.show', $patient);
            $urls['destroy'] = route('patients.destroy', $patient);
        } else {
            $urls['store'] = route('patients.store');
        }

        return $urls;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPatientForForm(Patient $patient): array
    {
        $ageParts = $this->parseAge($patient->age);

        $type = (string) ($patient->type ?? '0');
        if ($patient->isVipRecord()) {
            $type = '4';
        }

        return [
            'id' => $patient->id,
            'type' => $type,
            'is_vip' => $patient->isVipRecord(),
            'id_card' => $patient->id_card ?? '',
            'name' => $patient->name ?? '',
            'last_name' => $patient->last_name ?? '',
            'father_name' => $patient->father_name ?? '',
            'nid' => $patient->nid ?? '',
            'job' => $patient->job ?? '',
            'job_category' => (string) ($patient->job_category ?? '0'),
            'militery_type_id' => $patient->militery_type_id ? (string) $patient->militery_type_id : '',
            'rank' => $patient->rank ?? '',
            'phone' => $patient->phone ?? '',
            'age_year' => $ageParts['year'],
            'age_month' => $ageParts['month'],
            'age_day' => $ageParts['day'],
            'gender' => $patient->gender !== null ? (string) $patient->gender : '',
            'referred_by' => $patient->referred_by ? (string) $patient->referred_by : '',
            'commanded_by' => $patient->commanded_by ?? '',
            'recipient_part_id' => $patient->recipient_part_id ? (string) $patient->recipient_part_id : '',
            'province_id' => $patient->province_id ? (string) $patient->province_id : '',
            'district_id' => $patient->district_id ? (string) $patient->district_id : '',
            'referral_name' => $patient->referral_name ?? '',
            'referral_last_name' => $patient->referral_last_name ?? '',
            'referral_father_name' => $patient->referral_father_name ?? '',
            'referral_nid' => $patient->referral_nid ?? '',
            'referral_id_card' => $patient->referral_id_card ?? '',
            'referral_phone' => $patient->referral_phone ?? '',
            'referral_recipient' => $patient->referral_recipient ? (string) $patient->referral_recipient : '',
            'referral_recipient_part_id' => $patient->referral_recipient_part_id ? (string) $patient->referral_recipient_part_id : '',
            'relation_id' => $patient->relation_id ? (string) $patient->relation_id : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPatientForShow(Patient $patient): array
    {
        $creator = $patient->creator;
        $createdBy = $creator
            ? trim("{$creator->name} {$creator->last_name}")
            : null;

        return [
            'id' => $patient->id,
            'id_card' => $patient->id_card,
            'name' => $patient->name,
            'last_name' => $patient->last_name,
            'father_name' => $patient->father_name,
            'nid' => $patient->nid,
            'phone' => $patient->phone,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'job' => $patient->job,
            'rank' => $patient->rank,
            'job_category' => $patient->job_category,
            'type' => $patient->type,
            'is_vip' => $patient->isVipRecord(),
            'province' => $patient->province?->name_dr,
            'district' => $patient->district?->name_dr,
            'militery_type' => $patient->militeryType?->name,
            'relation' => $patient->relation?->name,
            'referred_by' => $this->formatRecipientDisplay($patient),
            'commanded_by' => $patient->commanded_by,
            'recipient_part' => $patient->recipientPart?->displayName(),
            'referral_name' => $patient->referral_name,
            'referral_last_name' => $patient->referral_last_name,
            'referral_father_name' => $patient->referral_father_name,
            'referral_nid' => $patient->referral_nid,
            'referral_id_card' => $patient->referral_id_card,
            'referral_phone' => $patient->referral_phone,
            'registration_date' => $patient->registration_date
                ? verta($patient->registration_date)->format('Y-m-d')
                : null,
            'created_at' => verta($patient->created_at)->format('Y-m-d'),
            'created_by' => $createdBy,
            'image' => $patient->image ? asset($patient->image) : null,
        ];
    }

    private function formatRecipientDisplay(Patient $patient): ?string
    {
        if ($patient->recipientPart) {
            $recipientName = $patient->recipient?->name;

            return $recipientName
                ? "{$recipientName} / {$patient->recipientPart->displayName()}"
                : $patient->recipientPart->displayName();
        }

        return $patient->recipient?->name ?? $patient->referral_name;
    }

    /**
     * @return array{year: string, month: string, day: string}
     */
    private function parseAge(?string $age): array
    {
        $parts = ['year' => '', 'month' => '', 'day' => ''];

        if (! $age) {
            return $parts;
        }

        if (preg_match('/(\d+)\s*ساله/u', $age, $matches)) {
            $parts['year'] = $matches[1];
        } elseif (preg_match('/(\d+)\s*ماه/u', $age, $matches)) {
            $parts['month'] = $matches[1];
        } elseif (preg_match('/(\d+)\s*روز/u', $age, $matches)) {
            $parts['day'] = $matches[1];
        }

        return $parts;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformPatientForIndex(Patient $patient): array
    {
        $provinceName = $patient->province?->name_dr;
        $districtName = $patient->district?->name_dr;

        if ($provinceName && $districtName) {
            $location = "{$provinceName} / {$districtName}";
        } elseif ($provinceName) {
            $location = $provinceName;
        } elseif ($districtName) {
            $location = $districtName;
        } else {
            $location = '-';
        }

        $creator = $patient->creator;
        $createdBy = $creator
            ? trim("{$creator->name} {$creator->last_name}")
            : null;

        return [
            'id' => $patient->id,
            'id_card' => $patient->id_card,
            'name' => $patient->name,
            'last_name' => $patient->last_name,
            'father_name' => $patient->father_name,
            'is_vip' => $patient->isVipRecord(),
            'location' => $location,
            'age' => $patient->age,
            'militery_type' => $patient->militeryType?->name,
            'phone' => $patient->phone,
            'created_by' => $createdBy,
        ];
    }
}
