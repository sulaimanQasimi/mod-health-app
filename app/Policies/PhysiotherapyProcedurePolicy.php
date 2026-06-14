<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PhysiotherapyProcedure;

class PhysiotherapyProcedurePolicy
{
    public function viewAny(User $user)
    {
        return $user->can('show-physiotherapy-procedures');
    }

    /**
     * Determine whether the user can view their own physiotherapy procedures.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewOwn(User $user)
    {
        return $user->can('show-own-physiotherapy-procedures');
    }

    /**
     * Determine whether the user can view the physiotherapy procedure.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyProcedure  $physiotherapyProcedure
     * @return bool
     */
    public function view(User $user, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        return $user->can('show-physiotherapy-procedures')
            && $this->belongsToUserBranch($user, $physiotherapyProcedure);
    }

    /**
     * Determine whether the user can create physiotherapy procedures.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->can('create-physiotherapy-procedures');
    }

    /**
     * Determine whether the user can update physiotherapy procedures.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyProcedure  $physiotherapyProcedure
     * @return bool
     */
    public function update(User $user, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        return $user->can('edit-physiotherapy-procedures')
            && $this->belongsToUserBranch($user, $physiotherapyProcedure);
    }

    /**
     * Determine whether the user can delete physiotherapy procedures.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PhysiotherapyProcedure  $physiotherapyProcedure
     * @return bool
     */
    public function delete(User $user, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        return $user->can('delete-physiotherapy-procedures')
            && $this->belongsToUserBranch($user, $physiotherapyProcedure);
    }

    private function belongsToUserBranch(User $user, PhysiotherapyProcedure $physiotherapyProcedure): bool
    {
        if (! $user->branch_id) {
            return true;
        }

        $physiotherapyProcedure->loadMissing('appointment:id,branch_id');

        return (int) $physiotherapyProcedure->appointment?->branch_id === (int) $user->branch_id;
    }
}
