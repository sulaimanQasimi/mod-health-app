<?php

namespace App\Policies;

use App\Models\PatientTestRegistration;
use App\Models\User;

class LaboratoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-labs-menu');
    }

    public function viewTools(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-laboratory-menu');
    }

    public function manageResults(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('manage-test-results');
    }

    public function view(User $user, PatientTestRegistration $registration): bool
    {
        return (int) $registration->branch_id === (int) $user->branch_id
            && $this->viewAny($user);
    }

    public function accept(User $user, PatientTestRegistration $registration): bool
    {
        return $this->manageResults($user)
            && $this->view($user, $registration)
            && $registration->status === 'pending'
            && ! $registration->assigned_to;
    }

    public function updateStatus(User $user, PatientTestRegistration $registration): bool
    {
        return $this->manageResults($user) && $this->view($user, $registration);
    }

    public function fillResults(User $user, PatientTestRegistration $registration): bool
    {
        if (! $this->manageResults($user) || ! $this->view($user, $registration)) {
            return false;
        }

        if ($user->hasRole(['super_admin', 'admin']) || $user->can('view-all-sections')) {
            return true;
        }

        if ($registration->assigned_to && (int) $registration->assigned_to !== (int) $user->id) {
            return false;
        }

        if ($registration->status === 'completed'
            && (int) $registration->completed_by !== (int) $user->id
            && (int) $registration->assigned_to !== (int) $user->id) {
            return false;
        }

        return true;
    }
}
