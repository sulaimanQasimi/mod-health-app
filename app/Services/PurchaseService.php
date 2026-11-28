<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\Supplier;
use App\Support\PackQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseService
{
    public function __construct(
        private readonly StockService $stocks,
        private readonly StockBatchService $batchCodes,
    ) {
    }

    public function create(array $data, array $items): Purchase
    {
        return DB::transaction(function () use ($data, $items) {
            $items = $this->ensureBatchNumbers($items);

            $supplierMeta = $this->resolveSupplier($data);

            $purchase = Purchase::query()->create([
                'purchase_number' => $this->nextPurchaseNumber(),
                'depot_id' => $data['depot_id'],
                'supplier_id' => $supplierMeta['supplier_id'],
                'supplier' => $supplierMeta['supplier'],
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'status' => Purchase::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($purchase, $items);

            return $purchase->fresh(['items.medicine', 'depot']);
        });
    }

    public function update(Purchase $purchase, array $data, array $items): Purchase
    {
        if (! $purchase->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Only draft purchases can be updated.']);
        }

        return DB::transaction(function () use ($purchase, $data, $items) {
            $items = $this->ensureBatchNumbers($items, $purchase->id);

            $supplierMeta = $this->resolveSupplier($data, $purchase);

            $purchase->update([
                'depot_id' => $data['depot_id'] ?? $purchase->depot_id,
                'supplier_id' => $supplierMeta['supplier_id'],
                'supplier' => $supplierMeta['supplier'],
                'purchase_date' => $data['purchase_date'] ?? $purchase->purchase_date,
                'notes' => $data['notes'] ?? $purchase->notes,
            ]);

            $purchase->items()->delete();
            $this->syncItems($purchase, $items);

            return $purchase->fresh(['items.medicine', 'depot']);
        });
    }

    public function receive(Purchase $purchase): Purchase
    {
        if (! $purchase->isDraft()) {
            throw ValidationException::withMessages(['status' => 'Only draft purchases can be received into depot.']);
        }

        if ($purchase->items()->count() < 1) {
            throw ValidationException::withMessages(['items' => 'Add at least one purchase item before receiving.']);
        }

        return DB::transaction(function () use ($purchase) {
            $purchase->load(['items.medicine', 'depot']);

            foreach ($purchase->items as $item) {
                $this->assertBatchNotUsedElsewhere($item->batch_number, $purchase->id);

                $stock = $this->stocks->receiveIntoDepot(
                    $purchase->depot,
                    $item->medicine,
                    $item->batch_number,
                    (int) $item->qty_units,
                    [
                        'unit_id' => $item->medicine->unit_id,
                        'units_per_pack' => $item->units_per_pack,
                        'expiry_date' => $item->expiry_date,
                        'purchase_price' => $item->purchase_price,
                        'unit_sale_price' => $item->unit_sale_price,
                        'pack_price' => $item->pack_price,
                        'purchase_item_id' => $item->id,
                    ],
                    $purchase
                );

                $item->forceFill([
                    'stock_id' => $stock->id,
                ])->save();

                $stock->forceFill(['purchase_item_id' => $item->id])->save();
            }

            $purchase->update([
                'status' => Purchase::STATUS_RECEIVED,
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            return $purchase->fresh(['items.medicine', 'items.stock', 'depot']);
        });
    }

    public function cancel(Purchase $purchase): Purchase
    {
        if ($purchase->isReceived()) {
            throw ValidationException::withMessages(['status' => 'Received purchases cannot be cancelled.']);
        }

        $purchase->update(['status' => Purchase::STATUS_CANCELLED]);

        return $purchase->fresh();
    }

    private function syncItems(Purchase $purchase, array $items): void
    {
        foreach ($items as $row) {
            $medicine = Medicine::query()->findOrFail($row['medicine_id']);
            $unitsPerPack = max(1, (int) ($row['units_per_pack'] ?? $medicine->unitsPerPack()));
            $packs = (int) ($row['packs'] ?? 0);
            $units = (int) ($row['units'] ?? 0);
            $qtyUnits = PackQuantity::toUnits($packs, $units, $unitsPerPack);
            $unitSale = (float) ($row['unit_sale_price'] ?? 0);
            $packPrice = $unitsPerPack > 1
                ? (float) ($row['pack_price'] ?? ($unitSale > 0 ? $unitSale * $unitsPerPack : 0))
                : ($unitSale > 0 ? $unitSale * $unitsPerPack : (float) ($row['pack_price'] ?? 0));

            PurchaseItem::query()->create([
                'purchase_id' => $purchase->id,
                'medicine_id' => $medicine->id,
                'batch_number' => trim((string) $row['batch_number']),
                'expiry_date' => $row['expiry_date'] ?? null,
                'packs' => $packs,
                'units' => $units,
                'qty_units' => $qtyUnits,
                'units_per_pack' => $unitsPerPack,
                'purchase_price' => 0,
                'unit_sale_price' => $unitSale,
                'pack_price' => $packPrice,
            ]);
        }
    }

    /**
     * Always auto-assign standard lot codes (BT-YYYYMMDD-####), ignoring any client input.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    private function ensureBatchNumbers(array $items, ?int $ignorePurchaseId = null): array
    {
        $nextSeq = $this->batchCodes->nextBatchSequence();

        foreach ($items as $index => $_row) {
            $items[$index]['batch_number'] = $this->batchCodes->formatBatchCode($nextSeq);
            $nextSeq++;
        }

        $this->assertUniqueBatchNumbers(array_column($items, 'batch_number'), $ignorePurchaseId);

        return $items;
    }

    /**
     * @param  array<int, string>  $batchNumbers
     */
    private function assertUniqueBatchNumbers(array $batchNumbers, ?int $ignorePurchaseId = null): void
    {
        $normalized = array_values(array_filter(array_map(fn ($b) => trim((string) $b), $batchNumbers)));
        if (count($normalized) !== count(array_unique($normalized))) {
            throw ValidationException::withMessages(['batch_number' => 'Batch numbers must be unique on the purchase.']);
        }

        foreach ($normalized as $batch) {
            $this->assertBatchNotUsedElsewhere($batch, $ignorePurchaseId);
        }
    }

    private function assertBatchNotUsedElsewhere(string $batchNumber, ?int $ignorePurchaseId = null): void
    {
        $purchaseQuery = PurchaseItem::query()->where('batch_number', $batchNumber);
        if ($ignorePurchaseId) {
            $purchaseQuery->where('purchase_id', '!=', $ignorePurchaseId);
        }

        if ($purchaseQuery->exists()) {
            throw ValidationException::withMessages([
                'batch_number' => "Batch number [{$batchNumber}] is already used on another purchase.",
            ]);
        }

        if (Stock::query()->where('batch_number', $batchNumber)->exists()) {
            throw ValidationException::withMessages([
                'batch_number' => "Batch number [{$batchNumber}] already exists in stock.",
            ]);
        }

        if (StockBatch::query()->withTrashed()->where('batch_code', $batchNumber)->exists()) {
            throw ValidationException::withMessages([
                'batch_number' => "Batch number [{$batchNumber}] already exists as a stock lot.",
            ]);
        }
    }

    /**
     * @return array{supplier_id: int|null, supplier: string|null}
     */
    private function resolveSupplier(array $data, ?Purchase $existing = null): array
    {
        if (! empty($data['supplier_id'])) {
            $supplier = Supplier::query()->find($data['supplier_id']);

            return [
                'supplier_id' => $supplier?->id,
                'supplier' => $supplier?->name ?? ($data['supplier'] ?? $existing?->supplier),
            ];
        }

        return [
            'supplier_id' => $existing?->supplier_id,
            'supplier' => $data['supplier'] ?? $existing?->supplier,
        ];
    }

    private function nextPurchaseNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';
        $last = Purchase::query()
            ->withTrashed()
            ->where('purchase_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('purchase_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
