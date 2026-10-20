<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->hasRole('super_admin');
    }
}
