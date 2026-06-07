<?php

namespace App\Policies;

use App\Models\ICUProcedureType;
use App\Models\User;

class ICUProcedureTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-add-icu-procedures-menu');
    }

    public function view(User $user, ICUProcedureType $type): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-procedure-types');
    }

    public function update(User $user, ICUProcedureType $type): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-procedure-types');
    }

    public function delete(User $user, ICUProcedureType $type): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-procedure-types');
    }
}
