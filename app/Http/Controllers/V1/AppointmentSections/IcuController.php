<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\ICU;
use App\Models\Room;
use App\Services\IcuReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class IcuController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function __construct(
        private readonly IcuReferralService $icuReferralService,
    ) {}

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canView($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false, 'create' => false]);
        }

        $items = $appointment->icu()
            ->with(['patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (ICU $item) => IcuReferralService::formatListItem($item))
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canMutateAppointment($appointment) && $user->can('refer-to-icu'),
            'edit' => $this->canMutateAppointment($appointment) && $user->can('edit-icus'),
            'delete' => $this->canMutateAppointment($appointment) && $user->can('delete-icus'),
        ]);
    }

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canMutateAppointment($appointment) && request()->user()->can('refer-to-icu'), 403);

        $appointment->loadMissing('patient:id,name');
        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'patient_name' => $appointment->patient?->name,
                'default_department_id' => $appointment->department_id,
                'departments' => $this->departments($branchId),
                'rooms' => $this->roomsWithAvailableBeds($branchId),
                'beds' => $this->availableBeds($branchId),
            ],
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canMutateAppointment($appointment) && $request->user()->can('refer-to-icu'), 403);

        $validated = $request->validate([
            'description' => 'required|string|max:2000',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        try {
            $this->icuReferralService->create([
                'description' => $validated['description'],
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id ?? $request->user()->doctor?->id,
                'branch_id' => $appointment->branch_id ?? $request->user()->branch_id,
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

    public function destroy(Appointment $appointment, ICU $icu): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-icus'), 403);
        $this->assertAppointmentMutable($appointment);
        abort_unless((int) $icu->appointment_id === (int) $appointment->id, 404);
        $icu->delete();

        return response()->json(['success' => true]);
    }

    private function canView($user): bool
    {
        return ($user?->can('refer-to-icu') ?? false)
            || ($user?->can('edit-icus') ?? false)
            || ($user?->can('delete-icus') ?? false);
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
