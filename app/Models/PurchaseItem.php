<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'medicine_id',
        'batch_number',
        'expiry_date',
        'packs',
        'units',
        'qty_units',
        'units_per_pack',
        'purchase_price',
        'unit_sale_price',
        'pack_price',
        'stock_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'packs' => 'integer',
        'units' => 'integer',
        'qty_units' => 'integer',
        'units_per_pack' => 'integer',
        'purchase_price' => 'decimal:2',
        'unit_sale_price' => 'decimal:2',
        'pack_price' => 'decimal:2',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }
}
