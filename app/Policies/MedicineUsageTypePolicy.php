<?php

namespace App\Policies;

use App\Models\MedicineUsageType;
use App\Models\User;

class MedicineUsageTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-medicine-usage-menu');
    }

    public function view(User $user, MedicineUsageType $medicineUsageType): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-medicines-usage-types');
    }

    public function update(User $user, MedicineUsageType $medicineUsageType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-medicines-usage-types');
    }

    public function delete(User $user, MedicineUsageType $medicineUsageType): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-medicines-usage-types');
    }
}
