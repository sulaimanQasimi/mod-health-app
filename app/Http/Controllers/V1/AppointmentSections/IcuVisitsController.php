<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class IcuVisitsController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = collect();
        $appointment->load(['icu.visits.doctor']);

        foreach ($appointment->icu as $icu) {
            foreach ($icu->visits as $visit) {
                $items->push([
                    'id' => $visit->id,
                    'description' => $visit->description,
                    'doctor_name' => $visit->doctor?->name,
                    'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y-m-d') : null,
                    'bp' => $visit->bp,
                    'pr' => $visit->pr,
                    'spo2' => $visit->spo2,
                ]);
            }
        }

        return $this->sectionIndexResponse($items->values()->all(), $appointment, []);
    }
}
