<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Persisted blood check row in {@see $table} blood_checks (lab typing / verification for a blood request).
 */
class BloodCheckRecord extends Model
{
    use SoftDeletes;

    protected $table = 'blood_checks';

    public const COMPONENT_TYPES = ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'];

    protected $fillable = [
        'blood_bank_id',
        'branch_id',
        'appointment_id',
        'patient_id',
        'department_id',
        'operation_id',
        'hospitalization_id',
        'anesthesia_id',
        'i_c_u_id',
        'under_review_id',
        'abo_group',
        'rh',
        'component_type',
        'quantity',
        'status',
        'reject_reason',
        'notes',
        'patient_typed_group',
        'patient_typed_rh',
        'verified_at',
        'verified_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function bloodBank(): BelongsTo
    {
        return $this->belongsTo(BloodBank::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
