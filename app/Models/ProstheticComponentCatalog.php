<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProstheticComponentCatalog extends Model
{
    protected $table = 'prosthetic_component_catalog';

    protected $fillable = [
        'item_code',
        'name',
        'local_name',
        'category',
        'subcategory',
        'brand',
        'unit_of_measure',
        'standard_cost',
        'minimum_stock',
        'tracks_serial',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'standard_cost' => 'decimal:2',
        'tracks_serial' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function stockBalances(): HasMany
    {
        return $this->hasMany(ProstheticStockBalance::class, 'prosthetic_component_catalog_id');
    }

    public function prescriptionLines(): HasMany
    {
        return $this->hasMany(ProstheticPrescriptionLine::class, 'prosthetic_component_catalog_id');
    }
}
