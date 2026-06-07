<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class HospitalizationCheckupController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = collect();
        $appointment->load(['hospitalization.labs.labType', 'hospitalization.labs.results']);

        foreach ($appointment->hospitalization as $hospitalization) {
            foreach ($hospitalization->labs as $lab) {
                $items->push([
                    'id' => $lab->id,
                    'test_name' => $lab->labType?->name,
                    'status' => $lab->status,
                    'result' => $lab->results?->first()?->result,
                ]);
            }
        }

        return $this->sectionIndexResponse($items->values()->all(), $appointment, []);
    }
}
