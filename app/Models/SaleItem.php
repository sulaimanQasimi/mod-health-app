<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'prescription_item_id',
        'stock_id',
        'medicine_id',
        'batch_number',
        'packs',
        'units',
        'qty_units',
        'units_per_pack',
        'unit_sale_price',
        'pack_price',
        'amount',
        'invoice_item_id',
        'user_id',
    ];

    protected $casts = [
        'packs' => 'integer',
        'units' => 'integer',
        'qty_units' => 'integer',
        'units_per_pack' => 'integer',
        'unit_sale_price' => 'decimal:2',
        'pack_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function stock(): BelongsTo
    {
        return $this->belongsTo(Stock::class);
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
