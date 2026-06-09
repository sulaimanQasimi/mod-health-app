<?php

namespace App\Policies;

use App\Models\Hospitalization;
use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-rooms-menu');
    }

    public function view(User $user, Room $room): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('show-rooms-menu')
            || $user->hasPermissionTo('show-rooms');
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('create-rooms');
    }

    public function update(User $user, Room $room): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('edit-rooms');
    }

    public function delete(User $user, Room $room): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->hasPermissionTo('delete-rooms');
    }

    /**
     * Access the hospitalization room-management page (list rooms/beds).
     */
    public function manageAny(User $user): bool
    {
        if (! $user->hasPermissionTo('manage-hospitalization-rooms')) {
            return false;
        }

        if (Hospitalization::userBypassesDepartmentScope($user)) {
            return true;
        }

        return $user->department_id !== null;
    }

    /**
     * View/manage a specific room in hospitalization room-management.
     */
    public function manage(User $user, Room $room): bool
    {
        if (! $this->manageAny($user)) {
            return false;
        }

        if ((int) $room->branch_id !== (int) $user->branch_id) {
            return false;
        }

        if (Hospitalization::userBypassesDepartmentScope($user)) {
            return true;
        }

        return $room->department_id !== null
            && (int) $room->department_id === (int) $user->department_id;
    }
}
