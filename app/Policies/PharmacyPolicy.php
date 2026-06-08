<?php

namespace App\Policies;

use App\Models\Pharmacy;
use App\Models\User;

class PharmacyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.index')
            || $user->hasPermissionTo('show-pharmacy-menu');
    }

    public function view(User $user, Pharmacy $pharmacy): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.show');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.create');
    }

    public function update(User $user, Pharmacy $pharmacy): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.edit');
    }

    public function delete(User $user, Pharmacy $pharmacy): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.delete');
    }

    public function manageUsers(User $user, Pharmacy $pharmacy): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('pharmacy.manage_users');
    }
}
