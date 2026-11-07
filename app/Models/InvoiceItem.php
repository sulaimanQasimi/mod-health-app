<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    public const TYPE_APPOINTMENT_FEE = 'appointment_fee';

    public const TYPE_LAB_TEST = 'lab_test';

    public const TYPE_PHARMACY_MEDICINE = 'pharmacy_medicine';

    public const TYPE_CUSTOM = 'custom';

    public const PAYMENT_UNPAID = 'unpaid';

    public const PAYMENT_PAID = 'paid';

    protected $fillable = [
        'invoice_id',
        'item_type',
        'description',
        'unit_price',
        'quantity',
        'amount',
        'billable_type',
        'billable_id',
        'lab_type_id',
        'payment_status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'amount' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function labType(): BelongsTo
    {
        return $this->belongsTo(LabType::class);
    }
}
