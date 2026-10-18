<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Tool;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class DepotStockService
{
    public const ITEM_MEDICINE = DepotTransaction::ITEM_MEDICINE;
    public const ITEM_TOOL = DepotTransaction::ITEM_TOOL;

    public function availableMedicineStock(int $depotId, int $medicineId): int
    {
        return DepotTransaction::availableStock($depotId, $medicineId);
    }

    public function availableToolStock(int $depotId, int $toolId): int
    {
        return DepotTransaction::availableToolStock($depotId, $toolId);
    }

    public function availableStock(string $itemType, int $depotId, int $itemId): int
    {
        return DepotTransaction::availableStockFor($itemType, $depotId, $itemId);
    }

    public function ensureAvailable(string $itemType, int $depotId, int $itemId, int $quantity): void
    {
        $available = $this->availableStock($itemType, $depotId, $itemId);

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Insufficient depot stock. Available quantity is {$available}.",
            ]);
        }
    }

    public function lockLedger(int $depotId, string $itemType, int $itemId): void
    {
        $column = DepotTransaction::itemColumn($itemType);

        DepotTransaction::query()
            ->where($column, $itemId)
            ->where(function ($query) use ($depotId) {
                $query->where('depot_id', $depotId)
                    ->orWhere('from_depot_id', $depotId)
                    ->orWhere('to_depot_id', $depotId);
            })
            ->lockForUpdate()
            ->get(['id']);
    }

    /**
     * @return Collection<int, array{item_type: string, item_id: int, name: string, available: int, unit: ?string}>
     */
    public function stockItemsForDepot(
        int $depotId,
        ?string $itemType = null,
        ?string $search = null,
        bool $includeZero = false,
    ): Collection {
        $items = collect();

        if ($itemType === null || $itemType === self::ITEM_MEDICINE) {
            $items = $items->merge(
                $this->aggregatedStockForDepot($depotId, self::ITEM_MEDICINE, $search, $includeZero)
            );
        }

        if ($itemType === null || $itemType === self::ITEM_TOOL) {
            $items = $items->merge(
                $this->aggregatedStockForDepot($depotId, self::ITEM_TOOL, $search, $includeZero)
            );
        }

        return $items->sortBy('name')->values();
    }

    /**
     * Single aggregated query for all medicine or tool balances in a depot.
     *
     * @return Collection<int, array{item_type: string, item_id: int, name: string, available: int, unit: ?string}>
     */
    private function aggregatedStockForDepot(
        int $depotId,
        string $itemType,
        ?string $search,
        bool $includeZero,
    ): Collection {
        $column = DepotTransaction::itemColumn($itemType);
        $stockIn = DepotTransaction::TYPE_STOCK_IN;
        $adjustment = DepotTransaction::TYPE_ADJUSTMENT;
        $stockOut = DepotTransaction::TYPE_STOCK_OUT;
        $depotToDepot = DepotTransaction::TYPE_DEPOT_TO_DEPOT;
        $depotToPharmacy = DepotTransaction::TYPE_DEPOT_TO_PHARMACY;

        $balances = DepotTransaction::query()
            ->completed()
            ->forDepot($depotId)
            ->whereNotNull($column)
            ->selectRaw("
                {$column} as item_id,
                SUM(CASE
                    WHEN (depot_id = ? AND type IN (?, ?))
                      OR (to_depot_id = ? AND type = ?)
                    THEN quantity ELSE 0
                END) -
                SUM(CASE
                    WHEN (depot_id = ? AND type = ?)
                      OR (from_depot_id = ? AND type IN (?, ?))
                    THEN quantity ELSE 0
                END) as available
            ", [
                $depotId, $stockIn, $adjustment,
                $depotId, $depotToDepot,
                $depotId, $stockOut,
                $depotId, $depotToDepot, $depotToPharmacy,
            ])
            ->groupBy($column)
            ->pluck('available', 'item_id');

        if ($balances->isEmpty()) {
            return collect();
        }

        if ($itemType === self::ITEM_MEDICINE) {
            $catalog = Medicine::query()
                ->whereIn('id', $balances->keys())
                ->when($search, fn ($q) => $q->where('name', 'like', '%'.$search.'%'))
                ->get(['id', 'name'])
                ->keyBy('id');
        } else {
            $catalog = Tool::query()
                ->with('unit:id,name,symbol')
                ->whereIn('id', $balances->keys())
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($inner) use ($search) {
                        $inner->where('name', 'like', '%'.$search.'%')
                            ->orWhere('code', 'like', '%'.$search.'%');
                    });
                })
                ->get(['id', 'name', 'code', 'unit_id'])
                ->keyBy('id');
        }

        return $balances
            ->map(function ($available, $itemId) use ($itemType, $catalog, $includeZero) {
                $item = $catalog->get($itemId);
                if (! $item) {
                    return null;
                }

                $qty = (int) $available;
                if (! $includeZero && $qty <= 0) {
                    return null;
                }

                return [
                    'item_type' => $itemType,
                    'item_id' => (int) $itemId,
                    'name' => $item->name,
                    'available' => $qty,
                    'unit' => $itemType === self::ITEM_TOOL
                        ? ($item->unit?->symbol ?? $item->unit?->name)
                        : null,
                ];
            })
            ->filter()
            ->values();
    }

    public const LOW_STOCK_THRESHOLD = 10;

    /**
     * @return array{
     *     total_items: int,
     *     total_quantity: int,
     *     medicine_count: int,
     *     tool_count: int,
     *     total_low_stock: int,
     *     total_out_of_stock: int,
     *     total_healthy: int,
     * }
     */
    public function stockStatsForDepot(int $depotId): array
    {
        $items = $this->stockItemsForDepot($depotId, includeZero: true);

        return [
            'total_items' => $items->count(),
            'total_quantity' => (int) $items->sum('available'),
            'medicine_count' => $items->where('item_type', self::ITEM_MEDICINE)->count(),
            'tool_count' => $items->where('item_type', self::ITEM_TOOL)->count(),
            'total_low_stock' => $items->filter(fn ($item) => $item['available'] > 0 && $item['available'] <= self::LOW_STOCK_THRESHOLD)->count(),
            'total_out_of_stock' => $items->where('available', '<=', 0)->count(),
            'total_healthy' => $items->where('available', '>', self::LOW_STOCK_THRESHOLD)->count(),
        ];
    }

    /**
     * @param  Collection<int, array{item_type: string, item_id: int, name: string, available: int, unit: ?string}>  $items
     * @return Collection<int, array{item_type: string, item_id: int, name: string, available: int, unit: ?string}>
     */
    public function filterAndSortStockItems(
        Collection $items,
        ?string $stockStatus = null,
        string $sortBy = 'name',
        string $sortOrder = 'asc',
    ): Collection {
        if ($stockStatus === 'low_stock') {
            $items = $items->filter(fn ($item) => $item['available'] > 0 && $item['available'] <= self::LOW_STOCK_THRESHOLD);
        } elseif ($stockStatus === 'out_of_stock') {
            $items = $items->where('available', '<=', 0);
        } elseif ($stockStatus === 'healthy') {
            $items = $items->where('available', '>', self::LOW_STOCK_THRESHOLD);
        }

        $descending = strtolower($sortOrder) === 'desc';

        $sorted = match ($sortBy) {
            'quantity' => $descending ? $items->sortByDesc('available') : $items->sortBy('available'),
            'item_type' => $descending ? $items->sortByDesc('item_type') : $items->sortBy('item_type'),
            default => $descending ? $items->sortByDesc('name') : $items->sortBy('name'),
        };

        return $sorted->values();
    }

    /**
     * @return Collection<int, array{depot_id: int, depot_name: string, item_type: string, item_id: int, item_name: string, available: int}>
     */
    public function stockReport(?int $depotId = null, ?string $itemType = null): Collection
    {
        $depotIds = $depotId
            ? collect([$depotId])
            : DepotTransaction::query()
                ->completed()
                ->select('depot_id')
                ->distinct()
                ->pluck('depot_id')
                ->merge(
                    DepotTransaction::query()->completed()->select('from_depot_id')->distinct()->pluck('from_depot_id')
                )
                ->merge(
                    DepotTransaction::query()->completed()->select('to_depot_id')->distinct()->pluck('to_depot_id')
                )
                ->filter()
                ->unique()
                ->values();

        $depots = Depot::query()
            ->whereIn('id', $depotIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $rows = collect();

        foreach ($depotIds as $id) {
            $depot = $depots->get($id);
            if (! $depot) {
                continue;
            }

            foreach ($this->stockItemsForDepot((int) $id, $itemType) as $item) {
                $rows->push([
                    'depot_id' => (int) $id,
                    'depot_name' => $depot->name,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'item_name' => $item['name'],
                    'available' => $item['available'],
                    'unit' => $item['unit'] ?? null,
                ]);
            }
        }

        return $rows;
    }
}
