<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Department;
use App\Models\Room;
use App\Models\UnderReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnderReviewController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canOpenUnderReview(request()->user()), 403);

        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'default_department_id' => $appointment->department_id,
                'departments' => Department::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name']),
                'rooms' => Room::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name', 'department_id']),
                'beds' => Bed::query()
                    ->when($branchId, fn ($q) => $q->whereHas('room', fn ($room) => $room->where('branch_id', $branchId)))
                    ->orderBy('number')
                    ->get(['id', 'number', 'room_id', 'is_occupied']),
            ],
        ]);
    }

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        if (! $this->canOpenUnderReview($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $items = $appointment->under_reviews()
            ->visibleForAuthUserDepartment()
            ->with(['room:id,name', 'bed:id,number', 'department:id,name'])
            ->latest()
            ->get()
            ->map(fn (UnderReview $item) => [
                'id' => $item->id,
                'reason' => $item->reason,
                'remarks' => $item->remarks,
                'department_name' => $item->department?->name,
                'room_name' => $item->room?->name,
                'bed_number' => $item->bed?->number,
                'is_active' => ! (bool) $item->is_discharged,
                'urls' => [
                    'show' => route('react.under-reviews.show', $item->id),
                    'edit' => route('react.under-reviews.edit', $item->id),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => $this->canMutateAppointment($appointment) && $user->can('patient-under-review'),
            'edit' => $this->canMutateAppointment($appointment) && $user->can('edit-under-reviews'),
            'delete' => $this->canMutateAppointment($appointment) && $user->can('delete-under-reviews'),
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canOpenUnderReview($request->user()), 403);
        abort_unless($this->canMutateAppointment($appointment) && $request->user()->can('patient-under-review'), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        $room = Room::findOrFail($validated['room_id']);
        abort_unless(
            $room->department_id === null || (int) $room->department_id === (int) $validated['department_id'],
            422
        );

        $bed = Bed::findOrFail($validated['bed_id']);
        abort_if((bool) $bed->is_occupied, 422);
        abort_unless((int) $bed->room_id === (int) $validated['room_id'], 422);

        $bed->update(['is_occupied' => true]);

        UnderReview::create([
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'],
            'department_id' => $validated['department_id'],
            'room_id' => $validated['room_id'],
            'bed_id' => $validated['bed_id'],
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $appointment->doctor_id,
            'branch_id' => $appointment->branch_id ?? $request->user()->branch_id,
            'is_discharged' => 0,
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(Appointment $appointment, UnderReview $underReview): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-under-reviews'), 403);
        abort_unless((int) $underReview->appointment_id === (int) $appointment->id, 404);
        abort_unless($underReview->userCanView(request()->user()), 404);
        $underReview->delete();

        return response()->json(['success' => true]);
    }

    private function canOpenUnderReview($user): bool
    {
        return ($user?->can('show-under-review-menu') ?? false)
            || ($user?->can('patient-under-review') ?? false);
    }
}
