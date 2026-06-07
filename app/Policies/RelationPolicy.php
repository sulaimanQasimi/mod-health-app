<?php

namespace App\Policies;

use App\Models\Relation;
use App\Models\User;

class RelationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-relations-menu');
    }

    public function view(User $user, Relation $relation): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-relations');
    }

    public function update(User $user, Relation $relation): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-relations');
    }

    public function delete(User $user, Relation $relation): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-relations');
    }
}
