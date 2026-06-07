<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-users-menu');
    }

    public function view(User $user, User $model): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-users');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-users');
    }

    public function toggleStatus(User $user, User $model): bool
    {
        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('deactivate-users');
    }
}
