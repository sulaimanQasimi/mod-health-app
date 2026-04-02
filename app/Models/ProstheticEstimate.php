<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticEstimate extends Model
{
    protected $fillable = [
        'prosthetic_case_id',
        'prosthetic_prescription_id',
        'currency',
        'parts_total',
        'labor_total',
        'discount',
        'total',
        'status',
        'approval_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parts_total' => 'decimal:2',
        'labor_total' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(ProstheticPrescription::class, 'prosthetic_prescription_id');
    }
}
