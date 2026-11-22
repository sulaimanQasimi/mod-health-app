<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepotTransferRequest extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_FULFILLED = 'fulfilled';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'request_number',
        'source_depot_id',
        'pharmacy_id',
        'destination_depot_id',
        'status',
        'notes',
        'created_by',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'fulfilled_by',
        'fulfilled_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(DepotTransferRequestItem::class);
    }

    public function sourceDepot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'source_depot_id');
    }

    public function destinationDepot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'destination_depot_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPharmacyTransfer(): bool
    {
        return $this->pharmacy_id !== null;
    }
}
