<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

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

        Blade::if('hasRole', function (string|array $role): bool {
            return auth()->check() && auth()->user()->hasRole($role);
        });

        $this->app->booted(function (): void {
            Livewire::component('wirechat.new.chat', \App\Livewire\Wirechat\New\Chat::class);
            Livewire::component('wirechat.new.group', \App\Livewire\Wirechat\New\Group::class);
            Livewire::component('wirechat.chat.group.add-members', \App\Livewire\Wirechat\Chat\Group\AddMembers::class);
        });
    }
}
