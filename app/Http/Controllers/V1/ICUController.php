<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesIcuListing;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\Room;
use App\Services\IcuReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ICUController extends Controller
{
    use ManagesIcuListing;
    use PaginatesInertiaIndex;

    public function __construct(
        private readonly IcuReferralService $icuReferralService,
    ) {}

    public function new(Request $request): Response
    {
        $this->authorizeIcuMenu();

        $query = ICU::query()
            ->where('status', 'new')
            ->when($this->icuBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->with([
                'patient:id,name,father_name,id_card',
                'placementHospitalization.room:id,name',
                'placementHospitalization.bed:id,number',
            ])
            ->orderByDesc('created_at');

        $this->applyIcuPatientFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedIcuItems($paginator);

        return Inertia::render('Icus/New', $this->listPagePayload($request, $items, false));
    }

    public function approved(Request $request): Response
    {
        $this->authorizeIcuMenu();

        $query = ICU::query()
            ->where('status', 'approved')
            ->with([
                'patient:id,name,father_name,id_card',
                'placementHospitalization.room:id,name',
                'placementHospitalization.bed:id,number',
            ])
            ->orderByDesc('created_at');

        $this->applyIcuDischargeFilter($query, $request);
        $this->applyIcuPatientFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedIcuItems($paginator);

        return Inertia::render('Icus/Approved', $this->listPagePayload($request, $items, true));
    }

    public function rejected(Request $request): Response
    {
        $this->authorizeIcuMenu();

        $query = ICU::query()
            ->where('status', 'rejected')
            ->when($this->icuBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->with([
                'patient:id,name,father_name,id_card',
                'placementHospitalization.room:id,name',
                'placementHospitalization.bed:id,number',
            ])
            ->orderByDesc('created_at');

        $this->applyIcuPatientFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $items = $this->paginatedIcuItems($paginator);

        return Inertia::render('Icus/Rejected', $this->listPagePayload($request, $items, false));
    }

    public function report(Request $request): Response
    {
        $this->authorizeIcuMenu();

        $items = [];
        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }

        return Inertia::render('Icus/Report', [
            'items' => $items,
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'status',
                'date_from',
                'date_to',
            ]),
            'urls' => [
                'current' => route('react.icus.report'),
                'export' => route('icus.export-report'),
                ...$this->icuListUrls(),
            ],
        ]);
    }

    public function show(Request $request, ICU $icu): Response
    {
        $this->authorizeIcuMenu();

        $icu->load([
            'patient:id,name,father_name,id_card,phone,last_name',
            'doctor:id,name',
            'branch:id,name',
            'appointment:id,patient_id,doctor_id,department_id,is_completed',
            'appointment.department:id,name',
            'appointment.doctor:id,name',
        ]);

        $placement = \App\Services\IcuReferralService::placementHospitalization($icu);
        if ($placement) {
            $placement->loadMissing(['room:id,name', 'bed:id,number']);
        }

        $user = $request->user();

        return Inertia::render('Icus/Show', [
            'icu' => $this->transformDetail($icu, $placement),
            'permissions' => [
                'edit' => $user->can('edit-icus'),
                'delete' => $user->can('delete-icus'),
                'approve' => $icu->status === 'new' && $user->can('edit-icus'),
                'reject' => $icu->status === 'new' && $user->can('edit-icus'),
                'discharge' => $icu->status === 'approved'
                    && ! (bool) $icu->is_discharged
                    && $user->can('edit-icus'),
            ],
            'sectionPermissions' => [
                'prescription' => $user->can('show-prescriptions-menu') && (bool) $icu->appointment_id,
                'lab' => $user->can('show-labs-menu') && (bool) $icu->appointment_id,
                'blood' => $user->can('show-blood-request-menu') && (bool) $icu->appointment_id,
                'visits' => $user->can('show-icu-menu') && $icu->status === 'approved',
                'procedures' => $user->can('show-icu-menu') && $icu->status === 'approved',
                'daily_progress' => $user->can('show-icu-menu') && $icu->status === 'approved',
            ],
            'urls' => [
                'update' => route('react.icus.update', $icu),
                'destroy' => route('react.icus.destroy', $icu),
                'back' => $this->backUrlForStatus($icu->status),
                'appointment' => $icu->appointment_id
                    ? route('react.appointments.show', $icu->appointment_id)
                    : null,
                'print_death_card' => route('icus.print-death-card', $icu),
                'print_move_card' => route('icus.print-move-card', $icu),
                'discharge_meta' => route('react.icus.discharge.meta', $icu),
                ...$this->icuListUrls(),
            ],
        ]);
    }

    public function dischargeMeta(ICU $icu): JsonResponse
    {
        $this->authorizeIcuMenu();
        abort_unless(request()->user()->can('edit-icus'), 403);
        abort_unless($icu->status === 'approved' && ! (bool) $icu->is_discharged, 403);

        $icu->loadMissing('appointment:id,department_id');
        $branchId = $icu->branch_id ?? request()->user()->branch_id;
        $placement = IcuReferralService::placementHospitalization($icu);
        if ($placement) {
            $placement->loadMissing(['room:id,name', 'bed:id,number']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'current_room_name' => $placement?->room?->name,
                'current_bed_number' => $placement?->bed?->number,
                'default_department_id' => $icu->appointment?->department_id,
                'departments' => $this->dischargeDepartments($branchId),
                'rooms' => $this->dischargeRooms($branchId),
                'beds' => $this->dischargeBeds($branchId),
            ],
        ]);
    }

    public function update(Request $request, ICU $icu): RedirectResponse
    {
        $this->authorizeIcuMenu();
        abort_unless($request->user()->can('edit-icus'), 403);

        $data = $request->validate([
            'icu_enterance_note' => 'nullable',
            'status' => 'nullable|in:new,approved,rejected',
            'icu_reject_reason' => 'nullable',
            'discharge_status' => 'nullable',
            'discharge_remark' => 'nullable',
            'discharged_at' => 'nullable',
            'cause_of_death' => 'nullable',
            'death_date' => 'nullable',
            'death_time' => 'nullable',
            'move_department_id' => 'nullable|exists:departments,id',
            'is_discharged' => 'nullable',
            'transfer_date' => 'nullable',
            'brief_history' => 'nullable',
            'transfer_room_id' => 'nullable|exists:rooms,id',
            'transfer_bed_id' => 'nullable|exists:beds,id',
            'recovered_room_id' => 'nullable|exists:rooms,id',
            'recovered_bed_id' => 'nullable|exists:beds,id',
            'description' => 'nullable',
        ]);

        if ($request->filled('discharge_status')) {
            $data['is_discharged'] = 1;
            $data['discharged_at'] = now();
            if ($request->discharge_status === 'moved' && empty($data['transfer_date'])) {
                $data['transfer_date'] = now()->toDateString();
            }
        }

        $icu->update($data);

        if ($request->filled('discharge_status')
            && in_array($request->discharge_status, ['recovered', 'died', 'moved'], true)) {
            $this->icuReferralService->applyDischarge($icu->fresh(), $data);
        }

        return redirect()->back()->with('success', localize('global.icu_updated_successfully.'));
    }

    public function destroy(ICU $icu): RedirectResponse
    {
        $this->authorizeIcuMenu();
        abort_unless(request()->user()->can('delete-icus'), 403);

        $icu->delete();

        return redirect()
            ->route('react.icus.new')
            ->with('success', localize('global.icu_deleted_successfully.'));
    }

    /**
     * @return array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}
     */
    private function paginatedIcuItems(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        $from = $paginator->firstItem();

        return [
            'data' => collect($paginator->items())
                ->map(function (ICU $icu, int $index) use ($from) {
                    return $this->transformIcuListItem($icu, $from ? $from + $index : null);
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
     * @param  array{data: array<int, mixed>, links: array<int, mixed>, meta: array<string, int|null>}  $items
     * @return array<string, mixed>
     */
    private function listPagePayload(Request $request, array $items, bool $includeDischarge): array
    {
        $filterKeys = $this->icuListFilterKeys($includeDischarge);
        $filters = $this->collectFilters($request, $filterKeys);

        if ($includeDischarge && $filters['discharge_filter'] === '') {
            $filters['discharge_filter'] = 'in_icu';
        }

        return [
            'icus' => $items,
            'filters' => $filters,
            'urls' => [
                'current' => $request->url(),
                ...$this->icuListUrls(),
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $query = DB::table('i_c_u_s as i')
            ->leftJoin('patients as p', 'i.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'i.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'i.branch_id', '=', 'b.id')
            ->select(
                'i.id',
                'p.name as patient_name',
                'd.name as doctor_name',
                'b.name as branch_name',
                'i.status',
                'i.created_at',
            )
            ->orderByDesc('i.created_at');

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('status')) {
            $query->where('i.status', $request->status);
        }

        if ($request->filled('date_from')) {
            try {
                $query->whereDate('i.created_at', '>=', \Hekmatinasser\Verta\Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('i.created_at', '<=', \Hekmatinasser\Verta\Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
            }
        }

        return $query->limit(200)->get()->map(fn ($item) => [
            'id' => $item->id,
            'patient_name' => $item->patient_name,
            'doctor_name' => $item->doctor_name,
            'branch_name' => $item->branch_name,
            'status' => $item->status,
            'created_at' => $item->created_at
                ? $this->formatIcuDate(\Illuminate\Support\Carbon::parse($item->created_at))
                : null,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(ICU $icu, ?Hospitalization $placement): array
    {
        return [
            'id' => $icu->id,
            'description' => $icu->description,
            'status' => $icu->status,
            'icu_enterance_note' => $icu->icu_enterance_note,
            'icu_reject_reason' => $icu->icu_reject_reason,
            'discharge_status' => $icu->discharge_status,
            'discharge_remark' => $icu->discharge_remark,
            'is_discharged' => (bool) $icu->is_discharged,
            'discharged_at' => $this->formatIcuDate($icu->discharged_at),
            'cause_of_death' => $icu->cause_of_death,
            'death_date' => $icu->death_date,
            'death_time' => $icu->death_time,
            'brief_history' => $icu->brief_history,
            'transfer_date' => $icu->transfer_date,
            'created_at' => $this->formatIcuDate($icu->created_at),
            'appointment_id' => $icu->appointment_id,
            'patient' => $icu->patient ? [
                'id' => $icu->patient->id,
                'name' => $icu->patient->name,
                'last_name' => $icu->patient->last_name,
                'father_name' => $icu->patient->father_name,
                'id_card' => $icu->patient->id_card,
                'phone' => $icu->patient->phone,
            ] : null,
            'doctor_name' => $icu->doctor?->name,
            'branch_name' => $icu->branch?->name,
            'department_name' => $icu->appointment?->department?->name,
            'room_name' => $placement?->room?->name,
            'bed_number' => $placement?->bed?->number,
        ];
    }

    private function backUrlForStatus(string $status): string
    {
        return match ($status) {
            'approved' => route('react.icus.approved'),
            'rejected' => route('react.icus.rejected'),
            default => route('react.icus.new'),
        };
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function dischargeDepartments(?int $branchId): array
    {
        return Department::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $department) => ['id' => $department->id, 'name' => $department->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, department_id: int|null}>
     */
    private function dischargeRooms(?int $branchId): array
    {
        return Room::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->whereHas('beds', fn ($query) => $query->where('is_occupied', false))
            ->orderBy('name')
            ->get(['id', 'name', 'department_id'])
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'department_id' => $room->department_id,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, number: string|int, room_id: int}>
     */
    private function dischargeBeds(?int $branchId): array
    {
        return Bed::query()
            ->where('is_occupied', false)
            ->when($branchId, fn ($query) => $query->whereHas('room', fn ($room) => $room->where('branch_id', $branchId)))
            ->orderBy('number')
            ->get(['id', 'number', 'room_id'])
            ->map(fn (Bed $bed) => [
                'id' => $bed->id,
                'number' => $bed->number,
                'room_id' => $bed->room_id,
            ])
            ->values()
            ->all();
    }
}
