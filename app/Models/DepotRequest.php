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

    protected $fillable = [
        'request_number',
        'requesting_depot_id',
        'source_depot_id',
        'medicine_id',
        'tool_id',
        'unit_id',
        'quantity',
        'batch_number',
        'notes',
        'status',
        'requested_by',
        'approved_by',
        'fulfilled_by',
        'approved_at',
        'fulfilled_at',
        'rejection_reason',
        'depot_transaction_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
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

    public function itemType(): ?string
    {
        if ($this->medicine_id) {
            return DepotTransaction::ITEM_MEDICINE;
        }

        if ($this->tool_id) {
            return DepotTransaction::ITEM_TOOL;
        }

        return null;
    }

    public function itemName(): string
    {
        if ($this->medicine_id) {
            return $this->medicine?->name ?? '-';
        }

        if ($this->tool_id) {
            return $this->tool?->displayName() ?? '-';
        }

        return '-';
    }

    public function requestingDepot()
    {
        return $this->belongsTo(Depot::class, 'requesting_depot_id');
    }

    public function sourceDepot()
    {
        return $this->belongsTo(Depot::class, 'source_depot_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
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

    public function depotTransaction()
    {
        return $this->belongsTo(DepotTransaction::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(DepotRequestStatusLog::class)->latest('id');
    }
}
