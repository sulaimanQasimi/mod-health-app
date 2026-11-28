<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\Medicine;
use App\Models\Pharmacy;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Support\PackQuantity;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function availableForLocation(string $locationType, int $locationId, int $medicineId): int
    {
        return (int) Stock::query()
            ->forLocation($locationType, $locationId)
            ->where('medicine_id', $medicineId)
            ->sum('qty_on_hand');
    }

    public function availableForDepot(int $depotId, int $medicineId): int
    {
        return $this->availableForLocation(Stock::LOCATION_DEPOT, $depotId, $medicineId);
    }

    public function availableForPharmacy(int $pharmacyId, int $medicineId): int
    {
        return $this->availableForLocation(Stock::LOCATION_PHARMACY, $pharmacyId, $medicineId);
    }

    public function availableForBatch(
        string $locationType,
        int $locationId,
        int $medicineId,
        string $batchNumber,
    ): int {
        return (int) Stock::query()
            ->forLocation($locationType, $locationId)
            ->where('medicine_id', $medicineId)
            ->where('batch_number', $batchNumber)
            ->sum('qty_on_hand');
    }

    /**
     * Medicines with on-hand stock at a location.
     *
     * @return list<array{id: int, name: string, units_per_pack: int, total_qty: int}>
     */
    public function medicinesForLocation(string $locationType, int $locationId): array
    {
        return Stock::query()
            ->forLocation($locationType, $locationId)
            ->where('qty_on_hand', '>', 0)
            ->with('medicine:id,name,units_per_pack')
            ->get()
            ->groupBy('medicine_id')
            ->map(function ($rows) {
                /** @var Stock $first */
                $first = $rows->first();
                $medicine = $first->medicine;
                if (! $medicine) {
                    return null;
                }

                return [
                    'id' => (int) $medicine->id,
                    'name' => (string) $medicine->name,
                    'units_per_pack' => (int) ($first->units_per_pack ?: $medicine->unitsPerPack()),
                    'total_qty' => (int) $rows->sum('qty_on_hand'),
                ];
            })
            ->filter()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Batches with stock for a medicine at a location (FEFO: earliest expiry first).
     *
     * @return list<array{batch_number: string, qty_on_hand: int, expiry_date: ?string, units_per_pack: int}>
     */
    public function batchesForLocation(string $locationType, int $locationId, int $medicineId): array
    {
        return Stock::query()
            ->forLocation($locationType, $locationId)
            ->where('medicine_id', $medicineId)
            ->where('qty_on_hand', '>', 0)
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->orderBy('batch_number')
            ->get(['batch_number', 'qty_on_hand', 'expiry_date', 'units_per_pack'])
            ->map(fn (Stock $stock) => [
                'batch_number' => (string) $stock->batch_number,
                'qty_on_hand' => (int) $stock->qty_on_hand,
                'expiry_date' => optional($stock->expiry_date)?->format('Y-m-d'),
                'units_per_pack' => max(1, (int) $stock->units_per_pack),
            ])
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function listForLocation(?string $locationType = null, ?int $locationId = null, ?string $search = null): Collection
    {
        $query = Stock::query()
            ->with(['medicine:id,name,units_per_pack', 'unit:id,name,symbol'])
            ->orderBy('batch_number')
            ->orderBy('location_type')
            ->orderBy('location_id');

        if ($locationType && $locationId) {
            $query->forLocation($locationType, $locationId);
        } elseif ($locationType) {
            $query->where('location_type', $locationType);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', '%'.$search.'%')
                    ->orWhereHas('medicine', fn ($m) => $m->where('name', 'like', '%'.$search.'%'));
            });
        }

        return $query->get();
    }

    /**
     * @return Collection<int, Stock>
     */
    public function listByBatchNumber(string $batchNumber): Collection
    {
        return Stock::query()
            ->with(['medicine:id,name,units_per_pack', 'unit:id,name,symbol'])
            ->forBatch($batchNumber)
            ->orderBy('location_type')
            ->orderBy('location_id')
            ->get();
    }

    public function receiveIntoDepot(
        Depot $depot,
        Medicine $medicine,
        string $batchNumber,
        int $qtyUnits,
        array $attributes = [],
        ?Model $reference = null,
    ): Stock {
        return DB::transaction(function () use ($depot, $medicine, $batchNumber, $qtyUnits, $attributes, $reference) {
            $stock = $this->lockOrCreateLocationStock(
                Stock::LOCATION_DEPOT,
                (int) $depot->id,
                $medicine,
                $batchNumber,
                $attributes
            );

            $stock->qty_on_hand = (int) $stock->qty_on_hand + $qtyUnits;
            $stock->save();

            $this->recordMovement(
                StockMovement::TYPE_PURCHASE_RECEIVE,
                $batchNumber,
                (int) $medicine->id,
                null,
                null,
                Stock::LOCATION_DEPOT,
                (int) $depot->id,
                null,
                (int) $stock->id,
                $qtyUnits,
                $reference
            );

            return $stock->fresh(['medicine', 'unit']);
        });
    }

    public function transfer(
        string $fromType,
        int $fromId,
        string $toType,
        int $toId,
        Medicine $medicine,
        int $qtyUnits,
        ?string $batchNumber = null,
        ?Model $reference = null,
        string $movementType = StockMovement::TYPE_DEPOT_TO_PHARMACY,
    ): array {
        if ($qtyUnits < 1) {
            throw ValidationException::withMessages(['quantity' => 'Transfer quantity must be at least 1 unit.']);
        }

        return DB::transaction(function () use ($fromType, $fromId, $toType, $toId, $medicine, $qtyUnits, $batchNumber, $reference, $movementType) {
            $sources = $this->lockFefoSources($fromType, $fromId, (int) $medicine->id, $batchNumber);

            $available = $sources->sum('qty_on_hand');
            if ($available < $qtyUnits) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Available: {$available}, requested: {$qtyUnits}.",
                ]);
            }

            $remaining = $qtyUnits;
            $moved = [];

            foreach ($sources as $source) {
                if ($remaining < 1) {
                    break;
                }

                $take = min((int) $source->qty_on_hand, $remaining);
                if ($take < 1) {
                    continue;
                }

                $source->qty_on_hand = (int) $source->qty_on_hand - $take;
                $source->save();

                $target = $this->lockOrCreateLocationStock(
                    $toType,
                    $toId,
                    $medicine,
                    $source->batch_number,
                    [
                        'unit_id' => $source->unit_id,
                        'units_per_pack' => $source->units_per_pack,
                        'expiry_date' => $source->expiry_date,
                        'purchase_price' => $source->purchase_price,
                        'unit_sale_price' => $source->unit_sale_price,
                        'pack_price' => $source->pack_price,
                        'purchase_item_id' => $source->purchase_item_id,
                    ]
                );

                $target->qty_on_hand = (int) $target->qty_on_hand + $take;
                $target->save();

                $this->recordMovement(
                    $movementType,
                    $source->batch_number,
                    (int) $medicine->id,
                    $fromType,
                    $fromId,
                    $toType,
                    $toId,
                    (int) $source->id,
                    (int) $target->id,
                    $take,
                    $reference
                );

                $moved[] = [
                    'from_stock' => $source->fresh(),
                    'to_stock' => $target->fresh(),
                    'qty_units' => $take,
                    'batch_number' => $source->batch_number,
                ];

                $remaining -= $take;
            }

            return $moved;
        });
    }

    public function sellFromPharmacy(
        Pharmacy $pharmacy,
        Medicine $medicine,
        int $qtyUnits,
        int $packs = 1,
        int $units = 0,
        ?string $batchNumber = null,
        ?Model $reference = null,
    ): array {
        if ($qtyUnits < 1) {
            throw ValidationException::withMessages(['quantity' => 'Sale quantity must be at least 1 unit.']);
        }

        return DB::transaction(function () use ($pharmacy, $medicine, $qtyUnits, $packs, $units, $batchNumber, $reference) {
            $sources = $this->lockFefoSources(Stock::LOCATION_PHARMACY, (int) $pharmacy->id, (int) $medicine->id, $batchNumber);
            $available = $sources->sum('qty_on_hand');

            if ($available < $qtyUnits) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient pharmacy stock. Available: {$available}, requested: {$qtyUnits}.",
                ]);
            }

            $remaining = $qtyUnits;
            $sold = [];
            $packsLeft = $packs;
            $unitsLeft = $units;

            foreach ($sources as $source) {
                if ($remaining < 1) {
                    break;
                }

                $take = min((int) $source->qty_on_hand, $remaining);
                if ($take < 1) {
                    continue;
                }

                $source->qty_on_hand = (int) $source->qty_on_hand - $take;
                $source->qty_sold = (int) $source->qty_sold + $take;
                $source->save();

                $linePacks = 0;
                $lineUnits = $take;
                $upp = max(1, (int) $source->units_per_pack);
                if ($packsLeft > 0 || $unitsLeft > 0) {
                    $linePacks = min($packsLeft, intdiv($take, $upp));
                    $lineUnits = $take - ($linePacks * $upp);
                    $packsLeft -= $linePacks;
                    $unitsLeft = max(0, $unitsLeft - $lineUnits);
                } else {
                    $split = PackQuantity::fromUnits($take, $upp);
                    $linePacks = $split['packs'];
                    $lineUnits = $split['units'];
                }

                $this->recordMovement(
                    StockMovement::TYPE_SALE,
                    $source->batch_number,
                    (int) $medicine->id,
                    Stock::LOCATION_PHARMACY,
                    (int) $pharmacy->id,
                    null,
                    null,
                    (int) $source->id,
                    null,
                    $take,
                    $reference
                );

                $sold[] = [
                    'stock' => $source->fresh(),
                    'qty_units' => $take,
                    'packs' => $linePacks,
                    'units' => $lineUnits,
                    'units_per_pack' => $upp,
                    'batch_number' => $source->batch_number,
                    'unit_sale_price' => (float) $source->unit_sale_price,
                    'pack_price' => (float) $source->pack_price,
                ];

                $remaining -= $take;
            }

            return $sold;
        });
    }

    /**
     * @return Collection<int, Stock>
     */
    private function lockFefoSources(string $locationType, int $locationId, int $medicineId, ?string $batchNumber = null): Collection
    {
        $query = Stock::query()
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->where('medicine_id', $medicineId)
            ->where('qty_on_hand', '>', 0)
            ->fefo()
            ->lockForUpdate();

        if ($batchNumber) {
            $query->where('batch_number', $batchNumber);
        }

        return $query->get();
    }

    private function lockOrCreateLocationStock(
        string $locationType,
        int $locationId,
        Medicine $medicine,
        string $batchNumber,
        array $attributes = [],
    ): Stock {
        $existing = Stock::query()
            ->where('batch_number', $batchNumber)
            ->where('location_type', $locationType)
            ->where('location_id', $locationId)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            if ((int) $existing->medicine_id !== (int) $medicine->id) {
                throw ValidationException::withMessages([
                    'batch_number' => 'Batch number already exists for a different medicine at this location.',
                ]);
            }

            return $existing;
        }

        $unitsPerPack = max(1, (int) ($attributes['units_per_pack'] ?? $medicine->unitsPerPack()));

        return Stock::query()->create([
            'batch_number' => $batchNumber,
            'medicine_id' => $medicine->id,
            'unit_id' => $attributes['unit_id'] ?? $medicine->unit_id,
            'units_per_pack' => $unitsPerPack,
            'location_type' => $locationType,
            'location_id' => $locationId,
            'qty_on_hand' => 0,
            'qty_sold' => 0,
            'expiry_date' => $attributes['expiry_date'] ?? null,
            'purchase_price' => $attributes['purchase_price'] ?? 0,
            'unit_sale_price' => $attributes['unit_sale_price'] ?? 0,
            'pack_price' => $attributes['pack_price'] ?? 0,
            'purchase_item_id' => $attributes['purchase_item_id'] ?? null,
        ]);
    }

    private function recordMovement(
        string $type,
        string $batchNumber,
        int $medicineId,
        ?string $fromType,
        ?int $fromId,
        ?string $toType,
        ?int $toId,
        ?int $fromStockId,
        ?int $toStockId,
        int $qtyUnits,
        ?Model $reference = null,
    ): StockMovement {
        return StockMovement::query()->create([
            'type' => $type,
            'batch_number' => $batchNumber,
            'medicine_id' => $medicineId,
            'from_location_type' => $fromType,
            'from_location_id' => $fromId,
            'to_location_type' => $toType,
            'to_location_id' => $toId,
            'from_stock_id' => $fromStockId,
            'to_stock_id' => $toStockId,
            'qty_units' => $qtyUnits,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'user_id' => auth()->id(),
        ]);
    }
}
