<?php

namespace App\Http\Controllers\V1\AppointmentSections;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\AppointmentSections\Concerns\AuthorizesAppointmentAccess;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class ReferDepartmentController extends Controller
{
    use AuthorizesAppointmentAccess;

    public function index(Appointment $appointment): JsonResponse
    {
        $this->authorizeAppointmentView($appointment);
        $user = request()->user();

        return $this->sectionIndexResponse([], $appointment, [
            'create' => ! $appointment->is_completed && $user->can('refer-to-another-doctor'),
        ], [
            'referral_remarks' => $appointment->refferal_remarks,
            'count' => filled($appointment->refferal_remarks) ? 1 : 0,
            'urls' => ['legacy' => url("/appointments/show/{$appointment->id}")],
        ]);
    }
}
