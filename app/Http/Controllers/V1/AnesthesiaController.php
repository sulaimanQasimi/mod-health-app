<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesAnesthesiaListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Jobs\SendNewOperationNotification;
use App\Models\Anesthesia;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\OperationType;
use HanifHefaz\Dcter\Dcter;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnesthesiaController extends Controller
{
    use ManagesAnesthesiaListing;
    use PaginatesInertiaIndex;

    public function new(Request $request): Response
    {
        return $this->renderListPage($request, 'new', 'Anesthesias/New');
    }

    public function approved(Request $request): Response
    {
        return $this->renderListPage($request, 'approved', 'Anesthesias/Approved');
    }

    public function rejected(Request $request): Response
    {
        return $this->renderListPage($request, 'rejected', 'Anesthesias/Rejected');
    }

    public function report(Request $request): Response
    {
        $this->authorizeAnesthesiaMenu();

        $branchId = $this->anesthesiaBranchId();
        $items = [];

        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }

        return Inertia::render('Anesthesias/Report', [
            'items' => $items,
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'status',
                'doctor_id',
                'anesthesia_type',
                'operation_type_id',
                'department_id',
                'time',
                'from',
                'to',
            ]),
            'filterOptions' => $this->reportFilterOptions($branchId),
            'urls' => [
                'current' => route('react.anesthesias.report'),
                'export' => route('anesthesias.export-report'),
                ...$this->anesthesiaListUrls(),
            ],
        ]);
    }

    public function show(Request $request, Anesthesia $anesthesia): Response
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);

        $anesthesia->load([
            'patient:id,name,father_name,id_card,phone',
            'doctor:id,name',
            'operationType:id,name',
            'surgion:id,name',
            'anesthesist:id,name',
            'anesthesia_log:id,name',
            'scrub_nurse:id,first_name,last_name',
            'circulation_nurse:id,first_name,last_name',
            'room:id,name',
            'bed:id,number',
            'appointment:id,department_id,is_completed',
            'appointment.department:id,name',
        ]);

        $user = $request->user();

        return Inertia::render('Anesthesias/Show', [
            'anesthesia' => $this->transformDetail($anesthesia),
            'hospitalDoctors' => $this->hospitalDoctorOptions(),
            'permissions' => [
                'edit' => $user->can('edit-anesthesias'),
                'delete' => $user->can('delete-anesthesias'),
                'approve' => $anesthesia->status === 'new' && $user->can('edit-anesthesias'),
                'reject' => $anesthesia->status === 'new' && $user->can('edit-anesthesias'),
                'referToOperation' => $anesthesia->status === 'approved'
                    && ! $anesthesia->is_referred_to_operation
                    && $user->can('edit-anesthesias'),
            ],
            'sectionPermissions' => [
                'prescription' => $user->can('show-prescriptions-menu') && (bool) $anesthesia->appointment_id,
            ],
            'urls' => [
                'update' => route('react.anesthesias.update', $anesthesia),
                'referToOperation' => route('react.anesthesias.refer-to-operation', $anesthesia),
                'destroy' => route('react.anesthesias.destroy', $anesthesia),
                'edit' => route('react.anesthesias.edit', $anesthesia),
                'back' => $this->backUrlForAnesthesiaStatus($anesthesia->status),
                'appointment' => $anesthesia->appointment_id
                    ? route('react.appointments.show', $anesthesia->appointment_id)
                    : null,
                ...$this->anesthesiaListUrls(),
            ],
        ]);
    }

    public function edit(Anesthesia $anesthesia): Response
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);
        abort_unless(request()->user()->can('edit-anesthesias'), 403);

        $anesthesia->load(['appointment:id,patient_id,doctor_id', 'patient:id,name']);

        $branchId = $this->anesthesiaBranchId();
        $assistants = json_decode($anesthesia->operation_assistants_id ?? '[]', true) ?: [];

        return Inertia::render('Anesthesias/Edit', [
            'anesthesia' => [
                'id' => $anesthesia->id,
                'plan' => $anesthesia->plan ?? '',
                'other_problems' => $anesthesia->other_problems ?? '',
                'anesthesia_plan' => $anesthesia->anesthesia_plan ?? '',
                'position_on_bed' => $anesthesia->position_on_bed ?? '',
                'planned_duration' => $anesthesia->planned_duration ?? '',
                'estimated_blood_waste' => $anesthesia->estimated_blood_waste ?? '',
                'date' => $anesthesia->date ? verta($anesthesia->date)->format('Y-m-d') : '',
                'time' => $anesthesia->time ?? '',
                'operation_type_id' => (string) ($anesthesia->operation_type_id ?? ''),
                'anesthesia_type' => $anesthesia->anesthesia_type ?? '',
                'operation_surgion_id' => (string) ($anesthesia->operation_surgion_id ?? ''),
                'operation_assistants_id' => array_map('strval', $assistants),
                'operation_anesthesia_log_id' => (string) ($anesthesia->operation_anesthesia_log_id ?? ''),
                'operation_anesthesist_id' => (string) ($anesthesia->operation_anesthesist_id ?? ''),
                'operation_scrub_nurse_id' => (string) ($anesthesia->operation_scrub_nurse_id ?? ''),
                'operation_circulation_nurse_id' => (string) ($anesthesia->operation_circulation_nurse_id ?? ''),
                'patient_id' => (int) $anesthesia->patient_id,
                'appointment_id' => (int) $anesthesia->appointment_id,
                'doctor_id' => (int) ($anesthesia->doctor_id ?? $anesthesia->appointment?->doctor_id ?? request()->user()->id),
                'branch_id' => (int) $anesthesia->branch_id,
                'patient_name' => $anesthesia->patient?->name,
            ],
            'operationTypes' => OperationType::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'hospitalDoctors' => $this->hospitalDoctorOptions(),
            'nurses' => $this->nurseOptions($branchId),
            'urls' => [
                'update' => route('react.anesthesias.update-details', $anesthesia),
                'show' => route('react.anesthesias.show', $anesthesia),
                'back' => route('react.anesthesias.show', $anesthesia),
            ],
        ]);
    }

    public function update(Request $request, Anesthesia $anesthesia): RedirectResponse
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);
        abort_unless($request->user()->can('edit-anesthesias'), 403);

        $data = $request->validate([
            'anesthesia_log_reply' => 'required',
            'status' => 'nullable|in:new,approved,rejected',
            'anesthesia_type' => 'nullable|in:local,spinal,general',
            'anesthesia_plan' => 'nullable',
            'operation_anesthesia_log_id' => 'nullable|exists:doctors,id',
            'operation_anesthesist_id' => 'nullable|exists:doctors,id',
        ]);

        $anesthesia->update($data);

        if (($data['status'] ?? null) === 'approved') {
            return redirect()
                ->route('react.anesthesias.show', $anesthesia)
                ->with('success', localize('global.anesthesia_updated_successfully.'));
        }

        $redirect = $this->backUrlForAnesthesiaStatus($anesthesia->fresh()->status);

        return redirect()
            ->to($redirect)
            ->with('success', localize('global.anesthesia_updated_successfully.'));
    }

    public function referToOperation(Request $request, Anesthesia $anesthesia): RedirectResponse
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);
        abort_unless($request->user()->can('edit-anesthesias'), 403);
        abort_unless($anesthesia->status === 'approved', 422);
        abort_if($anesthesia->is_referred_to_operation, 422);

        $anesthesia->update(['is_referred_to_operation' => true]);

        SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);

        return redirect()
            ->route('react.anesthesias.show', $anesthesia)
            ->with('success', localize('global.anesthesia_referred_to_operation_successfully.'));
    }

    public function updateDetails(Request $request, Anesthesia $anesthesia): RedirectResponse
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);
        abort_unless($request->user()->can('edit-anesthesias'), 403);

        $data = $request->validate([
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'required',
            'operation_type_id' => 'required',
            'hospitalization_id' => 'nullable',
            'date' => 'required',
            'time' => 'required',
            'plan' => 'required',
            'position_on_bed' => 'required',
            'planned_duration' => 'required',
            'estimated_blood_waste' => 'required',
            'other_problems' => 'required',
            'anesthesia_type' => 'nullable|in:local,spinal,general',
            'operation_assistants_id' => 'nullable|array',
            'operation_surgion_id' => 'nullable|exists:doctors,id',
            'operation_anesthesia_log_id' => 'nullable|exists:doctors,id',
            'operation_anesthesist_id' => 'nullable|exists:doctors,id',
            'operation_scrub_nurse_id' => 'nullable|exists:nurses,id',
            'operation_circulation_nurse_id' => 'nullable|exists:nurses,id',
            'anesthesia_plan' => 'nullable',
        ]);

        $data['operation_assistants_id'] = json_encode($data['operation_assistants_id'] ?? []);
        $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));

        $anesthesia->update($data);

        return redirect()
            ->route('react.anesthesias.show', $anesthesia)
            ->with('success', localize('global.anesthesia_updated_successfully.'));
    }

    public function destroy(Anesthesia $anesthesia): RedirectResponse
    {
        $this->authorizeAnesthesiaMenu();
        $this->ensureBranch($anesthesia);
        abort_unless(request()->user()->can('delete-anesthesias'), 403);

        $status = $anesthesia->status;
        $anesthesia->delete();

        return redirect()
            ->to($this->backUrlForAnesthesiaStatus($status))
            ->with('success', localize('global.anesthesia_deleted_successfully.'));
    }

    private function renderListPage(Request $request, string $status, string $page): Response
    {
        $this->authorizeAnesthesiaMenu();

        $query = Anesthesia::query()
            ->where('status', $status)
            ->when($this->anesthesiaBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->with([
                'patient:id,name,father_name,id_card',
                'operationType:id,name',
                'surgion:id,name',
            ])
            ->orderByDesc('created_at');

        $this->applyAnesthesiaListFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedAnesthesiaItems($paginator);
        $branchId = $this->anesthesiaBranchId();

        return Inertia::render($page, [
            ...$this->listPagePayload($request, $items),
            'filterOptions' => [
                'operationTypes' => OperationType::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    /**
     * @param  array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}  $items
     * @return array<string, mixed>
     */
    private function listPagePayload(Request $request, array $items): array
    {
        return [
            'anesthesias' => $items,
            'filters' => $this->collectFilters($request, $this->anesthesiaListFilterKeys()),
            'urls' => [
                'current' => $request->url(),
                ...$this->anesthesiaListUrls(),
            ],
        ];
    }

    /**
     * @return array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}
     */
    private function paginatedAnesthesiaItems(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        $from = $paginator->firstItem();

        return [
            'data' => collect($paginator->items())
                ->map(function (Anesthesia $anesthesia, int $index) use ($from) {
                    return $this->transformAnesthesiaListItem($anesthesia, $from ? $from + $index : null);
                })
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

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $query = DB::table('anesthesias as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('appointments as app', 'a.appointment_id', '=', 'app.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'd.name as doctor_name',
                'b.name as branch_name',
                'a.status',
                'a.anesthesia_type',
                'a.date',
                'a.time',
            )
            ->when($this->anesthesiaBranchId(), fn ($q, $branchId) => $q->where('a.branch_id', $branchId))
            ->orderByDesc('a.date')
            ->orderByDesc('a.time');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('status')) {
            $query->where('a.status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('a.doctor_id', $request->doctor_id);
        }

        if ($request->filled('anesthesia_type')) {
            $query->where('a.anesthesia_type', $request->anesthesia_type);
        }

        if ($request->filled('operation_type_id')) {
            $query->where('a.operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('department_id')) {
            $query->where('app.department_id', $request->department_id);
        }

        if ($request->filled('time')) {
            $query->where('a.time', $request->time);
        }

        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = Verta::parse($request->from)->datetime()->format('Y-m-d');
                $toDate = Verta::parse($request->to)->datetime()->format('Y-m-d');
                $query->whereDate('a.date', '>=', $fromDate)->whereDate('a.date', '<=', $toDate);
            } catch (\Throwable) {
            }
        }

        return $query->limit(200)->get()->map(fn ($item) => [
            'id' => $item->id,
            'patient_name' => $item->patient_name,
            'doctor_name' => $item->doctor_name,
            'branch_name' => $item->branch_name,
            'status' => $item->status,
            'anesthesia_type' => $item->anesthesia_type,
            'date' => $item->date ? $this->formatAnesthesiaDate($item->date) : null,
            'time' => $item->time,
        ])->values()->all();
    }

    /**
     * @return array{doctors: list<array{id: int, name: string}>, operationTypes: list<array{id: int, name: string}>, departments: list<array{id: int, name: string}>}
     */
    private function reportFilterOptions(?int $branchId): array
    {
        return [
            'doctors' => Doctor::query()->orderBy('name')->get(['id', 'name'])->all(),
            'operationTypes' => OperationType::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->all(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name'])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(Anesthesia $anesthesia): array
    {
        $assistantIds = json_decode($anesthesia->operation_assistants_id ?? '[]', true) ?: [];
        $assistantNames = $assistantIds
            ? Doctor::query()->whereIn('id', $assistantIds)->orderBy('name')->pluck('name')->all()
            : [];

        return [
            'id' => $anesthesia->id,
            'status' => $anesthesia->status,
            'plan' => $anesthesia->plan,
            'anesthesia_plan' => $anesthesia->anesthesia_plan,
            'anesthesia_log_reply' => $anesthesia->anesthesia_log_reply,
            'position_on_bed' => $anesthesia->position_on_bed,
            'planned_duration' => $anesthesia->planned_duration,
            'estimated_blood_waste' => $anesthesia->estimated_blood_waste,
            'other_problems' => $anesthesia->other_problems,
            'anesthesia_type' => $anesthesia->anesthesia_type,
            'date' => $this->formatAnesthesiaDate($anesthesia->date),
            'time' => $anesthesia->time,
            'appointment_id' => $anesthesia->appointment_id,
            'patient' => $anesthesia->patient ? [
                'id' => $anesthesia->patient->id,
                'name' => $anesthesia->patient->name,
                'father_name' => $anesthesia->patient->father_name,
                'id_card' => $anesthesia->patient->id_card,
                'phone' => $anesthesia->patient->phone,
            ] : null,
            'operation_type_name' => $anesthesia->operationType?->name,
            'doctor_name' => $anesthesia->doctor?->name,
            'surgion_name' => $anesthesia->surgion?->name,
            'anesthesist_name' => $anesthesia->anesthesist?->name,
            'anesthesia_log_name' => $anesthesia->anesthesia_log?->name,
            'scrub_nurse_name' => $anesthesia->scrub_nurse
                ? trim($anesthesia->scrub_nurse->first_name.' '.$anesthesia->scrub_nurse->last_name)
                : null,
            'circulation_nurse_name' => $anesthesia->circulation_nurse
                ? trim($anesthesia->circulation_nurse->first_name.' '.$anesthesia->circulation_nurse->last_name)
                : null,
            'department_name' => $anesthesia->appointment?->department?->name,
            'room_name' => $anesthesia->room?->name,
            'bed_number' => $anesthesia->bed?->number,
            'operation_anesthesia_log_id' => $anesthesia->operation_anesthesia_log_id,
            'operation_anesthesist_id' => $anesthesia->operation_anesthesist_id,
            'operation_assistants_names' => $assistantNames,
            'is_referred_to_operation' => (bool) $anesthesia->is_referred_to_operation,
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function hospitalDoctorOptions(): array
    {
        return Doctor::query()
            ->where('clinic_type', 'hospital')
            ->where('active_status', true)
            ->when($this->anesthesiaBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Doctor $doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function nurseOptions(?int $branchId): array
    {
        return Nurse::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Nurse $nurse) => [
                'id' => $nurse->id,
                'name' => trim($nurse->first_name.' '.$nurse->last_name),
            ])
            ->values()
            ->all();
    }

    private function ensureBranch(Anesthesia $anesthesia): void
    {
        $branchId = $this->anesthesiaBranchId();

        if ($branchId) {
            abort_unless((int) $anesthesia->branch_id === $branchId, 404);
        }
    }
}
