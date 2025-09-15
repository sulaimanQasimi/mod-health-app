<?php

namespace App\Policies;

use App\Models\MedicationAdministrationRecord;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicationAdministrationRecordPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-medication-administration-records');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, MedicationAdministrationRecord $medicationAdministrationRecord): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-medication-administration-records');
    }

    /**
     * Determine whether the user can create models.
     * Only users with nurse role or permission can create MARs.
     */
    public function create(User $user): bool
    {
        // Check if user has nurse role or is a nurse
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse']) || 
            $user->hasPermissionTo('create-medication-administration-records')) {
            return true;
        }

        // Check if user has a nurse profile
        if ($user->nurse) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, MedicationAdministrationRecord $medicationAdministrationRecord): bool
    {
        // Super admin and admin can always update
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // HR can update
        if ($user->hasRole('hr') || $user->hasPermissionTo('edit-medication-administration-records')) {
            return true;
        }

        // Nurse can update if they are the one who created it or if they have permission
        if ($user->hasRole('nurse') || $user->hasPermissionTo('edit-medication-administration-records')) {
            return true;
        }

        // Check if user has a nurse profile and can update
        if ($user->nurse && $user->hasPermissionTo('edit-medication-administration-records')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, MedicationAdministrationRecord $medicationAdministrationRecord): bool
    {
        // Super admin and admin can always delete
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // HR can delete
        if ($user->hasRole('hr') || $user->hasPermissionTo('delete-medication-administration-records')) {
            return true;
        }

        // Nurse can delete if they have permission
        if ($user->hasRole('nurse') || $user->hasPermissionTo('delete-medication-administration-records')) {
            return true;
        }

        // Check if user has a nurse profile and can delete
        if ($user->nurse && $user->hasPermissionTo('delete-medication-administration-records')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, MedicationAdministrationRecord $medicationAdministrationRecord): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('restore-medication-administration-records');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, MedicationAdministrationRecord $medicationAdministrationRecord): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('force-delete-medication-administration-records');
    }
}
