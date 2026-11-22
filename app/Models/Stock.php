<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stock extends Model
{
    use SoftDeletes;

    public const LOCATION_DEPOT = 'depot';

    public const LOCATION_PHARMACY = 'pharmacy';

    protected $fillable = [
        'batch_number',
        'medicine_id',
        'unit_id',
        'units_per_pack',
        'location_type',
        'location_id',
        'qty_on_hand',
        'qty_sold',
        'expiry_date',
        'purchase_price',
        'unit_sale_price',
        'pack_price',
        'purchase_item_id',
    ];

    protected $casts = [
        'units_per_pack' => 'integer',
        'qty_on_hand' => 'integer',
        'qty_sold' => 'integer',
        'expiry_date' => 'date',
        'purchase_price' => 'decimal:2',
        'unit_sale_price' => 'decimal:2',
        'pack_price' => 'decimal:2',
    ];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseItem::class);
    }

    public function depot(): BelongsTo
    {
        return $this->belongsTo(Depot::class, 'location_id');
    }

    public function pharmacy(): BelongsTo
    {
        return $this->belongsTo(Pharmacy::class, 'location_id');
    }

    public function locationModel(): ?Model
    {
        return match ($this->location_type) {
            self::LOCATION_DEPOT => Depot::query()->find($this->location_id),
            self::LOCATION_PHARMACY => Pharmacy::query()->find($this->location_id),
            default => null,
        };
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function scopeForLocation(Builder $query, string $type, int $id): Builder
    {
        return $query->where('location_type', $type)->where('location_id', $id);
    }

    public function scopeForDepot(Builder $query, int $depotId): Builder
    {
        return $query->forLocation(self::LOCATION_DEPOT, $depotId);
    }

    public function scopeForPharmacy(Builder $query, int $pharmacyId): Builder
    {
        return $query->forLocation(self::LOCATION_PHARMACY, $pharmacyId);
    }

    public function scopeWithStock(Builder $query): Builder
    {
        return $query->where('qty_on_hand', '>', 0);
    }

    public function scopeForBatch(Builder $query, string $batchNumber): Builder
    {
        return $query->where('batch_number', $batchNumber);
    }

    public function scopeFefo(Builder $query): Builder
    {
        return $query
            ->orderByRaw('expiry_date is null')
            ->orderBy('expiry_date')
            ->orderBy('id');
    }

    public function locationLabel(): string
    {
        $model = $this->locationModel();
        if ($model && isset($model->name)) {
            return ucfirst($this->location_type).': '.$model->name;
        }

        return ucfirst((string) $this->location_type).' #'.$this->location_id;
    }
}
