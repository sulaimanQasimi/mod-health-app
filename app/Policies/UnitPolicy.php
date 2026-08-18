<?php

namespace App\Policies;

use App\Models\Unit;
use App\Models\User;

class UnitPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-units-menu');
    }

    public function view(User $user, Unit $unit): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-units');
    }

    public function update(User $user, Unit $unit): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-units');
    }

    public function delete(User $user, Unit $unit): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-units');
    }
}
