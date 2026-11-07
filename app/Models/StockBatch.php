<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_type',
        'owner_id',
        'medicine_id',
        'tool_id',
        'unit_id',
        'batch_code',
        'expiry_date',
        'qty_on_hand',
        'purchase_price',
        'sale_price',
        'source_batch_id',
        'depot_transaction_id',
        'pharmacy_fulfillment_id',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'qty_on_hand' => 'integer',
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function sourceBatch(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_batch_id');
    }

    public function childBatches(): HasMany
    {
        return $this->hasMany(self::class, 'source_batch_id');
    }

    public function depotTransaction(): BelongsTo
    {
        return $this->belongsTo(DepotTransaction::class);
    }

    public function pharmacyFulfillment(): BelongsTo
    {
        return $this->belongsTo(PharmacyFulfillment::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PrescriptionItemBatchAllocation::class);
    }

    public function scopeForOwner(Builder $query, Model $owner): Builder
    {
        return $query
            ->where('owner_type', $owner->getMorphClass())
            ->where('owner_id', $owner->getKey());
    }

    public function scopeWithStock(Builder $query): Builder
    {
        return $query->where('qty_on_hand', '>', 0);
    }

    public function scopeForMedicine(Builder $query, int $medicineId): Builder
    {
        return $query->where('medicine_id', $medicineId)->whereNull('tool_id');
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query
            ->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    public function displayLabel(): string
    {
        $parts = [$this->batch_code];
        if ($this->expiry_date) {
            $parts[] = $this->expiry_date->format('Y-m-d');
        }
        $parts[] = 'qty '.$this->qty_on_hand;

        return implode(' · ', $parts);
    }
}
