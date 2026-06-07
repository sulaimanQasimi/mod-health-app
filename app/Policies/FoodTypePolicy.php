<?php

namespace App\Policies;

use App\Models\FoodType;
use App\Models\User;

class FoodTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-food-types-menu');
    }

    public function view(User $user, FoodType $foodType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-hospitalization-foods');
    }

    public function update(User $user, FoodType $foodType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-hospitalization-foods');
    }

    public function delete(User $user, FoodType $foodType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-hospitalization-foods');
    }
}
