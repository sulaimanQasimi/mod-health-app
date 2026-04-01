<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodCrossmatch extends Model
{
    use HasFactory;

    public const RESULT_VALUES = ['pending', 'compatible', 'incompatible', 'inconclusive'];
    public const STATUSES = ['pending', 'compatible', 'incompatible', 'overridden'];

    protected $fillable = [
        'blood_bank_id',
        'blood_unit_id',
        'patient_id',
        'patient_sample_id',
        'major_result',
        'minor_result',
        'status',
        'auto_decision',
        'auto_reason',
        'is_overridden',
        'override_by',
        'override_reason',
        'tested_at',
        'tested_by',
        'notes',
    ];

    protected $casts = [
        'auto_decision' => 'boolean',
        'is_overridden' => 'boolean',
        'tested_at' => 'datetime',
    ];

    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    public function bloodUnit()
    {
        return $this->belongsTo(BloodUnit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function patientSample()
    {
        return $this->belongsTo(BloodPatientSample::class, 'patient_sample_id');
    }

    public function testedBy()
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function overriddenBy()
    {
        return $this->belongsTo(User::class, 'override_by');
    }
}
