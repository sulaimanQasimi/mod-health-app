<?php

namespace App\Policies;

use App\Models\NursingAssessment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class NursingAssessmentPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_nursing_assessment') || 
               $user->hasRole(['admin', 'super_admin', 'nurse', 'doctor']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NursingAssessment $nursingAssessment): bool
    {
        return $user->hasPermissionTo('view_nursing_assessment') || 
               $user->hasRole(['admin', 'super_admin', 'nurse', 'doctor']) ||
               $user->id === $nursingAssessment->created_by;
    }

    /**
     * Determine whether the user can create models.
     * Only users with nurse role or permission can create nursing assessments.
     */
    public function create(User $user): bool
    {
        // Check if user has nurse role or is a nurse
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse']) || 
            $user->hasPermissionTo('create_nursing_assessment')) {
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
    public function update(User $user, NursingAssessment $nursingAssessment): bool
    {
        // Super admin and admin can always update
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // HR can update
        if ($user->hasRole('hr') || $user->hasPermissionTo('edit_nursing_assessment')) {
            return true;
        }

        // Nurse can update if they are the one who created it or if they have permission
        if ($user->hasRole('nurse') || $user->hasPermissionTo('edit_nursing_assessment')) {
            return true;
        }

        // Check if user has a nurse profile and can update
        if ($user->nurse && $user->hasPermissionTo('edit_nursing_assessment')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NursingAssessment $nursingAssessment): bool
    {
        // Super admin and admin can always delete
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        // HR can delete
        if ($user->hasRole('hr') || $user->hasPermissionTo('delete_nursing_assessment')) {
            return true;
        }

        // Nurse can delete if they have permission
        if ($user->hasRole('nurse') || $user->hasPermissionTo('delete_nursing_assessment')) {
            return true;
        }

        // Check if user has a nurse profile and can delete
        if ($user->nurse && $user->hasPermissionTo('delete_nursing_assessment')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NursingAssessment $nursingAssessment): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('restore_nursing_assessment');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NursingAssessment $nursingAssessment): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('force_delete_nursing_assessment');
    }
}
