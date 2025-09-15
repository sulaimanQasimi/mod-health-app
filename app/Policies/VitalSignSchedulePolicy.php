<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VitalSignSchedule;
use Illuminate\Auth\Access\Response;

class VitalSignSchedulePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-sign-schedules');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VitalSignSchedule $vitalSignSchedule): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-sign-schedules');
    }

    /**
     * Determine whether the user can create models.
     * Only nurses and doctors can create vital sign schedules.
     */
    public function create(User $user): bool
    {
        // Check if user has nurse or doctor role
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
            $user->hasPermissionTo('create-vital-sign-schedules')) {
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
     * Only nurses and doctors can update vital sign schedules.
     */
    public function update(User $user, VitalSignSchedule $vitalSignSchedule): bool
    {
        // Check if user has nurse or doctor role
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
            $user->hasPermissionTo('update-vital-sign-schedules')) {
            return true;
        }

        // Check if user has a nurse profile
        if ($user->nurse) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins and super admins can delete vital sign schedules.
     */
    public function delete(User $user, VitalSignSchedule $vitalSignSchedule): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('delete-vital-sign-schedules');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VitalSignSchedule $vitalSignSchedule): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('restore-vital-sign-schedules');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VitalSignSchedule $vitalSignSchedule): bool
    {
        return $user->hasRole(['super_admin']) || 
               $user->hasPermissionTo('force-delete-vital-sign-schedules');
    }
}
