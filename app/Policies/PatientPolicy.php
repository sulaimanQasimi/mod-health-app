<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->hasReceptionAccess($user)
            || $user->hasPermissionTo('view-patients');
    }

    public function view(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $this->hasReceptionAccess($user)
                || $user->hasPermissionTo('view-patients')
            );
    }

    public function create(User $user): bool
    {
        return $user->branch_id
            && (
                $this->hasReceptionAccess($user)
                || $user->hasPermissionTo('create-patients')
            );
    }

    public function update(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('edit-patients')
            );
    }

    public function delete(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('delete-patients')
            );
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('restore-patients')
            );
    }

    public function forceDelete(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin'])
                || $user->hasPermissionTo('force-delete-patients')
            );
    }

    public function printCard(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('print-patient-card')
            );
    }

    public function uploadImage(User $user, Patient $patient): bool
    {
        return $this->belongsToUserBranch($user, $patient)
            && (
                $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('upload-patient-image')
            );
    }

    private function hasReceptionAccess(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-information-menu');
    }

    private function belongsToUserBranch(User $user, Patient $patient): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $patient->branch_id;
    }
}
