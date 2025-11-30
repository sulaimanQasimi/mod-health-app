<?php

namespace SulaimanQasimi\Router;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TailwindFormsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tailwind-forms.php', 'tailwind-forms'
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../config/tailwind-forms.php' => config_path('tailwind-forms.php'),
        ], 'config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/tailwind-forms'),
        ], 'views');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/js' => public_path('vendor/tailwind-forms'),
        ], 'assets');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'tailwind-forms');

        // Register Blade components
        $this->registerBladeComponents();
    }

    /**
     * Register Blade components
     */
    protected function registerBladeComponents(): void
    {
        Blade::componentNamespace('SulaimanQasimi\\Router\\View\\Components', 'tailwind-forms');
    }
}
