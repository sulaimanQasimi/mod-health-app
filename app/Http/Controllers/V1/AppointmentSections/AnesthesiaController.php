<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Anesthesia;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class AnesthesiaController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->anesthesias()
            ->with(['operationType:id,name', 'patient:id,name'])
            ->latest()
            ->get()
            ->map(fn (Anesthesia $item) => [
                'id' => $item->id,
                'operation_type' => $item->operationType?->name,
                'patient_name' => $item->patient?->name,
                'status' => $item->status,
                'date' => $item->date ? verta($item->date)->format('Y-m-d') : null,
                'urls' => [
                    'show' => route('react.anesthesias.show', $item),
                    'edit' => route('react.anesthesias.edit', $item),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('refer-to-anesthesia'),
            'edit' => $user->can('edit-anesthesias'),
            'delete' => $user->can('delete-anesthesias'),
        ], [
            'urls' => ['store' => route('anesthesias.store')],
        ]);
    }

    public function destroy(Appointment $appointment, Anesthesia $anesthesia): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        abort_unless(request()->user()->can('delete-anesthesias'), 403);
        abort_unless((int) $anesthesia->appointment_id === (int) $appointment->id, 404);
        $anesthesia->delete();

        return response()->json(['success' => true]);
    }
}
