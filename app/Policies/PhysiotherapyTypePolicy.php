<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PhysiotherapyType;

class PhysiotherapyTypePolicy
{
    public function viewAny(User $user)
    {
        return $user->can('show-physiotherapy-types');
    }

    /**
     * Determine whether the user can view the physiotherapy type.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyType  $physiotherapyType
     * @return bool
     */
    public function view(User $user, PhysiotherapyType $physiotherapyType)
    {
        return $user->can('show-physiotherapy-types');
    }

    /**
     * Determine whether the user can create physiotherapy types.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('create-physiotherapy-types');
    }

    /**
     * Determine whether the user can update physiotherapy types.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyType  $physiotherapyType
     * @return bool
     */
    public function update(User $user, PhysiotherapyType $physiotherapyType)
    {
        return $user->can('edit-physiotherapy-types');
    }

    /**
     * Determine whether the user can delete physiotherapy types.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyType  $physiotherapyType
     * @return bool
     */
    public function delete(User $user, PhysiotherapyType $physiotherapyType)
    {
        return $user->can('delete-physiotherapy-types');
    }
}
