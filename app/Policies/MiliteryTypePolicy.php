<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MiliteryType;

class MiliteryTypePolicy
{
    public function viewAny(User $user)
    {
        return $user->can('show-militery-types');
    }

    /**
     * Determine whether the user can view the military type.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MiliteryType  $militeryType
     * @return bool
     */
    public function view(User $user, MiliteryType $militeryType)
    {
        return $user->can('show-militery-types');
    }

    /**
     * Determine whether the user can create military types.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('create-militery-types');
    }

    /**
     * Determine whether the user can update military types.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MiliteryType  $militeryType
     * @return bool
     */
    public function update(User $user, MiliteryType $militeryType)
    {
        return $user->can('edit-militery-types');
    }

    /**
     * Determine whether the user can delete military types.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\MiliteryType  $militeryType
     * @return bool
     */
    public function delete(User $user, MiliteryType $militeryType)
    {
        return $user->can('delete-militery-types');
    }
}
