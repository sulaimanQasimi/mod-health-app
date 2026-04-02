<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticStockMovement extends Model
{
    protected $fillable = [
        'prosthetic_component_catalog_id',
        'branch_id',
        'prosthetic_work_order_id',
        'movement_type',
        'quantity_delta',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'quantity_delta' => 'decimal:3',
    ];

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(ProstheticComponentCatalog::class, 'prosthetic_component_catalog_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(ProstheticWorkOrder::class, 'prosthetic_work_order_id');
    }
}
