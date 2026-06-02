<?php

namespace App\Services;

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
    public function stockItemsForDepot(int $depotId, ?string $itemType = null, ?string $search = null): Collection
    {
        $items = collect();

        if ($itemType === null || $itemType === self::ITEM_MEDICINE) {
            $medicineIds = DepotTransaction::query()
                ->completed()
                ->forDepot($depotId)
                ->whereNotNull('medicine_id')
                ->distinct()
                ->pluck('medicine_id');

            foreach ($medicineIds as $medicineId) {
                $medicine = Medicine::query()->find($medicineId);
                if (! $medicine) {
                    continue;
                }

                if ($search && stripos($medicine->name, $search) === false) {
                    continue;
                }

                $available = $this->availableMedicineStock($depotId, (int) $medicineId);
                if ($available <= 0) {
                    continue;
                }

                $items->push([
                    'item_type' => self::ITEM_MEDICINE,
                    'item_id' => (int) $medicineId,
                    'name' => $medicine->name,
                    'available' => $available,
                    'unit' => null,
                ]);
            }
        }

        if ($itemType === null || $itemType === self::ITEM_TOOL) {
            $toolIds = DepotTransaction::query()
                ->completed()
                ->forDepot($depotId)
                ->whereNotNull('tool_id')
                ->distinct()
                ->pluck('tool_id');

            foreach ($toolIds as $toolId) {
                $tool = Tool::query()->with('unit')->find($toolId);
                if (! $tool) {
                    continue;
                }

                if ($search && stripos($tool->name, $search) === false && stripos($tool->code, $search) === false) {
                    continue;
                }

                $available = $this->availableToolStock($depotId, (int) $toolId);
                if ($available <= 0) {
                    continue;
                }

                $items->push([
                    'item_type' => self::ITEM_TOOL,
                    'item_id' => (int) $toolId,
                    'name' => $tool->name,
                    'available' => $available,
                    'unit' => $tool->unit?->symbol ?? $tool->unit?->name,
                ]);
            }
        }

        return $items->sortBy('name')->values();
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

        $rows = collect();

        foreach ($depotIds as $id) {
            $depot = \App\Models\Depot::query()->find($id);
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
