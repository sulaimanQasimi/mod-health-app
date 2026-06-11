<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class OperationController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = $appointment->approved_anesthesias()
            ->where('is_referred_to_operation', true)
            ->with(['operationType:id,name', 'patient:id,name'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'operation_type' => $item->operationType?->name,
                'patient_name' => $item->patient?->name,
                'status' => $item->status,
                'date' => $item->date,
                'urls' => [
                    'show' => route('react.operations.show', $item),
                ],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, []);
    }
}
