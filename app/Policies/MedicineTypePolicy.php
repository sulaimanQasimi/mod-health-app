<?php

namespace App\Policies;

use App\Models\MedicineType;
use App\Models\User;

class MedicineTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-medicine-types-menu');
    }

    public function view(User $user, MedicineType $medicineType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-medicine-types');
    }

    public function update(User $user, MedicineType $medicineType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-medicine-types');
    }

    public function delete(User $user, MedicineType $medicineType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-medicine-types');
    }
}
