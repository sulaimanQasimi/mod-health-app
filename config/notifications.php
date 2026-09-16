<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable application notifications
    |--------------------------------------------------------------------------
    |
    | When false, Laravel notifications (database/mail/etc.) are not sent and
    | Send*Notification queue jobs exit immediately without work.
    |
    */
    'enabled' => (bool) env('NOTIFICATIONS_ENABLED', false),

];
