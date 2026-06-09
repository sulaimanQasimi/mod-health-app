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
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use App\Models\VitalSign;
use App\Models\Visit;
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
            'filters' => $this->collectFilters($request, ['q', 'room_id', 'date_from', 'date_to']),
            'filterOptions' => [
                'rooms' => $this->branchRooms(),
            ],
            'urls' => [
                'current' => route('react.hospitalizations.index'),
                'discharged' => route('react.hospitalizations.discharged'),
            ],
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
            'filters' => $this->collectFilters($request, [
                'q',
                'patient_id',
                'room_id',
                'doctor_id',
                'discharge_date_from',
                'discharge_date_to',
            ]),
            'filterOptions' => [
                'rooms' => $this->branchRooms(),
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
            'room:id,name',
            'bed:id,number',
            'appointment:id,patient_id,doctor_id,is_completed,department_id',
            'visits.doctor:id,name',
            'bloodBanks:id,hospitalization_id,group,created_at',
            'vitalSigns.vitalSignType:id,name',
            'vitalSigns.schedules.nurse:id,first_name,last_name',
            'nutritionCares.createdBy:id,name,last_name',
            'nutritionCares.nurse:id,first_name,last_name',
            'nursingAssessments:id,morphable_id,morphable_type,created_at',
            'advices:id,hospitalization_id,description,created_at',
            'complaints:id,hospitalization_id,description,created_at',
            'icu:id,hospitalization_id,created_at',
            'anesthesias:id,hospitalization_id,created_at',
        ]);

        $diabetesCharts = DiabetesChart::query()
            ->where('diabetes_chartable_type', Hospitalization::class)
            ->where('diabetes_chartable_id', $hospitalization->id)
            ->with(['nurse:id,first_name,last_name', 'medicine:id,name'])
            ->orderByDesc('date')
            ->orderByDesc('time')
            ->limit(50)
            ->get();

        $nurseNotes = NurseNote::query()
            ->where('morphable_type', Hospitalization::class)
            ->where('morphable_id', $hospitalization->id)
            ->with(['nurse:id,first_name,last_name'])
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

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
            'hospitalization' => $this->transformDetail($hospitalization, $diabetesCharts, $nurseNotes, $medicationRecords),
            'permissions' => [
                'edit' => $user->can('edit-hospitalizations'),
                'discharge' => ! (bool) $hospitalization->is_discharged && $user->can('edit-hospitalizations'),
                'change_room_bed' => ! (bool) $hospitalization->is_discharged && $this->canEditRooms($user),
                'store_visit' => ! (bool) $hospitalization->is_discharged,
                'edit_visit' => $user->can('edit-hospitalizations'),
                'delete_visit' => $user->can('delete-hospitalizations'),
            ],
            'sectionPermissions' => [
                'prescription' => $user->can('show-prescriptions-menu'),
                'lab' => $user->can('show-labs-menu'),
                'physiotherapy' => $user->can('show-physiotherapy-procedures'),
                'vital_signs' => $user->can('viewAny', VitalSign::class),
            ],
            'urls' => [
                'index' => route('react.hospitalizations.index'),
                'edit' => route('react.hospitalizations.edit', $hospitalization),
                'discharge' => route('react.hospitalizations.discharge', $hospitalization),
                'visit_store' => route('react.hospitalizations.visits.store', $hospitalization),
                'visit_update' => url('/react/hospitalizations/'.$hospitalization->id.'/visits'),
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
            'rooms' => $this->branchRooms(),
            'beds' => Bed::query()
                ->whereHas('room', fn ($q) => $q->where('branch_id', $this->branchId()))
                ->orderBy('number')
                ->get(['id', 'number', 'room_id', 'is_occupied']),
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

        $departmentId = null;
        if (! empty($validated['appointment_id'])) {
            $departmentId = \App\Models\Appointment::query()
                ->whereKey($validated['appointment_id'])
                ->value('department_id');
        }

        $hospitalization->update([
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'],
            'room_id' => $validated['room_id'],
            'bed_id' => $validated['bed_id'],
            'patient_id' => $validated['patient_id'],
            'appointment_id' => $validated['appointment_id'] ?? null,
            'branch_id' => $validated['branch_id'],
            'department_id' => $departmentId ?? \App\Models\Room::query()->whereKey($validated['room_id'])->value('department_id'),
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

    public function storeVisit(Request $request, Hospitalization $hospitalization): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        Visit::create([
            'description' => $validated['description'],
            'patient_id' => $hospitalization->patient_id,
            'doctor_id' => $hospitalization->doctor_id,
            'hospitalization_id' => $hospitalization->id,
        ]);

        return redirect()
            ->route('react.hospitalizations.show', $hospitalization)
            ->with('success', localize('global.visit_created_successfully.'));
    }

    public function updateVisit(Request $request, Hospitalization $hospitalization, Visit $visit): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_unless($request->user()->can('edit-hospitalizations'), 403);
        abort_unless((int) $visit->hospitalization_id === (int) $hospitalization->id, 404);

        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        $visit->update($validated);

        return redirect()
            ->route('react.hospitalizations.show', $hospitalization)
            ->with('success', localize('global.visit_updated_successfully.'));
    }

    public function destroyVisit(Hospitalization $hospitalization, Visit $visit): RedirectResponse
    {
        $this->authorizeMenu();
        $this->ensureAccessible($hospitalization);
        abort_unless(request()->user()->can('delete-hospitalizations'), 403);
        abort_unless((int) $visit->hospitalization_id === (int) $hospitalization->id, 404);

        $visit->delete();

        return redirect()
            ->route('react.hospitalizations.show', $hospitalization)
            ->with('success', localize('global.visit_deleted_successfully.'));
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
                'rooms' => $this->branchRooms(),
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
        $this->authorizeMenu();
        abort_unless(request()->user()->hasRole(['admin', 'super_admin']), 403);

        $rooms = $this->branchRooms();
        $selectedRoomId = (int) $request->input('room_id', 0);
        $beds = [];

        if ($selectedRoomId > 0) {
            $room = Room::query()
                ->where('branch_id', $this->branchId())
                ->find($selectedRoomId);

            if ($room) {
                $bedModels = $room->allBeds()->orderBy('number')->get();
                $activeByBed = Hospitalization::query()
                    ->whereIn('bed_id', $bedModels->pluck('id'))
                    ->where('is_discharged', 0)
                    ->with('patient:id,name')
                    ->get()
                    ->keyBy('bed_id');

                $beds = $bedModels->map(fn (Bed $bed) => [
                    'id' => $bed->id,
                    'number' => $bed->number,
                    'is_occupied' => (bool) $bed->is_occupied,
                    'patient_name' => $activeByBed->get($bed->id)?->patient?->name,
                    'hospitalization_id' => $activeByBed->get($bed->id)?->id,
                ])->values()->all();
            }
        }

        return Inertia::render('Hospitalizations/RoomManagement', [
            'rooms' => $rooms,
            'selectedRoomId' => $selectedRoomId ?: null,
            'beds' => $beds,
            'filters' => ['room_id' => (string) $request->input('room_id', '')],
            'urls' => [
                'current' => route('react.hospitalizations.room-management'),
                'index' => route('react.hospitalizations.index'),
                'legacy' => route('hospitalizations.roomManagement'),
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
    private function branchRooms(): array
    {
        return Room::query()
            ->where('branch_id', $this->branchId())
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Room $room) => ['id' => $room->id, 'name' => $room->name])
            ->values()
            ->all();
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
     * @param  \Illuminate\Support\Collection<int, NurseNote>  $nurseNotes
     * @param  \Illuminate\Support\Collection<int, MedicationAdministrationRecord>  $medicationRecords
     * @return array<string, mixed>
     */
    private function transformDetail(
        Hospitalization $hospitalization,
        $diabetesCharts,
        $nurseNotes,
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
            'room_name' => $hospitalization->room?->name,
            'bed_number' => $hospitalization->bed?->number,
            'visits' => $hospitalization->visits->map(fn (Visit $visit) => [
                'id' => $visit->id,
                'description' => $visit->description,
                'doctor_name' => $visit->doctor?->name,
            ])->values()->all(),
            'blood_banks' => $hospitalization->bloodBanks->map(fn ($item) => [
                'id' => $item->id,
                'group' => $item->group,
                'created_at' => $this->formatDate($item->created_at),
            ])->values()->all(),
            'diabetes_charts' => $diabetesCharts->map(fn (DiabetesChart $chart) => [
                'id' => $chart->id,
                'date' => $chart->date,
                'time' => $chart->time,
                'rbs' => $chart->rbs,
                'fbs' => $chart->fbs,
                'insulin_dose' => $chart->insulin_dose,
                'nurse_name' => $chart->nurse
                    ? trim($chart->nurse->first_name.' '.$chart->nurse->last_name)
                    : null,
                'medicine_name' => $chart->medicine?->name,
            ])->values()->all(),
            'nurse_notes' => $nurseNotes->map(fn (NurseNote $note) => [
                'id' => $note->id,
                'date' => $note->date,
                'note_am' => $note->note_am,
                'note_pm' => $note->note_pm,
                'nurse_name' => $note->nurse
                    ? trim($note->nurse->first_name.' '.$note->nurse->last_name)
                    : null,
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
            'nutrition_cares' => $hospitalization->nutritionCares->map(fn ($care) => [
                'id' => $care->id,
                'date' => $care->date,
                'nurse_name' => $care->nurse
                    ? trim($care->nurse->first_name.' '.$care->nurse->last_name)
                    : null,
            ])->values()->all(),
            'nursing_assessments_count' => $hospitalization->nursingAssessments->count(),
            'advices_count' => $hospitalization->advices->count(),
            'complaints_count' => $hospitalization->complaints->count(),
            'icu_count' => $hospitalization->icu->count(),
            'anesthesia_count' => $hospitalization->anesthesias->count(),
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
