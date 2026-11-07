<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_PAID = 'paid';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'uuid',
        'patient_id',
        'invoiceable_type',
        'invoiceable_id',
        'branch_id',
        'status',
        'subtotal',
        'total',
        'paid_amount',
        'issued_at',
        'paid_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            $user = Auth::user();
            $model->created_by = $user->id ?? $model->created_by ?? 0;
        });

        static::updating(function (Invoice $model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? 0;
        });
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoiceable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Convenience accessor when invoiceable is an Appointment.
     */
    public function appointment(): ?Appointment
    {
        return $this->invoiceable instanceof Appointment ? $this->invoiceable : null;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('amount');
        $this->forceFill([
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ])->save();
    }
}
