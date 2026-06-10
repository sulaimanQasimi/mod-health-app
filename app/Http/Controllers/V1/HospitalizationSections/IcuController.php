<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\Room;
use App\Services\IcuReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IcuController extends Controller
{
    public function __construct(
        private readonly IcuReferralService $icuReferralService,
    ) {}

    public function index(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);

        $user = request()->user();
        if (! $this->canView($user)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'items' => [],
                    'count' => 0,
                    'permissions' => [
                        'view' => false,
                        'create' => false,
                    ],
                ],
            ]);
        }

        $items = $hospitalization->icu()
            ->with(['patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (ICU $item) => IcuReferralService::formatListItem($item))
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $items,
                'count' => count($items),
                'permissions' => $this->permissions($user, $hospitalization),
            ],
        ]);
    }

    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless($this->canCreate(request()->user(), $hospitalization), 403);

        $hospitalization->loadMissing(['patient:id,name', 'room:id,name', 'bed:id,number']);
        $branchId = $hospitalization->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $hospitalization->patient?->name,
                'current_room_name' => $hospitalization->room?->name,
                'current_bed_number' => $hospitalization->bed?->number,
                'default_department_id' => $hospitalization->department_id,
                'departments' => $this->departments($branchId),
                'rooms' => $this->roomsWithAvailableBeds($branchId),
                'beds' => $this->availableBeds($branchId),
            ],
        ]);
    }

    public function store(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);
        abort_unless($this->canCreate($request->user(), $hospitalization), 403);

        $hospitalization->loadMissing(['patient:id,name', 'appointment:id,doctor_id']);

        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        try {
            $this->icuReferralService->create([
                'description' => $validated['description'],
                'patient_id' => $hospitalization->patient_id,
                'appointment_id' => $hospitalization->appointment_id,
                'hospitalization_id' => $hospitalization->id,
                'doctor_id' => $hospitalization->appointment?->doctor_id ?? $request->user()->doctor?->id,
                'branch_id' => $hospitalization->branch_id ?? $request->user()->branch_id,
                'room_id' => $validated['room_id'],
                'bed_id' => $validated['bed_id'],
            ], $request->user());
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())->flatten()->first(),
                'errors' => $exception->errors(),
            ], 422);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Hospitalization $hospitalization, ICU $icu): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_unless(request()->user()->can('delete-icus'), 403);
        abort_unless((int) $icu->hospitalization_id === (int) $hospitalization->id, 404);

        $icu->delete();

        return response()->json(['success' => true]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-icu') ?? false)
            || ($user?->can('edit-icus') ?? false)
            || ($user?->can('delete-icus') ?? false);
    }

    private function canCreate($user, Hospitalization $hospitalization): bool
    {
        return ! (bool) $hospitalization->is_discharged && ($user?->can('refer-to-icu') ?? false);
    }

    /**
     * @return array{view: bool, create: bool}
     */
    private function permissions($user, Hospitalization $hospitalization): array
    {
        return [
            'view' => $this->canView($user),
            'create' => $this->canCreate($user, $hospitalization),
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function departments(?int $branchId): array
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
    private function roomsWithAvailableBeds(?int $branchId): array
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
    private function availableBeds(?int $branchId): array
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
