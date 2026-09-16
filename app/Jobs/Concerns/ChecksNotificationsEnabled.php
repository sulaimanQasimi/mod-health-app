<?php

namespace App\Jobs\Concerns;

trait ChecksNotificationsEnabled
{
    protected function notificationsDisabled(): bool
    {
        return ! config('notifications.enabled', false);
    }
}
