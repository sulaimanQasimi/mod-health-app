<?php

namespace App\Services;

use App\Models\ProstheticComponentCatalog;
use App\Models\ProstheticStockBalance;
use App\Models\ProstheticStockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProstheticsStockService
{
    public function receive(int $catalogId, ?int $branchId, float $quantity, ?string $notes = null): void
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be positive.');
        }

        DB::transaction(function () use ($catalogId, $branchId, $quantity, $notes) {
            $balance = ProstheticStockBalance::query()
                ->where('prosthetic_component_catalog_id', $catalogId)
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            if (! $balance) {
                $balance = new ProstheticStockBalance([
                    'prosthetic_component_catalog_id' => $catalogId,
                    'branch_id' => $branchId,
                    'quantity' => 0,
                ]);
            }

            $balance->quantity = (float) $balance->quantity + $quantity;
            $balance->save();

            ProstheticStockMovement::create([
                'prosthetic_component_catalog_id' => $catalogId,
                'branch_id' => $branchId,
                'prosthetic_work_order_id' => null,
                'movement_type' => 'receive',
                'quantity_delta' => $quantity,
                'notes' => $notes,
                'created_by' => Auth::id(),
            ]);
        });
    }

    /**
     * Issue components to a work order (deducts stock).
     *
     * @param  array<int, array{catalog_id: int, quantity: float}>  $lines
     */
    public function issueToWorkOrder(int $workOrderId, ?int $branchId, array $lines): void
    {
        DB::transaction(function () use ($workOrderId, $branchId, $lines) {
            foreach ($lines as $line) {
                $catalogId = (int) $line['catalog_id'];
                $qty = (float) $line['quantity'];
                if ($qty <= 0) {
                    continue;
                }

                $balance = ProstheticStockBalance::query()
                    ->where('prosthetic_component_catalog_id', $catalogId)
                    ->where('branch_id', $branchId)
                    ->lockForUpdate()
                    ->first();

                $available = $balance ? (float) $balance->quantity : 0.0;
                if ($available + 1e-9 < $qty) {
                    $item = ProstheticComponentCatalog::find($catalogId);
                    $label = $item ? $item->name : "#{$catalogId}";
                    throw new \RuntimeException(__('global.prosthetics_insufficient_stock', ['item' => $label]));
                }

                if (! $balance) {
                    throw new \RuntimeException(__('global.prosthetics_insufficient_stock', ['item' => "#{$catalogId}"]));
                }

                $balance->quantity = $available - $qty;
                $balance->save();

                ProstheticStockMovement::create([
                    'prosthetic_component_catalog_id' => $catalogId,
                    'branch_id' => $branchId,
                    'prosthetic_work_order_id' => $workOrderId,
                    'movement_type' => 'issue_to_work_order',
                    'quantity_delta' => -$qty,
                    'notes' => null,
                    'created_by' => Auth::id(),
                ]);
            }
        });
    }
}
