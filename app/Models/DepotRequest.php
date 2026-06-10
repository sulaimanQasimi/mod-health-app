<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DepotRequest extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FULFILLED = 'fulfilled';
    public const STATUS_CANCELLED = 'cancelled';

    public const WORKFLOW_STEPS = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_FULFILLED,
    ];

    protected $fillable = [
        'request_number',
        'requesting_depot_id',
        'source_depot_id',
        'notes',
        'status',
        'requested_by',
        'approved_by',
        'fulfilled_by',
        'approved_at',
        'fulfilled_at',
        'rejection_reason',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $user = Auth::user();
            $model->requested_by = $model->requested_by ?: ($user->id ?? null);
            $model->status = $model->status ?: self::STATUS_DRAFT;
            $model->request_number = $model->request_number ?: self::nextRequestNumber();
        });
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_FULFILLED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function nextRequestNumber(): string
    {
        return 'DRQ-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    public function workflowRank(): int
    {
        $rank = array_search($this->status, self::WORKFLOW_STEPS, true);

        return $rank === false ? 0 : $rank;
    }

    public function totalQuantity(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function itemsSummary(): string
    {
        $names = $this->items->map(fn (DepotRequestItem $item) => $item->itemName())->filter()->values();

        if ($names->isEmpty()) {
            return '-';
        }

        if ($names->count() === 1) {
            return $names->first();
        }

        return $names->take(2)->join(', ') . ($names->count() > 2 ? ' +' . ($names->count() - 2) : '');
    }

    public function itemName(): string
    {
        $this->loadMissing(['items.medicine', 'items.tool']);

        return $this->itemsSummary();
    }

    public function getQuantityAttribute(): int
    {
        if ($this->relationLoaded('items')) {
            return $this->totalQuantity();
        }

        return (int) $this->items()->sum('quantity');
    }

    public function depotTransaction()
    {
        return $this->hasOne(DepotTransaction::class, 'depot_request_id')->latestOfMany('id');
    }

    public function requestingDepot()
    {
        return $this->belongsTo(Depot::class, 'requesting_depot_id');
    }

    public function sourceDepot()
    {
        return $this->belongsTo(Depot::class, 'source_depot_id');
    }

    public function items()
    {
        return $this->hasMany(DepotRequestItem::class)->orderBy('sort_order');
    }

    public function transactions()
    {
        return $this->hasMany(DepotTransaction::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function fulfilledBy()
    {
        return $this->belongsTo(User::class, 'fulfilled_by');
    }

    public function statusLogs()
    {
        return $this->hasMany(DepotRequestStatusLog::class)->latest('id');
    }
}
