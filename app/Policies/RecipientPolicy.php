<?php

namespace App\Policies;

use App\Models\Recipient;
use App\Models\User;

class RecipientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-recipients-menu');
    }

    public function view(User $user, Recipient $recipient): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-recipients');
    }

    public function update(User $user, Recipient $recipient): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-recipients');
    }

    public function delete(User $user, Recipient $recipient): bool
    {
        return $this->update($user, $recipient);
    }
}
