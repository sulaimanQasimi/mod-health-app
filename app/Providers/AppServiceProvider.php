<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 for pagination
        \Illuminate\Pagination\Paginator::useBootstrapFive();

        if (config('security.force_https')) {
            URL::forceScheme('https');
        }

        Blade::if('hasRole', function (string|array $role): bool {
            return auth()->check() && auth()->user()->hasRole($role);
        });
    }
}
