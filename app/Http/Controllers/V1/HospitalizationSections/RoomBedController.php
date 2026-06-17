<?php

namespace App\Http\Controllers\V1\HospitalizationSections;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomBedController extends Controller
{
    public function meta(Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $hospitalization->loadMissing([
            'appointment:id,department_id',
            'room:id,name,department_id',
            'bed:id,number,room_id,is_occupied',
        ]);

        $departmentId = $this->currentDepartmentId($hospitalization);
        $branchId = $hospitalization->branch_id ?? request()->user()->branch_id;

        $rooms = Room::query()
            ->where('branch_id', $branchId)
            ->when(
                $departmentId,
                fn ($query) => $query->where('department_id', $departmentId)
            )
            ->whereHas('beds')
            ->orderBy('name')
            ->get(['id', 'name', 'department_id'])
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'department_id' => $room->department_id,
            ])
            ->values()
            ->all();

        $roomIds = collect($rooms)->pluck('id')->all();

        $beds = Bed::query()
            ->whereIn('room_id', $roomIds)
            ->orderBy('number')
            ->get(['id', 'number', 'room_id', 'is_occupied'])
            ->map(fn (Bed $bed) => [
                'id' => $bed->id,
                'number' => $bed->number,
                'room_id' => $bed->room_id,
                'is_occupied' => (bool) $bed->is_occupied,
            ])
            ->values()
            ->all();

        return response()->json([
            'success' => true,
            'data' => [
                'current_room_id' => $hospitalization->room_id,
                'current_bed_id' => $hospitalization->bed_id,
                'current_room_name' => $hospitalization->room?->name,
                'current_bed_number' => $hospitalization->bed?->number,
                'department_id' => $departmentId,
                'rooms' => $rooms,
                'beds' => $beds,
            ],
        ]);
    }

    public function update(Request $request, Hospitalization $hospitalization): JsonResponse
    {
        $this->ensureAccessible($hospitalization);
        abort_if((bool) $hospitalization->is_discharged, 403);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_id' => 'required|exists:beds,id',
        ]);

        $hospitalization->loadMissing('appointment:id,department_id');
        $currentDeptId = $this->currentDepartmentId($hospitalization);

        $newRoom = Room::query()
            ->where('branch_id', $hospitalization->branch_id)
            ->findOrFail($validated['room_id']);

        $newBed = Bed::findOrFail($validated['bed_id']);

        if ((int) $newBed->room_id !== (int) $newRoom->id) {
            return response()->json([
                'success' => false,
                'message' => localize('global.bed_must_belong_to_selected_room'),
            ], 422);
        }

        $bedTaken = Hospitalization::query()
            ->where('bed_id', $validated['bed_id'])
            ->where('is_discharged', 0)
            ->where('id', '!=', $hospitalization->id)
            ->exists();

        if ($bedTaken) {
            return response()->json([
                'success' => false,
                'message' => localize('global.selected_bed_already_occupied'),
            ], 422);
        }

        if ($currentDeptId && (int) $newRoom->department_id !== (int) $currentDeptId) {
            return response()->json([
                'success' => false,
                'message' => localize('global.room_must_match_current_department'),
            ], 422);
        }

        $oldBed = $hospitalization->bed_id ? Bed::find($hospitalization->bed_id) : null;

        $hospitalization->update([
            'room_id' => $validated['room_id'],
            'bed_id' => $validated['bed_id'],
        ]);

        if ($oldBed && (int) $oldBed->id !== (int) $newBed->id) {
            $oldBed->update(['is_occupied' => false]);
        }

        $newBed->update(['is_occupied' => true]);

        $hospitalization->load(['room:id,name', 'bed:id,number']);

        return response()->json([
            'success' => true,
            'message' => localize('global.room_and_bed_updated_successfully'),
            'data' => [
                'room_name' => $hospitalization->room?->name,
                'bed_number' => $hospitalization->bed?->number,
            ],
        ]);
    }

    private function ensureAccessible(Hospitalization $hospitalization): void
    {
        abort_unless(request()->user()?->can('show-hospitalizations-menu'), 403);
        abort_unless($hospitalization->userCanView(request()->user()), 404);
    }

    private function currentDepartmentId(Hospitalization $hospitalization): ?int
    {
        $departmentId = $hospitalization->appointment?->department_id ?? $hospitalization->department_id;

        return $departmentId ? (int) $departmentId : null;
    }
}
