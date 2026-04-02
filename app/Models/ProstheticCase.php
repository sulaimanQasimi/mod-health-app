<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProstheticCase extends Model
{
    protected $table = 'prosthetic_cases';

    public const STATUS_NEW = 'new';
    public const STATUS_REFERRED = 'referred';
    public const STATUS_UNDER_ASSESSMENT = 'under_assessment';
    public const STATUS_MEASUREMENT_COMPLETED = 'measurement_completed';
    public const STATUS_PRESCRIPTION_COMPLETED = 'prescription_completed';
    public const STATUS_WAITING_APPROVAL = 'waiting_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_IN_PRODUCTION = 'in_production';
    public const STATUS_TRIAL_FIT = 'trial_fit';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_UNDER_FOLLOW_UP = 'under_follow_up';
    public const STATUS_CLOSED = 'closed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'patient_id',
        'referral_id',
        'branch_id',
        'case_number',
        'status',
        'side',
        'body_region',
        'case_category',
        'device_type',
        'primary_diagnosis',
        'secondary_diagnosis',
        'cause_of_loss_notes',
        'injury_surgery_onset_date',
        'amputation_level',
        'priority',
        'first_time_or_replacement',
        'prior_device_history',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'injury_surgery_onset_date' => 'date',
        'approved_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(ProstheticReferral::class, 'referral_id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(ProstheticAssessment::class, 'prosthetic_case_id');
    }

    public function measurementSets(): HasMany
    {
        return $this->hasMany(ProstheticMeasurementSet::class, 'prosthetic_case_id')->orderByDesc('version');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(ProstheticPrescription::class, 'prosthetic_case_id');
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(ProstheticEstimate::class, 'prosthetic_case_id');
    }

    public function workOrders(): HasMany
    {
        return $this->hasMany(ProstheticWorkOrder::class, 'prosthetic_case_id');
    }

    public function fittingSessions(): HasMany
    {
        return $this->hasMany(ProstheticFittingSession::class, 'prosthetic_case_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ProstheticDelivery::class, 'prosthetic_case_id');
    }

    public function followUps(): HasMany
    {
        return $this->hasMany(ProstheticFollowUp::class, 'prosthetic_case_id');
    }

    public function attachments()
    {
        return $this->morphMany(ProstheticAttachment::class, 'attachable');
    }

    public static function statusList(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_REFERRED,
            self::STATUS_UNDER_ASSESSMENT,
            self::STATUS_MEASUREMENT_COMPLETED,
            self::STATUS_PRESCRIPTION_COMPLETED,
            self::STATUS_WAITING_APPROVAL,
            self::STATUS_APPROVED,
            self::STATUS_IN_PRODUCTION,
            self::STATUS_TRIAL_FIT,
            self::STATUS_DELIVERED,
            self::STATUS_UNDER_FOLLOW_UP,
            self::STATUS_CLOSED,
            self::STATUS_CANCELLED,
        ];
    }
}
