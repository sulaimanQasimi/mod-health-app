<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    public const TYPE_PURCHASE_RECEIVE = 'purchase_receive';

    public const TYPE_DEPOT_TO_PHARMACY = 'depot_to_pharmacy';

    public const TYPE_DEPOT_TO_DEPOT = 'depot_to_depot';

    public const TYPE_SALE = 'sale';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'type',
        'batch_number',
        'medicine_id',
        'from_location_type',
        'from_location_id',
        'to_location_type',
        'to_location_id',
        'from_stock_id',
        'to_stock_id',
        'qty_units',
        'reference_type',
        'reference_id',
        'user_id',
        'notes',
    ];

    protected $casts = [
        'qty_units' => 'integer',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function fromStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'from_stock_id');
    }

    public function toStock(): BelongsTo
    {
        return $this->belongsTo(Stock::class, 'to_stock_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
