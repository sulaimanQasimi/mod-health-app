<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-roles-menu');
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->update($user, $role);
    }
}
