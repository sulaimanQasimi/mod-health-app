<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use App\Models\ICU;
use Illuminate\Http\JsonResponse;

class IcuController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->icu()
            ->with(['patient:id,name', 'hospitalization.room:id,name', 'hospitalization.bed:id,number'])
            ->latest()
            ->get()
            ->map(fn (ICU $item) => [
                'id' => $item->id,
                'patient_name' => $item->patient?->name,
                'description' => $item->description,
                'room_name' => $item->hospitalization?->room?->name,
                'bed_number' => $item->hospitalization?->bed?->number,
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d') : null,
                'urls' => ['edit' => route('icus.edit', $item->id)],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('refer-to-icu'),
            'edit' => $user->can('edit-icus'),
            'delete' => $user->can('delete-icus'),
        ], [
            'urls' => ['store' => route('icus.store')],
        ]);
    }

    public function destroy(Appointment $appointment, ICU $icu): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-icus'), 403);
        abort_unless((int) $icu->appointment_id === (int) $appointment->id, 404);
        $icu->delete();

        return response()->json(['success' => true]);
    }
}
