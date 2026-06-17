<?php

namespace App\Policies;

use App\Models\RecipientPart;
use App\Models\User;

class RecipientPartPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-recipient-parts-menu');
    }

    public function view(User $user, RecipientPart $recipientPart): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-recipient-parts');
    }

    public function update(User $user, RecipientPart $recipientPart): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-recipient-parts');
    }

    public function delete(User $user, RecipientPart $recipientPart): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-recipient-parts');
    }
}
