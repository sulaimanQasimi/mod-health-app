<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodPatientSample extends Model
{
    use HasFactory;

    protected $fillable = [
        'blood_bank_id',
        'patient_id',
        'sample_id',
        'collected_at',
        'collected_by',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    public function bloodBank()
    {
        return $this->belongsTo(BloodBank::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function crossmatches()
    {
        return $this->hasMany(BloodCrossmatch::class, 'patient_sample_id');
    }
}
