<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VitalSign;
use Illuminate\Auth\Access\Response;

class VitalSignPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-signs');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VitalSign $vitalSign): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-signs');
    }

    /**
     * Determine whether the user can create models.
     * Only nurses and doctors can create vital signs.
     */
    public function create(User $user): bool
    {
        // Check if user has nurse or doctor role
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
            $user->hasPermissionTo('create-vital-signs')) {
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
     * Only nurses and doctors can update vital signs.
     */
    public function update(User $user, VitalSign $vitalSign): bool
    {
        // Check if user has nurse or doctor role
        if ($user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
            $user->hasPermissionTo('update-vital-signs')) {
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
     * Only admins and super admins can delete vital signs.
     */
    public function delete(User $user, VitalSign $vitalSign): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('delete-vital-signs');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VitalSign $vitalSign): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('restore-vital-signs');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VitalSign $vitalSign): bool
    {
        return $user->hasRole(['super_admin']) || 
               $user->hasPermissionTo('force-delete-vital-signs');
    }
}
