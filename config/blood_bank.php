<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Low stock alert
    |--------------------------------------------------------------------------
    | Available units below this count (per group + RH + component row) trigger
    | a low-stock highlight on the blood bank dashboard.
    */
    'low_stock_threshold' => (int) env('BLOOD_BANK_LOW_STOCK_THRESHOLD', 5),

    /*
    |--------------------------------------------------------------------------
    | Expiry alert windows (days)
    |--------------------------------------------------------------------------
    */
    'expiry_critical_days' => (int) env('BLOOD_BANK_EXPIRY_CRITICAL_DAYS', 3),
    'expiry_warning_days' => (int) env('BLOOD_BANK_EXPIRY_WARNING_DAYS', 7),

];
