<?php

namespace App\Http\Controllers\V1\AppointmentSections\Concerns;

use App\Models\Appointment;
use App\Models\User;

trait AuthorizesAppointmentAccess
{
    protected function authorizeAppointmentView(Appointment $appointment): void
    {
        $this->authorize('view', $appointment);
    }

    /**
     * @return array<string, mixed>
     */
    protected function appointmentMeta(Appointment $appointment): array
    {
        return [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'branch_id' => $appointment->branch_id,
            'department_id' => $appointment->department_id,
            'is_completed' => (bool) $appointment->is_completed,
        ];
    }

    protected function belongsToAppointmentBranch(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $appointment->branch_id;
    }
}
