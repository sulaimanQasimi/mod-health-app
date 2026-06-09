<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Jobs\SendNewHospitalizationNotification;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HospitalizationController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function meta(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canOpenHospitalization(request()->user()), 403);

        $branchId = $appointment->branch_id ?? request()->user()->branch_id;

        return response()->json([
            'success' => true,
            'data' => [
                'rooms' => Room::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name']),
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

        if (! $this->canOpenHospitalization($user)) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $items = $appointment->hospitalization()
            ->with(['room:id,name', 'bed:id,number'])
            ->latest()
            ->get()
            ->map(fn (Hospitalization $item) => [
                'id' => $item->id,
                'reason' => $item->reason,
                'remarks' => $item->remarks,
                'room_name' => $item->room?->name,
                'bed_number' => $item->bed?->number,
                'is_active' => ! (bool) $item->is_discharged,
                'urls' => [
                    'show' => route('hospitalizations.show', $item->id),
                    'edit' => route('hospitalizations.edit', $item->id),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'view' => true,
            'create' => ! $appointment->is_completed && $user->can('patient-hospitalization'),
            'edit' => $user->can('edit-hospitalizations'),
            'delete' => $user->can('delete-hospitalizations'),
        ]);
    }

    public function store(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless($this->canOpenHospitalization($request->user()), 403);
        abort_unless(! $appointment->is_completed && $request->user()->can('patient-hospitalization'), 403);

        $validated = $request->validate([
            'reason' => 'required|string',
            'remarks' => 'required|string',
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        $bed = Bed::findOrFail($validated['bed_id']);
        abort_if((bool) $bed->is_occupied, 422);

        $bed->update(['is_occupied' => true]);

        $hospitalization = Hospitalization::create([
            'reason' => $validated['reason'],
            'remarks' => $validated['remarks'],
            'room_id' => $validated['room_id'],
            'bed_id' => $validated['bed_id'],
            'patient_id' => $appointment->patient_id,
            'appointment_id' => $appointment->id,
            'branch_id' => $appointment->branch_id ?? $request->user()->branch_id,
            'is_discharged' => 0,
            'food_type_id' => json_encode([]),
        ]);

        SendNewHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);

        return response()->json(['success' => true]);
    }

    public function destroy(Appointment $appointment, Hospitalization $hospitalization): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-hospitalizations'), 403);
        abort_unless((int) $hospitalization->appointment_id === (int) $appointment->id, 404);
        $hospitalization->delete();

        return response()->json(['success' => true]);
    }

    private function canOpenHospitalization($user): bool
    {
        return $user?->can('show-hospitalizations-menu') ?? false;
    }
}
