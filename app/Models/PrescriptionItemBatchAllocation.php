<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItemBatchAllocation extends Model
{
    protected $fillable = [
        'prescription_item_id',
        'stock_batch_id',
        'quantity',
        'unit_sale_price',
        'amount',
        'invoice_item_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_sale_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function stockBatch(): BelongsTo
    {
        return $this->belongsTo(StockBatch::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
