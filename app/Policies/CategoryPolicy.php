<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canAccessLabTypes($user);
    }

    public function view(User $user, Category $category): bool
    {
        return $this->canAccessLabTypes($user);
    }

    public function create(User $user): bool
    {
        return $this->canManageLabTypes($user);
    }

    public function update(User $user, Category $category): bool
    {
        return $this->canManageLabTypes($user);
    }

    public function delete(User $user, Category $category): bool
    {
        return $this->canManageLabTypes($user);
    }

    private function canManageLabTypes(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || $user->can('manage-lab-tests');
    }

    private function canAccessLabTypes(User $user): bool
    {
        return $this->canManageLabTypes($user) || $user->can('register-patient-tests');
    }
}
