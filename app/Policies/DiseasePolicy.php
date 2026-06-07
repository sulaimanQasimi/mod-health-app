<?php

namespace App\Policies;

use App\Models\Disease;
use App\Models\User;

class DiseasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-disease-menu');
    }

    public function view(User $user, Disease $disease): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-diseases');
    }

    public function update(User $user, Disease $disease): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-diseases');
    }

    public function delete(User $user, Disease $disease): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-diseases');
    }
}
