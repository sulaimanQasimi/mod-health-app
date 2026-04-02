<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProstheticWorkOrder extends Model
{
    protected $fillable = [
        'work_order_number',
        'prosthetic_case_id',
        'prosthetic_prescription_id',
        'status',
        'production_stage',
        'technician_user_id',
        'planned_start_date',
        'planned_end_date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'planned_start_date' => 'date',
        'planned_end_date' => 'date',
    ];

    public function prostheticCase(): BelongsTo
    {
        return $this->belongsTo(ProstheticCase::class, 'prosthetic_case_id');
    }

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(ProstheticPrescription::class, 'prosthetic_prescription_id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(ProstheticStockMovement::class, 'prosthetic_work_order_id');
    }
}
