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

    /*
    |--------------------------------------------------------------------------
    | Request quantity (bags vs volume)
    |--------------------------------------------------------------------------
    | The blood request "quantity" column is stored as a string. Values above
    | max_unit_order_before_volume_assumption are treated as total volume in ml
    | and converted to an approximate bag count using ml_per_bag_for_qty_inference
    | (one crossmatch/reservation per physical bag). Lab blood-check quantity
    | overrides the raw request when present.
    */
    'max_unit_order_before_volume_assumption' => (int) env('BLOOD_BANK_MAX_UNIT_BEFORE_ML_ASSUMPTION', 100),
    'ml_per_bag_for_qty_inference' => (int) env('BLOOD_BANK_ML_PER_BAG_INFERENCE', 450),

];
