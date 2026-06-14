<?php

namespace App\Policies;

use App\Models\UnderReview;
use App\Models\User;

class UnderReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('show-under-review-menu');
    }

    public function view(User $user, UnderReview $underReview): bool
    {
        return $underReview->userCanView($user)
            && $user->can('show-under-review-menu');
    }

    public function accept(User $user, UnderReview $underReview): bool
    {
        return $underReview->userCanView($user)
            && $user->can('show-under-review-menu')
            && ! (bool) $underReview->is_discharged
            && ! $underReview->processed_by;
    }

    public function complete(User $user, UnderReview $underReview): bool
    {
        if (! $underReview->userCanView($user) || (bool) $underReview->is_discharged) {
            return false;
        }

        if ($user->can('edit-under-reviews')) {
            return true;
        }

        return $user->can('show-under-review-menu')
            && (int) $underReview->processed_by === (int) $user->id;
    }
}
