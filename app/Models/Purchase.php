<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_RECEIVED = 'received';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'purchase_number',
        'depot_id',
        'supplier_id',
        'supplier',
        'purchase_date',
        'status',
        'notes',
        'created_by',
        'received_by',
        'received_at',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'received_at' => 'datetime',
    ];

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class);
    }

    public function supplierRelation(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }
}
