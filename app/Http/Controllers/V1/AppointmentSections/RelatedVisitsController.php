<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class RelatedVisitsController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = collect();
        $appointment->load(['under_reviews.visits.doctor']);

        foreach ($appointment->under_reviews as $underReview) {
            foreach ($underReview->visits as $visit) {
                $items->push([
                    'id' => $visit->id,
                    'description' => $visit->description,
                    'doctor_name' => $visit->doctor?->name,
                    'visit_date' => $visit->created_at ? verta($visit->created_at)->format('Y-m-d') : null,
                ]);
            }
        }

        return $this->sectionIndexResponse($items->values()->all(), $appointment, []);
    }
}
