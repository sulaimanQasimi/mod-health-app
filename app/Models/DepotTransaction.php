<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class DepotTransaction extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_DEPOT_TO_DEPOT = 'depot_to_depot';
    public const TYPE_DEPOT_TO_PHARMACY = 'depot_to_pharmacy';
    public const TYPE_STOCK_IN = 'stock_in';
    public const TYPE_STOCK_OUT = 'stock_out';
    public const TYPE_ADJUSTMENT = 'adjustment';

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'transaction_number',
        'depot_id',
        'user_id',
        'pharmacy_id',
        'medicine_type_id',
        'medicine_id',
        'tool_id',
        'unit_id',
        'batch_number',
        'transactionable_type',
        'transactionable_id',
        'transaction_type',
        'type',
        'status',
        'quantity',
        'from_depot_id',
        'to_depot_id',
        'transaction_date',
        'issued_date',
        'expiry_date',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'transaction_date' => 'date',
        'issued_date' => 'date',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $user = Auth::user();
            $model->created_by = $model->created_by ?: ($user->id ?? null);
            $model->user_id = $model->user_id ?: ($user->id ?? null);
            $model->transaction_date = $model->transaction_date ?: now()->toDateString();
            $model->status = $model->status ?: self::STATUS_COMPLETED;
            $model->type = $model->type ?: self::TYPE_STOCK_IN;
            $model->transaction_type = $model->transaction_type ?: self::legacyTypeFor($model->type);
            $model->transaction_number = $model->transaction_number ?: self::nextTransactionNumber();
        });

        self::updating(function ($model) {
            $user = Auth::user();
            $model->updated_by = $user->id ?? $model->updated_by;
        });

        self::deleting(function ($model) {
            $user = Auth::user();
            $model->deleted_by = $user->id ?? $model->deleted_by;
            $model->save();
        });
    }

    public static function types(): array
    {
        return [
            self::TYPE_DEPOT_TO_DEPOT,
            self::TYPE_DEPOT_TO_PHARMACY,
            self::TYPE_STOCK_IN,
            self::TYPE_STOCK_OUT,
            self::TYPE_ADJUSTMENT,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function legacyTypeFor(string $type): string
    {
        return match ($type) {
            self::TYPE_STOCK_OUT, self::TYPE_DEPOT_TO_PHARMACY => 'out',
            self::TYPE_DEPOT_TO_DEPOT => 'transfer',
            default => 'in',
        };
    }

    public static function nextTransactionNumber(): string
    {
        return 'DTR-' . now()->format('YmdHis') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }

    public static function availableStock(int $depotId, int $medicineId): int
    {
        $incoming = self::query()
            ->where('status', self::STATUS_COMPLETED)
            ->where('medicine_id', $medicineId)
            ->where(function ($query) use ($depotId) {
                $query->where(function ($q) use ($depotId) {
                    $q->where('depot_id', $depotId)
                        ->whereIn('type', [self::TYPE_STOCK_IN, self::TYPE_ADJUSTMENT]);
                })->orWhere(function ($q) use ($depotId) {
                    $q->where('to_depot_id', $depotId)
                        ->where('type', self::TYPE_DEPOT_TO_DEPOT);
                });
            })
            ->sum('quantity');

        $outgoing = self::query()
            ->where('status', self::STATUS_COMPLETED)
            ->where('medicine_id', $medicineId)
            ->where(function ($query) use ($depotId) {
                $query->where(function ($q) use ($depotId) {
                    $q->where('depot_id', $depotId)
                        ->where('type', self::TYPE_STOCK_OUT);
                })->orWhere(function ($q) use ($depotId) {
                    $q->where('from_depot_id', $depotId)
                        ->whereIn('type', [self::TYPE_DEPOT_TO_DEPOT, self::TYPE_DEPOT_TO_PHARMACY]);
                });
            })
            ->sum('quantity');

        return (int) $incoming - (int) $outgoing;
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForMedicine($query, int $medicineId)
    {
        return $query->where('medicine_id', $medicineId);
    }

    public function scopeForDepot($query, int $depotId)
    {
        return $query->where(function ($q) use ($depotId) {
            $q->where('depot_id', $depotId)
                ->orWhere('from_depot_id', $depotId)
                ->orWhere('to_depot_id', $depotId);
        });
    }

    public function depot()
    {
        return $this->belongsTo(Depot::class, 'depot_id');
    }

    public function fromDepot()
    {
        return $this->belongsTo(Depot::class, 'from_depot_id');
    }

    public function toDepot()
    {
        return $this->belongsTo(Depot::class, 'to_depot_id');
    }

    public function pharmacy()
    {
        return $this->belongsTo(Pharmacy::class);
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function product()
    {
        return $this->medicine();
    }

    public function item()
    {
        return $this->medicine();
    }

    public function medicineType()
    {
        return $this->belongsTo(MedicineType::class);
    }

    public function tool()
    {
        return $this->belongsTo(Tool::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
