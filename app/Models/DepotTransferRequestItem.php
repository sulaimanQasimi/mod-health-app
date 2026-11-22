<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepotTransferRequestItem extends Model
{
    protected $fillable = [
        'depot_transfer_request_id',
        'medicine_id',
        'batch_number',
        'packs',
        'units',
        'qty_units',
        'units_per_pack',
        'from_stock_id',
        'to_stock_id',
    ];

    protected $casts = [
        'packs' => 'integer',
        'units' => 'integer',
        'qty_units' => 'integer',
        'units_per_pack' => 'integer',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(DepotTransferRequest::class, 'depot_transfer_request_id');
    }

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
}
