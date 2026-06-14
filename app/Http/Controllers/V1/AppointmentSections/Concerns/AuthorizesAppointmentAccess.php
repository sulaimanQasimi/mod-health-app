<?php

namespace App\Http\Controllers\V1\AppointmentSections\Concerns;

use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\User;

trait AuthorizesAppointmentAccess
{
    protected function appointmentMutationsLocked(Appointment $appointment): bool
    {
        if ((bool) $appointment->is_completed) {
            return true;
        }

        return Hospitalization::query()
            ->where('appointment_id', $appointment->id)
            ->where('is_discharged', 1)
            ->exists();
    }

    protected function canMutateAppointment(Appointment $appointment): bool
    {
        return ! $this->appointmentMutationsLocked($appointment);
    }

    protected function assertAppointmentMutable(Appointment $appointment): void
    {
        abort_if($this->appointmentMutationsLocked($appointment), 403);
    }

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

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, bool>  $permissions
     * @param  array<string, mixed>  $extra
     */
    protected function sectionIndexResponse(
        array $items,
        Appointment $appointment,
        array $permissions,
        array $extra = [],
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => true,
            'data' => array_merge([
                'items' => $items,
                'count' => count($items),
                'meta' => $this->appointmentMeta($appointment),
                'permissions' => $permissions,
            ], $extra),
        ]);
    }
}
