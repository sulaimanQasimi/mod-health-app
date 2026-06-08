<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesLaboratoryRegistrations;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\LabType;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class LaboratoryController extends Controller
{
    use ManagesLaboratoryRegistrations;

    private const RESULTS_FILTER_KEYS = [
        'search',
        'patient_id',
        'status',
        'priority',
        'date_from',
        'date_to',
        'per_page',
    ];

    private const GROUPED_FILTER_KEYS = [
        'search',
        'patient_id',
        'status',
        'priority',
        'doctor',
        'date_from',
        'date_to',
        'per_page',
    ];

    private const REPORT_FILTER_KEYS = [
        'from',
        'to',
        'test_type',
        'patient_id',
        'per_page',
    ];

    private const REPORT_DETAILED_FILTER_KEYS = [
        'from',
        'to',
        'test_type',
        'patient_id',
        'status',
        'doctor_id',
        'branch_id',
        'department_id',
        'created_by',
        'updated_by',
        'completed_by',
        'completed_at_from',
        'completed_at_to',
        'assigned_to',
        'assigned_at_from',
        'assigned_at_to',
        'assigned_section_id',
        'notes',
        'per_page',
    ];

    public function pending(Request $request): Response
    {
        return $this->renderResultsList($request, 'pending', 'pending');
    }

    public function inProgress(Request $request): Response
    {
        return $this->renderResultsList($request, 'in_progress', 'in_progress');
    }

    public function completed(Request $request): Response
    {
        return $this->renderResultsList($request, 'completed', 'completed');
    }

    public function scan(Request $request): Response
    {
        $this->authorize('viewTools', PatientTestRegistration::class);

        return Inertia::render('Laboratory/Scan', [
            'urls' => [
                'scan' => route('react.laboratory.scan.submit'),
                'pending' => route('react.laboratory.results.pending'),
            ],
            'error' => session('error'),
        ]);
    }

    public function scanSubmit(Request $request): RedirectResponse
    {
        $this->authorize('viewTools', PatientTestRegistration::class);

        $request->validate(['ref_no' => 'required|string']);

        $registration = $this->scopedRegistrationQuery($request->user())
            ->where('ref_no', $request->input('ref_no'))
            ->first();

        if (! $registration) {
            return redirect()
                ->route('react.laboratory.scan')
                ->with('error', localize('global.test_not_found'));
        }

        if ($registration->status === 'completed') {
            return redirect()->route('laboratory.reports.print', $registration->ref_no);
        }

        return redirect()->route('react.laboratory.results.show', $registration);
    }

    public function showResults(PatientTestRegistration $registration): Response|RedirectResponse
    {
        $this->authorize('fillResults', $registration);

        $user = request()->user();

        $registration = $this->scopedRegistrationQuery($user)
            ->with([
                'testable.patient',
                'labType.category',
                'labType.directLabTestParameters',
                'doctor',
                'assignedTo',
            ])
            ->findOrFail($registration->id);

        if ($registration->status === 'completed') {
            return redirect()->route('laboratory.reports.print', $registration->ref_no);
        }

        $patient = $registration->testable?->patient;
        if (! $patient) {
            abort(404, 'Patient not found for this registration');
        }

        $parameterCount = $registration->labType?->directLabTestParameters?->count() ?? 0;
        $isParametered = $parameterCount > 0;
        $resultEntries = $this->loadOrCreateResultEntries($registration);

        $textResult = $resultEntries->first(fn ($r) => $r->lab_parameter_id === null)?->text_result ?? '';

        if (! $isParametered && $textResult === '') {
            $textResult = (string) PatientTestResult::query()
                ->where('test_registration_id', $registration->id)
                ->whereNull('lab_parameter_id')
                ->value('text_result');
        }

        $needsAccept = $registration->status === 'pending' && ! $registration->assigned_to;
        $canSave = ! $needsAccept && $user->can('fillResults', $registration);

        return Inertia::render('Laboratory/Results/Show', [
            'registration' => [
                'id' => $registration->id,
                'ref_no' => $registration->ref_no,
                'status' => $registration->status,
                'priority' => $registration->priority,
                'lab_type_name' => $registration->labType?->name,
                'category_name' => $registration->labType?->category?->name,
                'doctor_name' => $registration->doctor?->name,
                'assigned_to_name' => $registration->assignedTo
                    ? trim("{$registration->assignedTo->name} {$registration->assignedTo->last_name}")
                    : null,
                'registration_date' => $registration->registration_date
                    ? verta($registration->registration_date)->format('Y-m-d')
                    : null,
                'notes' => $registration->notes,
            ],
            'patient' => [
                'id' => $patient->id,
                'name' => trim("{$patient->name} {$patient->last_name}"),
                'father_name' => $patient->father_name,
                'age' => $patient->age,
                'phone' => $patient->phone,
                'id_card' => $patient->id_card,
                'gender' => $patient->gender,
            ],
            'is_parametered' => $isParametered,
            'results' => $isParametered
                ? $resultEntries
                    ->filter(fn ($r) => $r->lab_parameter_id !== null)
                    ->map(fn ($r) => $this->transformResultEntry($r))
                    ->values()
                    ->all()
                : [],
            'text_result' => $textResult ?? '',
            'permissions' => [
                'accept' => $user->can('accept', $registration),
                'canSave' => $canSave,
            ],
            'urls' => [
                'update' => route('react.laboratory.results.update', $registration),
                'accept' => route('react.laboratory.results.accept', $registration),
                'print' => route('laboratory.reports.print', $registration->ref_no),
                'back' => route('react.laboratory.results.in-progress'),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'completed' => session('completed'),
            ],
        ]);
    }

    public function updateResults(Request $request, PatientTestRegistration $registration): RedirectResponse
    {
        $this->authorize('fillResults', $registration);

        $request->validate([
            'results' => 'nullable|array',
            'results.*' => 'nullable|string',
            'text_result' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $user = $request->user();

        $registration = $this->scopedRegistrationQuery($user)
            ->with('labType.directLabTestParameters')
            ->findOrFail($registration->id);

        if ($registration->status === 'completed') {
            return redirect()
                ->route('laboratory.reports.print', $registration->ref_no)
                ->with('error', localize('global.cannot_update_completed_test'));
        }

        if ($registration->status === 'pending' && ! $registration->assigned_to) {
            return back()->with('error', localize('global.accept_test_to_continue'));
        }

        $patientId = $registration->testable?->patient_id ?? $registration->testable?->patient?->id;
        $parameterCount = $registration->labType?->directLabTestParameters?->count() ?? 0;
        $isParametered = $parameterCount > 0;

        if (! $isParametered) {
            $existingResult = PatientTestResult::query()
                ->where('test_registration_id', $registration->id)
                ->whereNull('lab_parameter_id')
                ->first();

            if ($existingResult) {
                $existingResult->update(['text_result' => $request->input('text_result')]);
            } else {
                PatientTestResult::create([
                    'patient_id' => $patientId,
                    'ref_no' => $registration->ref_no,
                    'lab_parameter_id' => null,
                    'text_result' => $request->input('text_result'),
                    'test_registration_id' => $registration->id,
                ]);
            }
        } elseif ($request->has('results')) {
            foreach ($request->input('results', []) as $parameterId => $resultValue) {
                $existingResult = PatientTestResult::query()
                    ->where('ref_no', $registration->ref_no)
                    ->where('lab_parameter_id', $parameterId)
                    ->first();

                if ($existingResult) {
                    $existingResult->update(['result' => $resultValue]);
                } else {
                    PatientTestResult::create([
                        'patient_id' => $patientId,
                        'ref_no' => $registration->ref_no,
                        'lab_parameter_id' => $parameterId,
                        'result' => $resultValue,
                        'test_registration_id' => $registration->id,
                    ]);
                }
            }
        }

        $registration->notes = $request->input('notes');
        $registration->save();

        $allResults = PatientTestResult::query()
            ->where('test_registration_id', $registration->id)
            ->get();

        $allFilled = false;

        if (! $isParametered && filled($request->input('text_result'))) {
            $allFilled = true;
        } elseif ($isParametered && $request->has('results')) {
            $expectedCount = $parameterCount;
            $filledCount = $allResults
                ->whereNotNull('lab_parameter_id')
                ->filter(fn ($r) => $r->result !== null && $r->result !== '')
                ->count();
            $allFilled = $expectedCount > 0 && $filledCount >= $expectedCount;
        }

        if ($allFilled) {
            $registration->markCompleted();

            return redirect()
                ->route('laboratory.reports.print', $registration->ref_no)
                ->with('success', localize('global.results_updated_successfully'))
                ->with('completed', true);
        }

        return back()
            ->with('success', localize('global.results_updated_successfully'));
    }

    public function grouped(Request $request): Response
    {
        $this->authorize('viewTools', PatientTestRegistration::class);

        $request->validate([
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:pending,in_progress,completed,cancelled',
            'priority' => 'nullable|in:normal,urgent,stat',
            'doctor' => 'nullable|integer',
            'date_from' => 'nullable|string',
            'date_to' => 'nullable|string',
            'patient_id' => 'nullable|string',
            'per_page' => 'nullable|integer|min:10|max:100',
        ]);

        $user = $request->user();

        $query = $this->scopedRegistrationQuery($user)
            ->with([
                'testable.patient',
                'labType.category',
                'doctor',
                'assignedTo',
            ])
            ->whereNotNull('category_id');

        $query = $this->applyResultsFilters($query, $request);

        $statsBase = clone $query;
        $stats = [
            'pending' => (clone $statsBase)->where('status', 'pending')->count(),
            'in_progress' => (clone $statsBase)->where('status', 'in_progress')->count(),
            'completed' => (clone $statsBase)->where('status', 'completed')->count(),
            'cancelled' => (clone $statsBase)->where('status', 'cancelled')->count(),
            'total' => (clone $statsBase)->count(),
        ];

        $perPage = min(max((int) $request->input('per_page', 15), 10), 100);
        $paginator = $query->latest('registration_date')->paginate($perPage)->withQueryString();

        $groups = collect($paginator->items())
            ->groupBy('category_id')
            ->map(function ($registrations, $categoryId) use ($user) {
                $first = $registrations->first();
                $patient = $first?->testable?->patient;

                return [
                    'category_id' => (int) $categoryId,
                    'patient_name' => $patient
                        ? trim("{$patient->name} {$patient->last_name}")
                        : null,
                    'test_count' => $registrations->count(),
                    'status_summary' => [
                        'pending' => $registrations->where('status', 'pending')->count(),
                        'in_progress' => $registrations->where('status', 'in_progress')->count(),
                        'completed' => $registrations->where('status', 'completed')->count(),
                    ],
                    'print_group_url' => route('laboratory.reports.print-group', $categoryId),
                    'registrations' => $registrations
                        ->map(fn (PatientTestRegistration $registration) => [
                            'id' => $registration->id,
                            'ref_no' => $registration->ref_no,
                            'lab_type_name' => $registration->labType?->name,
                            'status' => $registration->status,
                            'priority' => $registration->priority,
                            'doctor_name' => $registration->doctor?->name,
                            'print_url' => route('laboratory.reports.print', $registration->ref_no),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();

        $filters = [];
        foreach (self::GROUPED_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Laboratory/Results/Grouped', [
            'groups' => [
                'data' => $groups,
                ...$this->paginatedInertiaPayload($paginator),
            ],
            'stats' => $stats,
            'filters' => $filters,
            'filterOptions' => [
                'doctors' => Doctor::query()
                    ->where('branch_id', $user->branch_id)
                    ->where('active_status', true)
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
            'urls' => $this->laboratoryNavUrls(),
        ]);
    }

    public function registrationReport(Request $request): Response
    {
        $this->authorize('viewTools', PatientTestRegistration::class);

        $user = $request->user();
        $items = null;
        $hasFilters = $request->hasAny(self::REPORT_FILTER_KEYS);

        if ($hasFilters) {
            $query = $this->scopedRegistrationQuery($user)
                ->with(['labType'])
                ->select(['id', 'lab_type_id']);

            $query = $this->applyReportFilters($query, $request);

            $grouped = $query->get()
                ->groupBy('lab_type_id')
                ->map(function ($group, $labTypeId) {
                    $first = $group->first();

                    return [
                        'lab_type_id' => (int) $labTypeId,
                        'lab_type_name' => $first->labType?->name ?? 'Unknown',
                        'total_count' => $group->count(),
                    ];
                })
                ->values()
                ->sortBy('lab_type_name')
                ->values();

            $perPage = $request->input('per_page', '15');
            if ($perPage === 'all') {
                $items = ['data' => $grouped->all()];
            } else {
                $perPageInt = in_array((int) $perPage, [10, 15, 25, 50, 100], true) ? (int) $perPage : 15;
                $currentPage = (int) $request->input('page', 1);
                $total = $grouped->count();
                $offset = ($currentPage - 1) * $perPageInt;
                $pageItems = $grouped->slice($offset, $perPageInt)->values();

                $paginator = new LengthAwarePaginator(
                    $pageItems,
                    $total,
                    $perPageInt,
                    $currentPage,
                    ['path' => $request->url(), 'pageName' => 'page'],
                );
                $paginator->appends($request->query());

                $items = [
                    'data' => $pageItems->all(),
                    ...$this->paginatedInertiaPayload($paginator),
                ];
            }
        }

        $filters = [];
        foreach (self::REPORT_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Laboratory/Registrations/Report', [
            'items' => $items,
            'filters' => $filters,
            'filterOptions' => [
                'labTypes' => LabType::query()->orderBy('name')->get(['id', 'name']),
            ],
            'urls' => [
                'report' => route('react.laboratory.registrations.report'),
                'export' => route('laboratory.registrations.export-report'),
            ],
        ]);
    }

    public function registrationReportDetailed(Request $request): Response
    {
        $this->authorize('viewTools', PatientTestRegistration::class);

        $user = $request->user();
        $request->mergeIfMissing(['status' => 'completed']);

        $items = null;
        $hasFilters = $request->hasAny(self::REPORT_DETAILED_FILTER_KEYS);

        if ($hasFilters) {
            $query = $this->buildDetailedReportQuery($request, $user)
                ->with([
                    'testable.patient',
                    'labType',
                    'doctor',
                    'branch',
                    'creator',
                    'updater',
                    'completedBy',
                    'assignedTo',
                    'assignedSection.department',
                ])
                ->orderByDesc('registration_date')
                ->orderByDesc('id');

            $perPage = $request->input('per_page', '15');
            if ($perPage === 'all') {
                $items = [
                    'data' => $query->get()
                        ->map(fn (PatientTestRegistration $row) => $this->transformDetailedReportRow($row))
                        ->values()
                        ->all(),
                ];
            } else {
                $perPageInt = in_array((int) $perPage, [10, 15, 25, 50, 100], true) ? (int) $perPage : 15;
                $paginator = $query->paginate($perPageInt)->withQueryString();
                $items = [
                    'data' => collect($paginator->items())
                        ->map(fn (PatientTestRegistration $row) => $this->transformDetailedReportRow($row))
                        ->values()
                        ->all(),
                    ...$this->paginatedInertiaPayload($paginator),
                ];
            }
        }

        $filters = [];
        foreach (self::REPORT_DETAILED_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Laboratory/Registrations/ReportDetailed', [
            'items' => $items,
            'filters' => $filters,
            'filterOptions' => [
                'labTypes' => LabType::query()->orderBy('name')->get(['id', 'name']),
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
                'doctors' => Doctor::query()
                    ->where('active_status', true)
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'users' => User::query()->orderBy('name')->get(['id', 'name']),
                'sections' => Section::query()
                    ->with('department:id,name')
                    ->orderBy('name')
                    ->get(['id', 'name', 'department_id']),
            ],
            'urls' => [
                'report' => route('react.laboratory.registrations.report-detailed'),
                'export' => route('laboratory.registrations.export-report-detailed'),
            ],
        ]);
    }

    public function accept(Request $request, PatientTestRegistration $registration): RedirectResponse
    {
        $this->authorize('accept', $registration);

        if ($registration->assigned_to) {
            return back()->with('error', localize('global.test_already_assigned'));
        }

        if ($registration->status !== 'pending') {
            return back()->with('error', localize('global.only_pending_tests_can_be_accepted'));
        }

        $registration->assignToUser($request->user()->id);

        return back()->with('success', localize('global.test_accepted_successfully'));
    }

    public function markCompleted(PatientTestRegistration $registration): RedirectResponse
    {
        $this->authorize('updateStatus', $registration);

        if ($registration->status !== 'in_progress') {
            return back()->with('error', localize('global.test_registration_marked_in_progress'));
        }

        $registration->markCompleted();

        return back()->with('success', localize('global.test_registration_marked_completed'));
    }

    public function cancel(PatientTestRegistration $registration): RedirectResponse
    {
        $this->authorize('updateStatus', $registration);

        if (! in_array($registration->status, ['pending', 'in_progress'], true)) {
            return back()->with('error', localize('global.test_registration_cancelled'));
        }

        $registration->cancel();

        return back()->with('success', localize('global.test_registration_cancelled'));
    }

    private function renderResultsList(Request $request, string $listMode, string $forcedStatus): Response
    {
        $this->authorize('viewAny', PatientTestRegistration::class);

        $user = $request->user();

        $query = $this->scopedRegistrationQuery($user)
            ->with([
                'testable.patient',
                'labType.category',
                'labType' => fn ($labTypeQuery) => $labTypeQuery->withCount('directLabTestParameters'),
                'doctor',
                'assignedTo',
            ]);

        $query = $this->applyResultsAccessControl($query, $user, $listMode);
        $query = $this->applyResultsFilters($query, $request, $forcedStatus);

        $perPage = min(max((int) $request->input('per_page', 50), 15), 100);
        $paginator = $query->latest()->paginate($perPage)->withQueryString();

        $filters = $this->resultsFiltersFromRequest($request);
        if ($listMode !== 'pending') {
            $filters['status'] = $forcedStatus;
        }

        $pageConfig = match ($listMode) {
            'pending' => [
                'titleKey' => 'global.pending_tests',
                'subtitleKey' => 'global.test_results',
                'icon' => 'bx-hourglass',
                'accent' => 'from-amber-500 to-orange-600',
            ],
            'in_progress' => [
                'titleKey' => 'global.in_progress_tests',
                'subtitleKey' => 'global.test_results',
                'icon' => 'bx-loader-circle',
                'accent' => 'from-cyan-500 to-blue-600',
            ],
            default => [
                'titleKey' => 'global.completed_tests',
                'subtitleKey' => 'global.test_results',
                'icon' => 'bx-check-double',
                'accent' => 'from-emerald-500 to-teal-600',
            ],
        };

        $patientGroups = $this->transformPatientGroups($paginator, $user);

        return Inertia::render('Laboratory/Results/Index', [
            'listMode' => $listMode,
            'page' => $pageConfig,
            'patients' => [
                'data' => $patientGroups,
                ...$this->paginatedInertiaPayload($paginator),
            ],
            'summary' => [
                'patient_count' => count($patientGroups),
                'registration_count' => $paginator->total(),
            ],
            'filters' => $filters,
            'permissions' => [
                'manageResults' => $user->can('manageResults', PatientTestRegistration::class),
            ],
            'urls' => array_merge($this->laboratoryNavUrls(), [
                'index' => match ($listMode) {
                    'pending' => route('react.laboratory.results.pending'),
                    'in_progress' => route('react.laboratory.results.in-progress'),
                    default => route('react.laboratory.results.completed'),
                },
            ]),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<PatientTestRegistration>  $query
     * @return \Illuminate\Database\Eloquent\Builder<PatientTestRegistration>
     */
    private function applyReportFilters($query, Request $request)
    {
        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($testableQuery) use ($request) {
                $testableQuery->whereHas('patient', function ($patientQuery) use ($request) {
                    $patientQuery->where('id', $request->patient_id);
                });
            });
        }

        if ($request->filled('test_type')) {
            $query->where('lab_type_id', $request->test_type);
        }

        return $this->applyDateFilters($query, $request);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<PatientTestRegistration>
     */
    private function buildDetailedReportQuery(Request $request, User $user)
    {
        $query = $this->scopedRegistrationQuery($user);

        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($testableQuery) use ($request) {
                $testableQuery->whereHas('patient', function ($patientQuery) use ($request) {
                    $patientQuery->where('id', $request->patient_id);
                });
            });
        }

        if ($request->filled('test_type')) {
            $query->where('lab_type_id', $request->test_type);
        }

        $query = $this->applyDateFilters($query, $request);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('assignedSection', function ($sectionQuery) use ($request) {
                $sectionQuery->where('department_id', $request->department_id);
            });
        }

        foreach (['created_by', 'updated_by', 'completed_by', 'assigned_to'] as $userField) {
            if ($request->filled($userField)) {
                $query->where($userField, $request->input($userField));
            }
        }

        if ($request->filled('assigned_section_id')) {
            $query->where('assigned_section_id', $request->assigned_section_id);
        }

        if ($request->filled('notes')) {
            $notes = $request->notes;
            $query->where(function ($notesQuery) use ($notes) {
                $notesQuery->where('notes', 'like', '%'.$notes.'%')
                    ->orWhere('detailed_notes', 'like', '%'.$notes.'%');
            });
        }

        $this->applyOptionalDateRange($query, $request, 'completed_at', 'completed_at_from', 'completed_at_to');
        $this->applyOptionalDateRange($query, $request, 'assigned_at', 'assigned_at_from', 'assigned_at_to');

        return $query;
    }

    private function applyOptionalDateRange($query, Request $request, string $column, string $fromKey, string $toKey): void
    {
        if ($request->filled($fromKey) && $request->filled($toKey)) {
            $from = $this->convertPersianDate($request->input($fromKey));
            $to = $this->convertPersianDate($request->input($toKey));
            if ($from !== null && $to !== null) {
                $query->whereDate($column, '>=', $from)->whereDate($column, '<=', $to);
            }
        } elseif ($request->filled($fromKey)) {
            $from = $this->convertPersianDate($request->input($fromKey));
            if ($from !== null) {
                $query->whereDate($column, '>=', $from);
            }
        } elseif ($request->filled($toKey)) {
            $to = $this->convertPersianDate($request->input($toKey));
            if ($to !== null) {
                $query->whereDate($column, '<=', $to);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetailedReportRow(PatientTestRegistration $row): array
    {
        $patient = $row->testable?->patient;

        return [
            'id' => $row->id,
            'ref_no' => $row->ref_no,
            'registration_date' => $row->registration_date
                ? verta($row->registration_date)->format('Y-m-d')
                : null,
            'patient_name' => $patient ? trim("{$patient->name} {$patient->last_name}") : null,
            'lab_type_name' => $row->labType?->name,
            'status' => $row->status,
            'priority' => $row->priority,
            'doctor_name' => $row->doctor?->name,
            'branch_name' => $row->branch?->name,
            'created_by_name' => $row->creator?->name,
            'updated_by_name' => $row->updater?->name,
            'completed_by_name' => $row->completedBy?->name,
            'completed_at' => $row->completed_at
                ? verta($row->completed_at)->format('Y-m-d H:i')
                : null,
            'assigned_to_name' => $row->assignedTo?->name,
            'assigned_at' => $row->assigned_at
                ? verta($row->assigned_at)->format('Y-m-d H:i')
                : null,
            'assigned_section_name' => $row->assignedSection?->name,
            'department_name' => $row->assignedSection?->department?->name,
            'notes' => $row->notes,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function laboratoryNavUrls(): array
    {
        return [
            'pending' => route('react.laboratory.results.pending'),
            'inProgress' => route('react.laboratory.results.in-progress'),
            'completed' => route('react.laboratory.results.completed'),
            'grouped' => route('react.laboratory.results.grouped'),
            'scan' => route('react.laboratory.scan'),
        ];
    }
}
