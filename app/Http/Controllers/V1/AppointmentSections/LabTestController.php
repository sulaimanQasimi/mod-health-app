<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class LabTestController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        $items = $appointment->patientTestRegistrations()
            ->with(['labType:id,name', 'doctor:id,name', 'assignedSection:id,name'])
            ->latest()
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'test_name' => $item->labType?->name,
                'doctor_name' => $item->doctor?->name,
                'section_name' => $item->assignedSection?->name,
                'status' => $item->status,
                'created_at' => $item->created_at ? verta($item->created_at)->format('Y-m-d H:i') : null,
            ])
            ->values()
            ->all();

        return $this->sectionIndexResponse($items, $appointment, [
            'create' => ! $appointment->is_completed && $user->can('register-patient-tests'),
        ], [
            'urls' => [
                'store' => url("/lab-test-registration-ajax/store/appointment/{$appointment->id}"),
            ],
        ]);
    }
}
