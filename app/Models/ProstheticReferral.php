<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticReferral extends Model
{
    protected $fillable = [
        'patient_id',
        'branch_id',
        'referral_number',
        'status',
        'referral_date',
        'referring_facility',
        'referring_doctor',
        'reason',
        'diagnosis_summary',
        'urgency',
        'requested_service_type',
        'notes',
        'document_path',
        'converted_case_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'referral_date' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function convertedCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'converted_case_id');
    }

    public function cases()
    {
        return $this->hasMany(ProstheticCase::class, 'referral_id');
    }
}
