<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Trusted Hosts (extra patterns)
    |--------------------------------------------------------------------------
    |
    | Comma-separated host patterns in addition to APP_URL subdomains.
    | Example: "health.example.com,*.internal.example.com"
    |
    */
    'trusted_hosts' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_HOSTS', ''))
    ))),

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | "*" trusts all proxies (typical behind nginx/load balancer).
    | Or a comma-separated list of proxy IPs.
    |
    */
    'trusted_proxies' => env('TRUSTED_PROXIES', '*'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS in production
    |--------------------------------------------------------------------------
    */
    'force_https' => filter_var(
        env('FORCE_HTTPS', env('APP_ENV') === 'production'),
        FILTER_VALIDATE_BOOLEAN
    ),

];

