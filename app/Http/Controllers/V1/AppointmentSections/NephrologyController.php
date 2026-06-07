<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class NephrologyController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $canOpen = $user->can('access-nephrology-registrations')
            && optional($user->doctor)->is_nephrologist;

        if (! $canOpen) {
            return $this->sectionIndexResponse([], $appointment, ['view' => false]);
        }

        $items = $appointment->nephrologyRegistrations()
            ->with(['doctor:id,name', 'disease:id,name'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'ref_no' => $item->ref_no,
                'doctor_name' => $item->doctor?->name,
                'disease_name' => $item->disease?->name,
                'visit_date' => $item->visit_date ? verta($item->visit_date)->format('Y-m-d') : null,
                'status' => $item->status,
                'urls' => ['show' => route('nephrology-registrations.show', $item->id)],
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed,
        ], [
            'urls' => ['store' => route('nephrology-registrations.store', $appointment->id)],
        ]);
    }
}
