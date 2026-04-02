<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProstheticMeasurementSet extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'version',
        'is_locked',
        'locked_at',
        'locked_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }

    public function measurements(): HasMany
    {
        return $this->hasMany(ProstheticMeasurement::class, 'prosthetic_measurement_set_id');
    }
}
