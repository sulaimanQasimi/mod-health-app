<?php

namespace App\Policies;

use App\Models\OperationType;
use App\Models\User;

class OperationTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-operation-types-menu');
    }

    public function view(User $user, OperationType $operationType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-operation-types');
    }

    public function update(User $user, OperationType $operationType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-operation-types');
    }

    public function delete(User $user, OperationType $operationType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-operation-types');
    }
}
