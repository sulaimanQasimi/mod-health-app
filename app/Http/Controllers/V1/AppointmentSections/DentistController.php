<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class DentistController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);

        $items = $appointment->dentistRegistrations()
            ->with(['dentist:id,name'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'dentist_name' => $item->dentist?->name,
                'visit_date' => $item->visit_date ? verta($item->visit_date)->format('Y-m-d') : null,
                'status' => $item->status,
                'urls' => ['show' => route('dentist-registrations.show', $item->id)],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed,
        ], [
            'urls' => ['store' => url("/dentist-registrations/store/{$appointment->id}")],
        ]);
    }
}
