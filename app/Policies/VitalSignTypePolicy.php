<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VitalSignType;
use Illuminate\Auth\Access\Response;

class VitalSignTypePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-sign-types');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, VitalSignType $vitalSignType): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr', 'nurse', 'doctor']) || 
               $user->hasPermissionTo('view-vital-sign-types');
    }

    /**
     * Determine whether the user can create models.
     * Only admins and super admins can create vital sign types.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('create-vital-sign-types');
    }

    /**
     * Determine whether the user can update the model.
     * Only admins and super admins can update vital sign types.
     */
    public function update(User $user, VitalSignType $vitalSignType): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('update-vital-sign-types');
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins and super admins can delete vital sign types.
     */
    public function delete(User $user, VitalSignType $vitalSignType): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('delete-vital-sign-types');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, VitalSignType $vitalSignType): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || 
               $user->hasPermissionTo('restore-vital-sign-types');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, VitalSignType $vitalSignType): bool
    {
        return $user->hasRole(['super_admin']) || 
               $user->hasPermissionTo('force-delete-vital-sign-types');
    }
}
