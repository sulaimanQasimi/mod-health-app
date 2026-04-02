<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProstheticPrescription extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'status',
        'device_timing',
        'target_functionality',
        'suspension_notes',
        'socket_type',
        'liner_type',
        'foot_type',
        'special_instructions',
        'clinical_justification',
        'created_by',
        'updated_by',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ProstheticPrescriptionLine::class, 'prosthetic_prescription_id');
    }
}
