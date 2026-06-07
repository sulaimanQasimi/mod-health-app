<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\Hospitalization;
use Illuminate\Http\JsonResponse;

class HospitalizationController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->hospitalization()
            ->with(['room:id,name', 'bed:id,number'])
            ->latest()
            ->get()
            ->map(fn (Hospitalization $item) => [
                'id' => $item->id,
                'description' => $item->description,
                'room_name' => $item->room?->name,
                'bed_number' => $item->bed?->number,
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d') : null,
                'urls' => ['edit' => route('hospitalizations.edit', $item->id)],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('patient-hospitalization'),
            'edit' => $user->can('edit-hospitalizations'),
            'delete' => $user->can('delete-hospitalizations'),
        ], [
            'urls' => ['store' => route('hospitalizations.store')],
        ]);
    }

    public function destroy(Appointment $appointment, Hospitalization $hospitalization): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-hospitalizations'), 403);
        abort_unless((int) $hospitalization->appointment_id === (int) $appointment->id, 404);
        $hospitalization->delete();

        return response()->json(['success' => true]);
    }
}
