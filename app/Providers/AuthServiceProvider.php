<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \Spatie\Backup\BackupDestination\Backup::class => \App\Policies\BackupPolicy::class,
        \App\Models\DiabetesChart::class => \App\Policies\DiabetesChartPolicy::class,
        \App\Models\MedicationAdministrationRecord::class => \App\Policies\MedicationAdministrationRecordPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
