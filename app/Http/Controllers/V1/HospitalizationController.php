<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Bed;
use App\Models\DiabetesChart;
use App\Models\Doctor;
use App\Models\FoodType;
use App\Models\Hospitalization;
use App\Models\MedicationAdministrationRecord;
use App\Models\NurseNote;
use App\Models\NutritionCare;
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use App\Models\VitalSign;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HospitalizationController extends Controller
{
    use PaginatesInertiaIndex;

    protected function authorizeMenu(): void
    {
        abort_unless(request()->user()?->can('show-hospitalizations-menu'), 403);
    }

    protected function branchId(): int
    {
        return (int) request()->user()->branch_id;
    }

    protected function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    protected function canEditRooms(?User $user = null): bool
    {
        $user = $user ?? request()->user();

        return $user?->hasRole(['super_admin', 'admin'])
            || $user?->can('edit-hospitalizations');
    }

    public function index(Request $request): Response
    {
        $this->authorizeMenu();

        $query = Hospitalization::query()
            ->where('branch_id', $this->branchId())
            ->where('is_discharged', '0')
            ->visibleForAuthUserDepartment()
            ->with([
                'patient:id,name,father_name,id_card',
                'room:id,name',
                'bed:id,number',
                'doctor:id,name',
                'department:id,name',
                'appointment.department:id,name',
            ])
            ->orderByDesc('created_at');

        $this->applyActiveFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload($paginator, fn (Hospitalization $item) => [
            'id' => $item->id,
            'patient_id_card' => $item->patient?->id_card,
            'patient_name' => $item->patient?->name,
            'father_name' => $item->patient?->father_name,
                'department_name' => $item->department?->name ?? $item->appointment?->department?->name,
            'room_name' => $item->room?->name,
            'bed_number' => $item->bed?->number,
            'doctor_name' => $item->doctor?->name,
            'admission_date' => $this->formatDate($item->created_at),
            'urls' => [
                'show' => route('react.hospitalizations.show', $item),
            ],
        ]);

        return Inertia::render('Hospitalizations/Index', [
            'hospitalizations' => $items,
            'stats' => $this->branchStats(),
            'filters' => $this->collectFilters($request, ['q', 'room_id', 'date_from', 'date_to']),
            'filterOptions' => [
                'rooms' => $this->branchRooms($this->scopedDepartmentId()),
            ],
            'urls' => $this->indexUrls($request),
        ]);
    }

    public function discharged(Request $request): Response
    {
        $this->authorizeMenu();

        $query = Hospitalization::query()
            ->where('branch_id', $this->branchId())
            ->where('is_discharged', '1')
            ->visibleForAuthUserDepartment()
            ->with([
                'patient:id,name,father_name,id_card',
                'room:id,name',
                'bed:id,number',
                'doctor:id,name',
            ])
            ->orderByDesc('discharged_at')
            ->orderByDesc('id');

        $this->applyDischargedFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50, 100]);
        $items = $this->paginationPayload($paginator, fn (Hospitalization $item) => [
            'id' => $item->id,
            'patient_id_card' => $item->patient?->id_card,
            'patient_name' => $item->patient?->name,
            'father_name' => $item->patient?->father_name,
            'room_name' => $item->room?->name,
            'bed_number' => $item->bed?->number,
            'doctor_name' => $item->doctor?->name,
            'admission_date' => $this->formatDate($item->created_at),
            'discharged_at' => $this->formatDate($item->discharged_at),
            'discharge_status' => $item->discharge_status,
            'urls' => [
                'show' => route('react.hospitalizations.show', $item),
            ],
        ]);

        return Inertia::render('Hospitalizations/Discharged', [
            'hospitalizations' => $items,
            'stats' => $this->branchStats(),
            'filters' => $this->collectFilters($request, [
                'q',
                'patient_id',
                'room_id',
                'doctor_id',
                'discharge_date_from',
                'discharge_date_to',
            ]),
            'filterOptions' => [
                'rooms' => $this->branchRooms($this->scopedDepartmentId()),
                'doctors' => Doctor::query()
                    ->where('branch_id', $this->branchId())
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
            'urls' => [
                'current' => route('react.hospitalizations.discharged'),
                'index' => route('react.hospitalizations.index'),
            ],
        ]);
    }

    public function show(Request $request, Hospitalization $hospitalization): Response
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);

        $hospitalization->load([
            'patient:id,name,father_name,id_card,phone',
            'doctor:id,name',
            'department:id,name',
            'room:id,name',
            'bed:id,number',
            'appointment:id,patient_id,doctor_id,is_completed,department_id',
            'appointment.department:id,name',
            'bloodBanks:id,hospitalization_id,group,created_at',
            'vitalSigns.vitalSignType:id,name',
            'vitalSigns.schedules.nurse:id,first_name,last_name',
            'nursingAssessments:id,morphable_id,morphable_type,created_at',
            'advices:id,hospitalization_id,description,created_at',
            'complaints:id,hospitalization_id,description,created_at',
            'icu:id,hospitalization_id,created_at',
            'anesthesias:id,hospitalization_id,created_at',
        ]);

        $medicationRecords = MedicationAdministrationRecord::query()
            ->where('morphable_type', Hospitalization::class)
            ->where('morphable_id', $hospitalization->id)
            ->with(['medicine:id,name', 'nurse:id,first_name,last_name'])
            ->orderByDesc('order_date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $user = $request->user();

        return Inertia::render('Hospitalizations/Show', [
            'hospitalization' => $this->transformDetail($hospitalization, $medicationRecords),
            'permissions' => [
                'edit' => $user->can('edit-hospitalizations'),
                'discharge' => ! (bool) $hospitalization->is_discharged && $user->can('edit-hospitalizations'),
                'change_room_bed' => ! (bool) $hospitalization->is_discharged && $this->canEditRooms($user),
            ],
            'sectionPermissions' => [
                'prescription' => $user->can('show-prescriptions-menu'),
                'lab' => $user->can('show-labs-menu'),
                'physiotherapy' => $user->can('show-physiotherapy-procedures'),
                'vital_signs' => $user->can('viewAny', VitalSign::class),
                'visits' => $user->can('show-hospitalizations-menu'),
                'diabetes_charts' => $user->can('viewAny', DiabetesChart::class),
                'nurse_notes' => $user->can('viewAny', NurseNote::class),
                'nutrition_cares' => $user->can('viewAny', NutritionCare::class),
            ],
            'urls' => [
                'index' => route('react.hospitalizations.index'),
                'edit' => route('react.hospitalizations.edit', $hospitalization),
                'discharge' => route('react.hospitalizations.discharge', $hospitalization),
                'appointment' => $hospitalization->appointment_id
                    ? route('react.appointments.show', $hospitalization->appointment_id)
                    : null,
                'change_room_bed' => route('hospitalizations.changeRoomBed', $hospitalization),
            ],
        ]);
    }

    public function edit(Hospitalization $hospitalization): Response
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_unless(request()->user()->can('edit-hospitalizations'), 403);

        $hospitalization->load(['appointment:id,patient_id']);

        return Inertia::render('Hospitalizations/Edit', [
            'hospitalization' => [
                'id' => $hospitalization->id,
                'reason' => $hospitalization->reason,
                'remarks' => $hospitalization->remarks,
                'room_id' => $hospitalization->room_id,
                'bed_id' => $hospitalization->bed_id,
                'patient_id' => $hospitalization->patient_id,
                'appointment_id' => $hospitalization->appointment_id,
                'branch_id' => $hospitalization->branch_id,
                'food_type_ids' => $this->decodeFoodTypeIds($hospitalization->food_type_id),
                'patinet_companion' => $hospitalization->patinet_companion,
                'companion_father_name' => $hospitalization->companion_father_name,
                'relation_to_patient' => $hospitalization->relation_to_patient,
                'companion_card_type' => $hospitalization->companion_card_type,
            ],
            'rooms' => $this->branchRooms($this->scopedDepartmentId()),
            'beds' => $this->branchBeds($this->scopedDepartmentId()),
            'foodTypes' => FoodType::query()->orderBy('name')->get(['id', 'name']),
            'relations' => Relation::query()->orderBy('name')->get(['id', 'name']),
            'urls' => [
                'show' => route('react.hospitalizations.show', $hospitalization),
                'update' => route('react.hospitalizations.update', $hospitalization),
            ],
        ]);
    }

    public function update(Request $request, Hospitalization $hospitalization): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_unless($request->user()->can('edit-hospitalizations'), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'branch_id' => 'required|integer',
            'food_type_ids' => 'nullable|array',
            'food_type_ids.*' => 'integer|exists:food_types,id',
            'patinet_companion' => 'nullable|string',
            'companion_father_name' => 'nullable|string',
            'relation_to_patient' => 'nullable',
            'companion_card_type' => 'nullable|string',
        ]);

        if ((int) $validated['bed_id'] !== (int) $hospitalization->bed_id) {
            $this->releaseBed($hospitalization->bed_id);
            Bed::query()->whereKey($validated['bed_id'])->update(['is_occupied' => 1]);
        }

        $departmentId = $this->resolveDepartmentIdForSave(
            $request,
            $validated['appointment_id'] ?? null,
            (int) $validated['room_id'],
            $hospitalization->department_id
        );

        $hospitalization->update([
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'],
            'room_id' => $validated['room_id'],
            'bed_id' => $validated['bed_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'branch_id' => $validated['branch_id'],
            'department_id' => $departmentId,
            'food_type_id' => json_encode($validated['food_type_ids'] ?? []),
            'patinet_companion' => $validated['patinet_companion'] ?? null,
            'companion_father_name' => $validated['companion_father_name'] ?? null,
            'relation_to_patient' => $validated['relation_to_patient'] ?? null,
            'companion_card_type' => $validated['companion_card_type'] ?? null,
        ]);

        return redirect()
            ->route('react.hospitalizations.show', $hospitalization)
            ->with('success', localize('global.hospitalization_updated_successfully.'));
    }

    public function discharge(Request $request, Hospitalization $hospitalization): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_unless($request->user()->can('edit-hospitalizations'), 403);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $validated = $request->validate([
            'discharge_remark' => 'required|string',
            'discharge_status' => 'required|in:recovered,died,moved',
        ]);

        DB::transaction(function () use ($hospitalization, $validated) {
            $hospitalization->update([
                'is_discharged' => 1,
                'discharge_remark' => $validated['discharge_remark'],
                'discharge_status' => $validated['discharge_status'],
                'discharged_at' => now(),
            ]);

            $this->releaseBed($hospitalization->bed_id);
        });

        return redirect()
            ->route('react.hospitalizations.show', $hospitalization)
            ->with('success', localize('global.hospitalization_updated_successfully.'));
    }

    public function report(Request $request): Response
    {
        $this->authorizeMenu();

        $items = [];
        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }

        return Inertia::render('Hospitalizations/Report', [
            'items' => $items,
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'doctor_id',
                'room_id',
                'food_type_id',
                'is_discharged',
                'date_from',
                'date_to',
            ]),
            'filterOptions' => [
                'doctors' => Doctor::query()
                    ->where('branch_id', $this->branchId())
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'rooms' => $this->branchRooms($this->scopedDepartmentId()),
                'foodTypes' => FoodType::query()->orderBy('name')->get(['id', 'name']),
            ],
            'urls' => [
                'current' => route('react.hospitalizations.report'),
                'index' => route('react.hospitalizations.index'),
                'export' => route('hospitalizations.export-report'),
            ],
        ]);
    }

    public function roomManagement(Request $request): Response
    {
        $this->authorizeRoomManagement();

        $rooms = $this->roomManagementSummaries();
        $selectedRoomId = (int) $request->input('room_id', 0);
        if ($selectedRoomId === 0 && $rooms !== []) {
            $selectedRoomId = (int) $rooms[0]['id'];
        }

        $beds = [];
        $selectedRoom = null;

        if ($selectedRoomId > 0) {
            $selectedRoom = $this->findRoomForManagement($selectedRoomId);
            abort_unless($selectedRoom !== null, 404);
            $this->authorize('manage', $selectedRoom);
            $selectedRoom->loadMissing('department:id,name');

            $bedModels = $selectedRoom->allBeds()->orderBy('number')->get();
            $activeByBed = Hospitalization::query()
                ->whereIn('bed_id', $bedModels->pluck('id'))
                ->where('is_discharged', 0)
                ->visibleForAuthUserDepartment()
                ->with('patient:id,name,father_name')
                ->get()
                ->keyBy('bed_id');

            $beds = $bedModels->map(function (Bed $bed) use ($activeByBed) {
                $hospitalization = $activeByBed->get($bed->id);

                return [
                    'id' => $bed->id,
                    'number' => $bed->number,
                    'is_occupied' => (bool) $bed->is_occupied,
                    'patient_name' => $hospitalization?->patient?->name,
                    'father_name' => $hospitalization?->patient?->father_name,
                    'admission_date' => $hospitalization
                        ? $this->formatDate($hospitalization->created_at)
                        : null,
                    'hospitalization_id' => $hospitalization?->id,
                    'hospitalization_url' => $hospitalization
                        ? route('react.hospitalizations.show', $hospitalization)
                        : null,
                ];
            })->values()->all();
        }

        return Inertia::render('Hospitalizations/RoomManagement', [
            'rooms' => $rooms,
            'overview' => $this->roomManagementOverview($rooms),
            'selectedRoom' => $selectedRoom ? [
                'id' => $selectedRoom->id,
                'name' => $selectedRoom->name,
                'department_name' => $selectedRoom->department?->name,
            ] : null,
            'beds' => $beds,
            'filters' => ['room_id' => (string) $selectedRoomId],
            'urls' => [
                'current' => route('react.hospitalizations.room-management'),
                'index' => route('react.hospitalizations.index'),
            ],
        ]);
    }

  /**
     * @param  \Illuminate\Database\Eloquent\Builder<Hospitalization>  $query
     */
    private function applyActiveFilters($query, Request $request): void
    {
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($w) use ($search) {
                $w->whereHas('patient', function ($p) use ($search) {
                    $p->where('name', 'like', '%'.$search.'%')
                        ->orWhere('father_name', 'like', '%'.$search.'%')
                        ->orWhere('id_card', 'like', '%'.$search.'%');
                })
                    ->orWhereHas('room', fn ($r) => $r->where('name', 'like', '%'.$search.'%'))
                    ->orWhereHas('bed', fn ($b) => $b->where('number', 'like', '%'.$search.'%'))
                    ->orWhere('reason', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        $this->applyDateRangeFilter($query, $request, 'created_at', 'date_from', 'date_to');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Hospitalization>  $query
     */
    private function applyDischargedFilters($query, Request $request): void
    {
        $patientId = (int) $request->input('patient_id', 0);
        if ($patientId > 0) {
            $query->where('patient_id', $patientId);
        }

        if ($request->filled('q')) {
            $like = '%'.$request->q.'%';
            $query->whereHas('patient', fn ($p) => $p->where('name', 'like', $like));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $this->applyDateRangeFilter($query, $request, 'discharged_at', 'discharge_date_from', 'discharge_date_to');
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<Hospitalization>  $query
     */
    private function applyDateRangeFilter($query, Request $request, string $column, string $fromKey, string $toKey): void
    {
        if ($request->filled($fromKey)) {
            try {
                $query->whereDate($column, '>=', Verta::parse($request->input($fromKey))->datetime());
            } catch (\Throwable) {
            }
        }

        if ($request->filled($toKey)) {
            try {
                $query->whereDate($column, '<=', Verta::parse($request->input($toKey))->datetime());
            } catch (\Throwable) {
            }
        }
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    protected function authorizeRoomManagement(): void
    {
        $this->authorizeMenu();
        $this->authorize('manageAny', Room::class);
    }

    private function scopedDepartmentId(): ?int
    {
        $user = request()->user();
        if (Hospitalization::userBypassesDepartmentScope($user)) {
            return null;
        }

        return $user?->department_id;
    }

    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     department_name: string|null,
     *     beds_count: int,
     *     occupied_beds_count: int,
     *     empty_beds_count: int,
     *     occupancy_rate: int
     * }>
     */
    private function roomManagementSummaries(): array
    {
        return $this->branchRoomQuery()
            ->with('department:id,name')
            ->withCount([
                'allBeds as beds_count',
                'allBeds as occupied_beds_count' => fn ($query) => $query->where('is_occupied', true),
            ])
            ->orderBy('name')
            ->get()
            ->map(function (Room $room) {
                $emptyBeds = max($room->beds_count - $room->occupied_beds_count, 0);

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'department_name' => $room->department?->name,
                    'beds_count' => (int) $room->beds_count,
                    'occupied_beds_count' => (int) $room->occupied_beds_count,
                    'empty_beds_count' => $emptyBeds,
                    'occupancy_rate' => $room->beds_count > 0
                        ? (int) round(($room->occupied_beds_count / $room->beds_count) * 100)
                        : 0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  list<array{
     *     beds_count: int,
     *     occupied_beds_count: int,
     *     empty_beds_count: int,
     *     occupancy_rate: int
     * }>  $rooms
     * @return array{
     *     rooms_count: int,
     *     beds_count: int,
     *     occupied_beds_count: int,
     *     empty_beds_count: int,
     *     occupancy_rate: int
     * }
     */
    private function roomManagementOverview(array $rooms): array
    {
        $bedsCount = array_sum(array_column($rooms, 'beds_count'));
        $occupiedCount = array_sum(array_column($rooms, 'occupied_beds_count'));

        return [
            'rooms_count' => count($rooms),
            'beds_count' => $bedsCount,
            'occupied_beds_count' => $occupiedCount,
            'empty_beds_count' => max($bedsCount - $occupiedCount, 0),
            'occupancy_rate' => $bedsCount > 0 ? (int) round(($occupiedCount / $bedsCount) * 100) : 0,
        ];
    }

    private function findRoomForManagement(int $roomId): ?Room
    {
        return $this->branchRoomQuery()->find($roomId);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Room>
     */
    private function branchRoomQuery()
    {
        return Room::query()->manageableForHospitalization();
    }

    private function branchRooms(?int $departmentId = null): array
    {
        return Room::query()
            ->where('branch_id', $this->branchId())
            ->when(
                $departmentId,
                fn ($query) => $query->where(function ($roomQuery) use ($departmentId) {
                    $roomQuery->where('department_id', $departmentId)
                        ->orWhereNull('department_id');
                })
            )
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Room $room) => ['id' => $room->id, 'name' => $room->name])
            ->values()
            ->all();
    }

    private function branchBeds(?int $departmentId = null): array
    {
        return Bed::query()
            ->whereHas('room', function ($query) use ($departmentId) {
                $query->where('branch_id', $this->branchId())
                    ->when(
                        $departmentId,
                        fn ($roomQuery) => $roomQuery->where(function ($scopedRoomQuery) use ($departmentId) {
                            $scopedRoomQuery->where('department_id', $departmentId)
                                ->orWhereNull('department_id');
                        })
                    );
            })
            ->orderBy('number')
            ->get(['id', 'number', 'room_id', 'is_occupied'])
            ->all();
    }

    private function resolveDepartmentIdForSave(
        Request $request,
        ?int $appointmentId,
        int $roomId,
        ?int $currentDepartmentId
    ): ?int {
        if (! Hospitalization::userBypassesDepartmentScope($request->user())) {
            abort_unless(
                $request->user()->department_id !== null
                    && (int) $currentDepartmentId === (int) $request->user()->department_id,
                403
            );

            return (int) $request->user()->department_id;
        }

        if ($appointmentId) {
            $appointmentDepartmentId = \App\Models\Appointment::query()
                ->whereKey($appointmentId)
                ->value('department_id');
            if ($appointmentDepartmentId) {
                return (int) $appointmentDepartmentId;
            }
        }

        $roomDepartmentId = Room::query()->whereKey($roomId)->value('department_id');

        return $roomDepartmentId ? (int) $roomDepartmentId : $currentDepartmentId;
    }

    /**
     * @return list<int>
     */
    private function decodeFoodTypeIds(mixed $value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = json_decode((string) $value, true);

        if (is_array($decoded)) {
            return array_values(array_map('intval', $decoded));
        }

        return [(int) $value];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $query = Hospitalization::query()
            ->where('branch_id', $this->branchId())
            ->visibleForAuthUserDepartment()
            ->with([
                'patient:id,name,id_card',
                'doctor:id,name',
                'room:id,name',
                'bed:id,number',
            ])
            ->orderByDesc('created_at');

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn ($p) => $p->where('name', 'like', '%'.$request->patient_name.'%'));
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('is_discharged') && $request->is_discharged !== '') {
            $query->where('is_discharged', (int) $request->is_discharged);
        }

        $this->applyDateRangeFilter($query, $request, 'created_at', 'date_from', 'date_to');

        return $query->limit(200)->get()->map(fn (Hospitalization $item) => [
            'id' => $item->id,
            'patient_name' => $item->patient?->name,
            'patient_id_card' => $item->patient?->id_card,
            'doctor_name' => $item->doctor?->name,
            'room_name' => $item->room?->name,
            'bed_number' => $item->bed?->number,
            'admission_date' => $this->formatDate($item->created_at),
            'discharged_at' => $this->formatDate($item->discharged_at),
            'is_discharged' => (bool) $item->is_discharged,
            'urls' => ['show' => route('react.hospitalizations.show', $item)],
        ])->values()->all();
    }

    /**
     * @param  \Illuminate\Support\Collection<int, DiabetesChart>  $diabetesCharts
     * @param  \Illuminate\Support\Collection<int, MedicationAdministrationRecord>  $medicationRecords
     * @return array<string, mixed>
     */
    private function transformDetail(
        Hospitalization $hospitalization,
        $medicationRecords,
    ): array {
        return [
            'id' => $hospitalization->id,
            'reason' => $hospitalization->reason,
            'remarks' => $hospitalization->remarks,
            'discharge_remark' => $hospitalization->discharge_remark,
            'discharge_status' => $hospitalization->discharge_status,
            'is_discharged' => (bool) $hospitalization->is_discharged,
            'admission_date' => $this->formatDate($hospitalization->created_at),
            'admission_time' => $hospitalization->created_at?->format('H:i'),
            'discharged_at' => $this->formatDate($hospitalization->discharged_at),
            'appointment_id' => $hospitalization->appointment_id,
            'patient' => $hospitalization->patient ? [
                'id' => $hospitalization->patient->id,
                'name' => $hospitalization->patient->name,
                'father_name' => $hospitalization->patient->father_name,
                'id_card' => $hospitalization->patient->id_card,
                'phone' => $hospitalization->patient->phone,
            ] : null,
            'doctor_name' => $hospitalization->doctor?->name,
            'department_name' => $hospitalization->department?->name
                ?? $hospitalization->appointment?->department?->name,
            'room_name' => $hospitalization->room?->name,
            'bed_number' => $hospitalization->bed?->number,
            'blood_banks' => $hospitalization->bloodBanks->map(fn ($item) => [
                'id' => $item->id,
                'group' => $item->group,
                'created_at' => $this->formatDate($item->created_at),
            ])->values()->all(),
            'medication_records' => $medicationRecords->map(fn (MedicationAdministrationRecord $record) => [
                'id' => $record->id,
                'order_date' => $record->order_date,
                'medicine_name' => $record->medicine?->name,
                'nurse_name' => $record->nurse
                    ? trim($record->nurse->first_name.' '.$record->nurse->last_name)
                    : null,
            ])->values()->all(),
            'vital_signs' => $hospitalization->vitalSigns->map(fn ($vital) => [
                'id' => $vital->id,
                'type_name' => $vital->vitalSignType?->name,
                'schedules_count' => $vital->schedules->count(),
                'recorded_at' => $this->formatDate($vital->created_at),
            ])->values()->all(),
            'nursing_assessments_count' => $hospitalization->nursingAssessments->count(),
            'advices_count' => $hospitalization->advices->count(),
            'complaints_count' => $hospitalization->complaints->count(),
            'icu_count' => $hospitalization->icu->count(),
            'anesthesia_count' => $hospitalization->anesthesias->count(),
        ];
    }

    private function indexUrls(Request $request): array
    {
        $user = $request->user();

        return [
            'current' => route('react.hospitalizations.index'),
            'discharged' => route('react.hospitalizations.discharged'),
            'report' => route('react.hospitalizations.report'),
            'room_management' => $user?->can('manageAny', Room::class)
                ? route('react.hospitalizations.room-management')
                : null,
        ];
    }

    private function branchStats(): array
    {
        $branchId = $this->branchId();
        $hospitalizationQuery = Hospitalization::query()
            ->where('branch_id', $branchId)
            ->visibleForAuthUserDepartment();
        $departmentId = $this->scopedDepartmentId();
        $bedQuery = Bed::query()
            ->whereHas('room', function ($query) use ($branchId, $departmentId) {
                $query->where('branch_id', $branchId)
                    ->when($departmentId, fn ($roomQuery) => $roomQuery->where('department_id', $departmentId));
            });
        $dischargedQuery = (clone $hospitalizationQuery)->where('is_discharged', '1');

        return [
            'active' => (clone $hospitalizationQuery)->where('is_discharged', '0')->count(),
            'discharged' => (clone $dischargedQuery)->count(),
            'occupied_beds' => (clone $bedQuery)->where('is_occupied', true)->count(),
            'total_beds' => (clone $bedQuery)->count(),
            'recovered' => (clone $dischargedQuery)->where('discharge_status', 'recovered')->count(),
            'moved' => (clone $dischargedQuery)->where('discharge_status', 'moved')->count(),
            'died' => (clone $dischargedQuery)->where('discharge_status', 'died')->count(),
        ];
    }

    private function releaseBed(?int $bedId): void
    {
        if (! $bedId) {
            return;
        }

        Bed::query()->whereKey($bedId)->update(['is_occupied' => 0]);
    }

    private function formatDate(?\Illuminate\Support\Carbon $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return verta($date)->format('Y/n/j');
        } catch (\Throwable) {
            return $date->format('Y-m-d');
        }
    }
}
