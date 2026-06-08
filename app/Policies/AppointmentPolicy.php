<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-information-menu')
            || $user->hasPermissionTo('show-my-visits-menu');
    }

    public function viewMyVisits(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-my-visits-menu');
    }

    public function accept(User $user, Appointment $appointment): bool
    {
        return $this->viewMyVisits($user)
            && $this->belongsToUserBranch($user, $appointment)
            && ! $appointment->processed_by;
    }

    public function changeDepartment(User $user, Appointment $appointment): bool
    {
        return $this->viewMyVisits($user)
            && $this->belongsToUserBranch($user, $appointment);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $this->belongsToUserBranch($user, $appointment)
            && $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->branch_id
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('create-appointment')
            );
    }

    public function updateStatus(User $user, Appointment $appointment): bool
    {
        return $this->belongsToUserBranch($user, $appointment)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('update-appointment-status')
            );
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $this->belongsToUserBranch($user, $appointment)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('edit-appointments')
            );
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $this->belongsToUserBranch($user, $appointment)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('delete-appointments')
            );
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $this->belongsToUserBranch($user, $appointment)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('restore-appointments')
            );
    }

    private function belongsToUserBranch(User $user, Appointment $appointment): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $appointment->branch_id;
    }
}
