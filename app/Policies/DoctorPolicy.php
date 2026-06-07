<?php

namespace App\Policies;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DoctorPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('view-doctors');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Doctor $doctor): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('view-doctors');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('create-doctors');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Doctor $doctor): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('edit-doctors');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Doctor $doctor): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('delete-doctors');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Doctor $doctor): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('restore-doctors');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Doctor $doctor): bool
    {
        return $user->hasRole(['super_admin', 'admin', 'hr']) || 
               $user->hasPermissionTo('force-delete-doctors');
    }

    public function toggleStatus(User $user, Doctor $doctor): bool
    {
        return $this->update($user, $doctor);
    }
}
