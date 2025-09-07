<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Backup\BackupDestination\Backup;

class BackupPolicy
{
    /**
     * Determine whether the user can view any backups.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can view the backup.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Backup\BackupDestination\Backup  $backup
     * @return bool
     */
    public function view(User $user, Backup $backup)
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can create backups.
     *
     * @param  \App\Models\User  $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can download backups.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Backup\BackupDestination\Backup  $backup
     * @return bool
     */
    public function download(User $user, Backup $backup)
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Determine whether the user can delete backups.
     *
     * @param  \App\Models\User  $user
     * @param  \Spatie\Backup\BackupDestination\Backup  $backup
     * @return bool
     */
    public function delete(User $user, Backup $backup)
    {
        return $user->hasRole('super_admin');
    }
}
