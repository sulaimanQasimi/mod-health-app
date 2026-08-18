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
        \App\Models\NursingAssessment::class => \App\Policies\NursingAssessmentPolicy::class,
        \App\Models\Category::class => \App\Policies\CategoryPolicy::class,
        \App\Models\Doctor::class => \App\Policies\DoctorPolicy::class,
        \App\Models\Patient::class => \App\Policies\PatientPolicy::class,
        \App\Models\Appointment::class => \App\Policies\AppointmentPolicy::class,
        \App\Models\UnderReview::class => \App\Policies\UnderReviewPolicy::class,
        \App\Models\User::class => \App\Policies\UserPolicy::class,
        \App\Models\Recipient::class => \App\Policies\RecipientPolicy::class,
        \App\Models\RecipientPart::class => \App\Policies\RecipientPartPolicy::class,
        \App\Models\Relation::class => \App\Policies\RelationPolicy::class,
        \App\Models\Role::class => \App\Policies\RolePolicy::class,
        \App\Models\Unit::class => \App\Policies\UnitPolicy::class,
        \App\Models\Department::class => \App\Policies\DepartmentPolicy::class,
        \App\Models\Section::class => \App\Policies\SectionPolicy::class,
        \App\Models\Floor::class => \App\Policies\FloorPolicy::class,
        \App\Models\Room::class => \App\Policies\RoomPolicy::class,
        \App\Models\Bed::class => \App\Policies\BedPolicy::class,
        \App\Models\MiliteryType::class => \App\Policies\MiliteryTypePolicy::class,
        \App\Models\ICUProcedureType::class => \App\Policies\ICUProcedureTypePolicy::class,
        \App\Models\OperationType::class => \App\Policies\OperationTypePolicy::class,
        \App\Models\MedicineType::class => \App\Policies\MedicineTypePolicy::class,
        \App\Models\Medicine::class => \App\Policies\MedicinePolicy::class,
        \App\Models\MedicineUsageType::class => \App\Policies\MedicineUsageTypePolicy::class,
        \App\Models\FoodType::class => \App\Policies\FoodTypePolicy::class,
        \App\Models\Disease::class => \App\Policies\DiseasePolicy::class,
        \App\Models\Branch::class => \App\Policies\BranchPolicy::class,
        \App\Models\Nurse::class => \App\Policies\NursePolicy::class,
        \App\Models\Prescription::class => \App\Policies\PrescriptionPolicy::class,
        \App\Models\ForeignCountryReferral::class => \App\Policies\ForeignCountryReferralPolicy::class,
        \App\Models\Pharmacy::class => \App\Policies\PharmacyPolicy::class,
        \App\Models\PatientTestRegistration::class => \App\Policies\LaboratoryPolicy::class,
        \Spatie\Activitylog\Models\Activity::class => \App\Policies\ActivityPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
