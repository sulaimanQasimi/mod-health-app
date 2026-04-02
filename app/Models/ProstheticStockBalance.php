<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProstheticStockBalance extends Model
{
    protected $fillable = [
        'prosthetic_component_catalog_id',
        'branch_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
    ];

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(ProstheticComponentCatalog::class, 'prosthetic_component_catalog_id');
    }
}
