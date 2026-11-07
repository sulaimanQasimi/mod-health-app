<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentReceipt extends Model
{
    protected $fillable = [
        'uuid',
        'payment_id',
        'invoice_id',
        'snapshot',
    ];

    protected $casts = [
        'snapshot' => 'array',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (PaymentReceipt $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
