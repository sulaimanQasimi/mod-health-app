<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticMeasurement extends Model
{
    protected $fillable = [
        'prosthetic_measurement_set_id',
        'name',
        'value_numeric',
        'value_text',
        'unit',
        'notes',
    ];

    protected $casts = [
        'value_numeric' => 'decimal:4',
    ];

    public function measurementSet(): BelongsTo
    {
        return $this->belongsTo(ProstheticMeasurementSet::class, 'prosthetic_measurement_set_id');
    }
}
