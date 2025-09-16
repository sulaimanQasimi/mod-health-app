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
        \App\Models\VitalSignType::class => \App\Policies\VitalSignTypePolicy::class,
        \App\Models\VitalSign::class => \App\Policies\VitalSignPolicy::class,
        \App\Models\VitalSignSchedule::class => \App\Policies\VitalSignSchedulePolicy::class,
        \App\Models\NutritionCare::class => \App\Policies\NutritionCarePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
