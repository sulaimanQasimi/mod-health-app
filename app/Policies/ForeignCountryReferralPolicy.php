<?php

namespace App\Policies;

use App\Models\ForeignCountryReferral;
use App\Models\User;

class ForeignCountryReferralPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->can('view-refer-to-foreign-country');
    }

    public function view(User $user, ForeignCountryReferral $referral): bool
    {
        return $this->viewAny($user) && $this->canAccess($user, $referral);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->can('add-refer-to-foreign-country');
    }

    public function update(User $user, ForeignCountryReferral $referral): bool
    {
        return ($user->hasRole(['super_admin', 'admin']) || $user->can('edit-refer-to-foreign-country'))
            && $this->canAccess($user, $referral);
    }

    public function delete(User $user, ForeignCountryReferral $referral): bool
    {
        return ($user->hasRole(['super_admin', 'admin']) || $user->can('delete-refer-to-foreign-country'))
            && $this->canAccess($user, $referral);
    }

    private function canAccess(User $user, ForeignCountryReferral $referral): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $referral->branch_id;
    }
}
