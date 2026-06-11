<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesOperationListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Jobs\SendNewHospitalizationNotification;
use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Hospitalization;
use App\Models\Nurse;
use App\Models\OperationType;
use App\Models\Room;
use HanifHefaz\Dcter\Dcter;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OperationController extends Controller
{
    use ManagesOperationListing;
    use PaginatesInertiaIndex;

    public function new(Request $request): Response
    {
        return $this->renderListPage($request, 'new', 'Operations/New');
    }

    public function approved(Request $request): Response
    {
        return $this->renderListPage($request, 'approved', 'Operations/Approved');
    }

    public function reserved(Request $request): Response
    {
        return $this->renderListPage($request, 'reserved', 'Operations/Reserved');
    }

    public function completed(Request $request): Response
    {
        return $this->renderListPage($request, 'completed', 'Operations/Completed');
    }

    public function report(Request $request): Response
    {
        $this->authorizeOperationsMenu();

        $items = [];
        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }

        return Inertia::render('Operations/Report', [
            'items' => $items,
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'surgeon_id',
                'operation_status',
                'operation_approval',
                'reserve_status',
                'operation_type_id',
                'date_from',
                'date_to',
            ]),
            'filterOptions' => $this->reportFilterOptions(),
            'urls' => [
                'current' => route('react.operations.report'),
                'export' => route('operations.export-report'),
                ...$this->operationListUrls(),
            ],
        ]);
    }

    public function show(Request $request, Anesthesia $operation): Response
    {
        $this->authorizeOperationsMenu();
        $this->ensureBranch($operation);

        $operation->load([
            'patient:id,name,father_name,id_card,phone',
            'doctor:id,name',
            'operationType:id,name',
            'surgion:id,name',
            'anesthesist:id,name',
            'anesthesia_log:id,name',
            'scrub_nurse:id,first_name,last_name',
            'circulation_nurse:id,first_name,last_name',
            'appointment:id,department_id',
            'appointment.department:id,name',
        ]);

        $user = $request->user();
        $linkedHospitalization = $this->resolveOperationHospitalization($operation);

        return Inertia::render('Operations/Show', [
            'operation' => $this->transformDetail($operation),
            'hospitalization' => $linkedHospitalization
                ? $this->transformOperationHospitalization($linkedHospitalization)
                : null,
            'nurses' => $this->nurseOptions(),
            'permissions' => [
                'prescription' => $user->can('show-prescriptions-menu') && (bool) $operation->appointment_id,
                'hospitalize' => (bool) $operation->appointment_id
                    && $user->can('show-hospitalizations-menu')
                    && $user->can('patient-hospitalization'),
            ],
            'urls' => [
                'update' => route('react.operations.update', $operation),
                'complete' => route('react.operations.complete', $operation),
                'reserve' => route('react.operations.reserve', $operation),
                'unreserve' => route('react.operations.unreserve', $operation),
                'back' => $this->backUrlForOperation($operation),
                'appointment' => $operation->appointment_id
                    ? route('react.appointments.show', $operation->appointment_id)
                    : null,
                'hospitalizationMeta' => $operation->appointment_id
                    ? route('react.appointments.sections.hospitalization.meta', $operation->appointment_id)
                    : null,
                ...$this->operationListUrls(),
            ],
        ]);
    }

    public function update(Request $request, Anesthesia $operation): RedirectResponse
    {
        $this->authorizeOperationsMenu();
        $this->ensureBranch($operation);

        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'is_operation_approved' => 'nullable',
            'operation_remark' => 'nullable|string',
            'operation_result' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable|exists:nurses,id',
            'operation_circulation_nurse_id' => 'nullable|exists:nurses,id',
            'date' => 'nullable|string',
            'time' => 'nullable|string',
            'operation_expense_remarks' => 'nullable|string',
            'patient_status' => 'nullable|in:discharge,death',
        ]);

        if (! empty($data['date'])) {
            $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));
        }

        foreach (['operation_scrub_nurse_id', 'operation_circulation_nurse_id'] as $key) {
            if (empty($data[$key])) {
                $data[$key] = null;
            }
        }

        $data['room_id'] = $request->input('room_id');
        $data['bed_id'] = $request->input('bed_id');

        $existingDate = $operation->date;

        if (! empty($data['date']) && $data['date'] > $existingDate) {
            $operation->reserve();
            $operation->update($data);

            return redirect()
                ->route('react.operations.reserved')
                ->with('success', localize('global.operation_reserved_successfully.'));
        }

        $operation->update($data);

        return redirect()
            ->back()
            ->with('success', localize('global.operation_updated_successfully.'));
    }

    public function complete(Request $request, Anesthesia $operation): RedirectResponse
    {
        $this->authorizeOperationsMenu();
        $this->ensureBranch($operation);

        $data = $request->validate([
            'operation_remark' => 'nullable|string',
            'operation_result' => 'required|in:0,1',
            'hospitalize' => 'nullable|boolean',
            'reason' => 'required_if:hospitalize,1|string',
            'remarks' => 'required_if:hospitalize,1|string',
            'department_id' => 'required_if:hospitalize,1|exists:departments,id',
            'room_id' => 'required_if:hospitalize,1|exists:rooms,id',
            'bed_id' => 'required_if:hospitalize,1|exists:beds,id',
        ]);

        DB::transaction(function () use ($request, $operation, $data) {
            $operationData = [
                'operation_remark' => $data['operation_remark'] ?? null,
                'operation_result' => $data['operation_result'],
                'is_operation_done' => 1,
                'room_id' => $operation->room_id,
                'bed_id' => $operation->bed_id,
            ];

            if ($operation->bed_id) {
                Bed::query()
                    ->whereKey($operation->bed_id)
                    ->update(['is_occupied' => false]);
            }

            $operation->update($operationData);

            if ($request->boolean('hospitalize')) {
                $this->syncOperationHospitalization($operation, $data, $request->user());
            }
        });

        return redirect()
            ->back()
            ->with('success', localize('global.operation_completed_successfully.'));
    }

    public function reserve(Request $request, Anesthesia $operation): RedirectResponse
    {
        $this->authorizeOperationsMenu();
        $this->ensureBranch($operation);

        $data = $request->validate([
            'reserve_reason' => 'required|string',
        ]);

        $operation->reserve();
        $operation->update($data);

        return redirect()
            ->route('react.operations.reserved')
            ->with('success', localize('global.operation_reserved_successfully.'));
    }

    public function unreserve(Anesthesia $operation): RedirectResponse
    {
        $this->authorizeOperationsMenu();
        $this->ensureBranch($operation);

        $operation->unreserve();
        $operation->update(['is_operation_approved' => 0]);

        return redirect()
            ->back()
            ->with('success', localize('global.operation_unreserved_successfully.'));
    }

    private function renderListPage(Request $request, string $variant, string $page): Response
    {
        $this->authorizeOperationsMenu();

        $query = $this->operationListQuery($variant)
            ->with($this->operationEagerLoads($variant));

        $this->applyOperationListFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $from = $paginator->firstItem();

        $items = [
            'data' => collect($paginator->items())
                ->map(function (Anesthesia $operation, int $index) use ($variant, $from) {
                    return $this->transformOperationListItem(
                        $operation,
                        $variant,
                        $from ? $from + $index : null,
                    );
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

        return Inertia::render($page, [
            'operations' => $items,
            'filters' => $this->collectFilters($request, $this->operationListFilterKeys()),
            'filterOptions' => $this->listFilterOptions(),
            'urls' => [
                'current' => $request->url(),
                ...$this->operationListUrls(),
            ],
        ]);
    }

    /**
     * @return array{branches: list<array{id: int, name: string}>, departments: list<array{id: int, name: string}>, operationTypes: list<array{id: int, name: string}>, surgeons: list<array{id: int, name: string}>}
     */
    private function listFilterOptions(): array
    {
        $branchId = $this->operationBranchId();

        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name'])->all(),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name'])->all(),
            'operationTypes' => OperationType::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->all(),
            'surgeons' => Doctor::query()
                ->where('active_status', true)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->all(),
        ];
    }

    /**
     * @return array{operationTypes: list<array{id: int, name: string}>, surgeons: list<array{id: int, name: string}>}
     */
    private function reportFilterOptions(): array
    {
        $branchId = $this->operationBranchId();

        return [
            'operationTypes' => OperationType::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->all(),
            'surgeons' => Doctor::query()
                ->where('active_status', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->all(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $query = DB::table('anesthesias as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as u', 'a.operation_surgion_id', '=', 'u.id')
            ->leftJoin('operation_types as ot', 'a.operation_type_id', '=', 'ot.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'u.name as surgion_name',
                'ot.name as operation_type_name',
                'a.date',
                'a.time',
                'a.is_operation_done',
                'a.is_operation_approved',
                'a.is_reserved',
            )
            ->when($this->operationBranchId(), fn ($q, $branchId) => $q->where('a.branch_id', $branchId))
            ->where('a.status', 'approved')
            ->where('a.is_referred_to_operation', true)
            ->orderByDesc('a.date')
            ->orderByDesc('a.time');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('surgeon_id')) {
            $query->where('a.operation_surgion_id', $request->surgeon_id);
        }

        if ($request->filled('operation_status')) {
            $query->where('a.is_operation_done', $request->operation_status);
        }

        if ($request->filled('operation_approval')) {
            $query->where('a.is_operation_approved', $request->operation_approval);
        }

        if ($request->filled('reserve_status')) {
            $query->where('a.is_reserved', $request->reserve_status);
        }

        if ($request->filled('operation_type_id')) {
            $query->where('a.operation_type_id', $request->operation_type_id);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            try {
                $fromDate = Verta::parse($request->date_from)->datetime()->format('Y-m-d');
                $toDate = Verta::parse($request->date_to)->datetime()->format('Y-m-d');
                $query->whereDate('a.date', '>=', $fromDate)->whereDate('a.date', '<=', $toDate);
            } catch (\Throwable) {
            }
        }

        return $query->limit(200)->get()->map(fn ($item) => [
            'id' => $item->id,
            'patient_name' => $item->patient_name,
            'surgion_name' => $item->surgion_name,
            'operation_type_name' => $item->operation_type_name,
            'date' => $item->date ? $this->formatOperationDate($item->date) : null,
            'time' => $item->time,
            'is_operation_done' => (bool) $item->is_operation_done,
            'is_operation_approved' => (bool) $item->is_operation_approved,
            'is_reserved' => (bool) $item->is_reserved,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(Anesthesia $operation): array
    {
        $assistantIds = json_decode($operation->operation_assistants_id ?? '[]', true) ?: [];
        $assistantNames = $assistantIds
            ? Doctor::query()->whereIn('id', $assistantIds)->orderBy('name')->pluck('name')->all()
            : [];

        return [
            'id' => $operation->id,
            'status' => $operation->status,
            'plan' => $operation->plan,
            'anesthesia_plan' => $operation->anesthesia_plan,
            'anesthesia_log_reply' => $operation->anesthesia_log_reply,
            'position_on_bed' => $operation->position_on_bed,
            'planned_duration' => $operation->planned_duration,
            'estimated_blood_waste' => $operation->estimated_blood_waste,
            'other_problems' => $operation->other_problems,
            'anesthesia_type' => $operation->anesthesia_type,
            'operation_remark' => $operation->operation_remark,
            'operation_expense_remarks' => $operation->operation_expense_remarks,
            'reserve_reason' => $operation->reserve_reason,
            'patient_status' => $operation->patient_status,
            'operation_result' => $operation->operation_result,
            'date' => $operation->date ? verta($operation->date)->format('Y-m-d') : '',
            'date_display' => $this->formatOperationDate($operation->date),
            'time' => $operation->time,
            'appointment_id' => $operation->appointment_id,
            'is_operation_approved' => (bool) $operation->is_operation_approved,
            'is_operation_done' => (bool) $operation->is_operation_done,
            'is_reserved' => (bool) $operation->is_reserved,
            'is_referred_to_operation' => (bool) $operation->is_referred_to_operation,
            'operation_scrub_nurse_id' => $operation->operation_scrub_nurse_id,
            'operation_circulation_nurse_id' => $operation->operation_circulation_nurse_id,
            'patient' => $operation->patient ? [
                'id' => $operation->patient->id,
                'name' => $operation->patient->name,
                'father_name' => $operation->patient->father_name,
                'id_card' => $operation->patient->id_card,
                'phone' => $operation->patient->phone,
            ] : null,
            'operation_type_name' => $operation->operationType?->name,
            'doctor_name' => $operation->doctor?->name,
            'surgion_name' => $operation->surgion?->name,
            'anesthesist_name' => $operation->anesthesist?->name,
            'anesthesia_log_name' => $operation->anesthesia_log?->name,
            'scrub_nurse_name' => $operation->scrub_nurse?->full_name,
            'circulation_nurse_name' => $operation->circulation_nurse?->full_name,
            'department_name' => $operation->appointment?->department?->name,
            'operation_assistants_names' => $assistantNames,
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function nurseOptions(): array
    {
        return Nurse::query()
            ->where('employment_status', 'active')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name'])
            ->map(fn (Nurse $nurse) => [
                'id' => $nurse->id,
                'name' => $nurse->full_name,
            ])
            ->values()
            ->all();
    }

    private function ensureBranch(Anesthesia $operation): void
    {
        $branchId = $this->operationBranchId();

        if ($branchId) {
            abort_unless((int) $operation->branch_id === $branchId, 404);
        }
    }

    private function resolveOperationHospitalization(Anesthesia $operation): ?Hospitalization
    {
        if ($operation->hospitalization_id) {
            $linked = Hospitalization::query()->find($operation->hospitalization_id);
            if ($linked) {
                return $linked;
            }
        }

        if (! $operation->appointment_id) {
            return null;
        }

        return Hospitalization::query()
            ->where('appointment_id', $operation->appointment_id)
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformOperationHospitalization(Hospitalization $hospitalization): array
    {
        return [
            'id' => $hospitalization->id,
            'reason' => $hospitalization->reason ?? '',
            'remarks' => $hospitalization->remarks ?? '',
            'department_id' => (string) ($hospitalization->department_id ?? ''),
            'room_id' => (string) ($hospitalization->room_id ?? ''),
            'bed_id' => (string) ($hospitalization->bed_id ?? ''),
            'is_active' => ! (bool) $hospitalization->is_discharged,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function syncOperationHospitalization(Anesthesia $operation, array $data, $user): void
    {
        abort_unless($operation->appointment_id, 422);

        $room = Room::query()->findOrFail($data['room_id']);
        abort_unless(
            $room->department_id === null || (int) $room->department_id === (int) $data['department_id'],
            422
        );

        $bed = Bed::query()->findOrFail($data['bed_id']);
        abort_unless((int) $bed->room_id === (int) $data['room_id'], 422);

        $existing = $this->resolveOperationHospitalization($operation);

        if ($existing) {
            if ((int) $bed->id !== (int) $existing->bed_id) {
                abort_if((bool) $bed->is_occupied, 422);
                $this->releaseHospitalizationBed($existing->bed_id);
                $bed->update(['is_occupied' => true]);
            }

            $existing->update([
                'reason' => $data['reason'],
                'remarks' => $data['remarks'],
                'room_id' => $data['room_id'],
                'bed_id' => $data['bed_id'],
                'department_id' => $data['department_id'],
                'is_discharged' => 0,
            ]);

            $operation->update(['hospitalization_id' => $existing->id]);

            return;
        }

        abort_if((bool) $bed->is_occupied, 422);
        $bed->update(['is_occupied' => true]);

        $hospitalization = Hospitalization::create([
            'reason' => $data['reason'],
            'remarks' => $data['remarks'],
            'room_id' => $data['room_id'],
            'bed_id' => $data['bed_id'],
            'patient_id' => $operation->patient_id,
            'appointment_id' => $operation->appointment_id,
            'branch_id' => $operation->branch_id ?? $user->branch_id,
            'department_id' => $data['department_id'],
            'is_discharged' => 0,
            'food_type_id' => json_encode([]),
        ]);

        $operation->update(['hospitalization_id' => $hospitalization->id]);

        SendNewHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);
    }

    private function releaseHospitalizationBed(?int $bedId): void
    {
        if (! $bedId) {
            return;
        }

        Bed::query()->whereKey($bedId)->update(['is_occupied' => false]);
    }
}
