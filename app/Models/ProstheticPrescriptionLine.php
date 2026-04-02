<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticPrescriptionLine extends Model
{
    protected $fillable = [
        'prosthetic_prescription_id',
        'prosthetic_component_catalog_id',
        'quantity',
        'unit_cost_snapshot',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost_snapshot' => 'decimal:2',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(ProstheticPrescription::class, 'prosthetic_prescription_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(ProstheticComponentCatalog::class, 'prosthetic_component_catalog_id');
    }
}
