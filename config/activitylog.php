<?php

use Spatie\Activitylog\Actions\CleanActivityLogAction;
use Spatie\Activitylog\Actions\LogActivityAction;
use Spatie\Activitylog\Models\Activity;

return [

    /*
     * If set to false, no activities will be saved to the database.
     */
    'enabled' => env('ACTIVITYLOG_ENABLED', true),

    /*
     * When the clean command is executed, all recording activities older than
     * the number of days specified here will be deleted.
     */
    'clean_after_days' => (int) env('ACTIVITYLOG_CLEAN_AFTER_DAYS', 90),

    /*
     * If no log name is passed to the activity() helper
     * we use this default log name.
     */
    'default_log_name' => 'default',

    /*
     * You can specify an auth driver here that gets user models.
     * If this is null we'll use the current Laravel auth driver.
     */
    'default_auth_driver' => null,

    /*
     * If set to true, the subject relationship on activities
     * will include soft deleted models.
     */
    'include_soft_deleted_subjects' => false,

    /*
     * This model will be used to log activity.
     * It should implement the Spatie\Activitylog\Contracts\Activity interface
     * and extend Illuminate\Database\Eloquent\Model.
     */
    'activity_model' => Activity::class,

    /*
     * These attributes will be excluded from logging for all models.
     */
    'default_except_attributes' => [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        'deleted_at',
        'email_verified_at',
        'created_by',
        'updated_by',
        'deleted_by',
        'image',
        'avatar',
        'metadata',
        'detailed_notes',
        'brief_history',
        'icu_enterance_note',
        'icu_reject_reason',
        'cause_of_death',
        'discharge_remark',
        'status_remark',
        'notes',
        'description',
        'raw_data',
        'payload',
        'content',
        'body',
        'html',
        'file_path',
        'attachment',
        'attachments',
    ],

    /*
     * High-churn / low-audit-value models skipped by ModelActivityServiceProvider.
     */
    'ignored_models' => [
        \App\Models\BloodStockMovement::class,
        \App\Models\DentalChartImage::class,
        \App\Models\DentalChartMeasurement::class,
        \App\Models\DentalPeriodontalMeasurement::class,
        \App\Models\DepotRequestStatusLog::class,
        \App\Models\DepotTransaction::class,
        \App\Models\MedicationAdministrationTime::class,
        \App\Models\PatientTestResultAttachment::class,
        \App\Models\PrintedNumber::class,
        \App\Models\ProstheticStockBalance::class,
        \App\Models\ProstheticStockMovement::class,
        \App\Models\VitalSign::class,
        \App\Models\VitalSignSchedule::class,
    ],

    /*
     * Truncate long string/JSON attribute values stored in properties.
     */
    'max_attribute_length' => (int) env('ACTIVITYLOG_MAX_ATTRIBUTE_LENGTH', 500),

    /*
     * When enabled, activities are buffered in memory and inserted in a
     * single bulk query after the response has been sent to the client.
     */
    'buffer' => [
        'enabled' => env('ACTIVITYLOG_BUFFER_ENABLED', true),
    ],

    /*
     * These action classes can be overridden to customize how activities
     * are logged and cleaned. Your custom classes must extend the originals.
     */
    'actions' => [
        'log_activity' => LogActivityAction::class,
        'clean_log' => CleanActivityLogAction::class,
    ],
];
