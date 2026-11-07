<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\DepotRequestItem;
use App\Models\DepotTransaction;
use App\Models\Pharmacy;
use App\Models\PharmacyFulfillment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionItemBatchAllocation;
use App\Models\StockBatch;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockBatchService
{
    /**
     * Receive stock into a depot as a single lot (no semicolon multi-batch strings).
     */
    public function receiveIntoDepot(Depot $depot, array $data): StockBatch
    {
        $quantity = max(1, (int) ($data['quantity'] ?? 0));
        $purchasePrice = max(0, (float) ($data['purchase_price'] ?? 0));
        $salePrice = max(0, (float) ($data['sale_price'] ?? $purchasePrice));

        return DB::transaction(function () use ($depot, $data, $quantity, $purchasePrice, $salePrice) {
            Depot::whereKey($depot->id)->lockForUpdate()->firstOrFail();

            // Always auto-assign a lot code on stock-in (ignore manual / empty input).
            $batchCode = $this->nextBatchCode($depot);

            $transaction = DepotTransaction::create([
                'depot_id' => $depot->id,
                'medicine_id' => $data['medicine_id'] ?? null,
                'tool_id' => $data['tool_id'] ?? null,
                'unit_id' => $data['unit_id'] ?? null,
                'batch_number' => $batchCode,
                'quantity' => $quantity,
                'purchase_price' => $purchasePrice,
                'sale_price' => $salePrice,
                'type' => DepotTransaction::TYPE_STOCK_IN,
                'transaction_type' => DepotTransaction::legacyTypeFor(DepotTransaction::TYPE_STOCK_IN),
                'status' => DepotTransaction::STATUS_COMPLETED,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'issued_date' => $data['issued_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $batch = $this->findOrCreateOwnerBatch(
                $depot,
                [
                    'medicine_id' => $data['medicine_id'] ?? null,
                    'tool_id' => $data['tool_id'] ?? null,
                    'unit_id' => $data['unit_id'] ?? null,
                    'batch_code' => $batchCode,
                    'expiry_date' => $data['expiry_date'] ?? null,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                ],
                $quantity
            );

            $batch->forceFill([
                'depot_transaction_id' => $transaction->id,
                'purchase_price' => $purchasePrice,
                'sale_price' => $salePrice,
            ])->save();

            $transaction->forceFill(['stock_batch_id' => $batch->id])->save();

            return $batch->fresh();
        });
    }

    /**
     * Transfer medicine from depot batches to pharmacy batches (FEFO or explicit lot).
     *
     * @return array{fulfillments: list<PharmacyFulfillment>, transactions: list<DepotTransaction>, pharmacy_batches: list<StockBatch>}
     */
    public function transferMedicineToPharmacy(
        Depot $depot,
        Pharmacy $pharmacy,
        int $medicineId,
        int $quantity,
        array $options = []
    ): array {
        $quantity = max(1, $quantity);

        return DB::transaction(function () use ($depot, $pharmacy, $medicineId, $quantity, $options) {
            Depot::whereKey($depot->id)->lockForUpdate()->firstOrFail();
            Pharmacy::whereKey($pharmacy->id)->lockForUpdate()->firstOrFail();

            $sourceBatches = $this->lockAndPickDepotBatches(
                $depot,
                $medicineId,
                $quantity,
                isset($options['stock_batch_id']) ? (int) $options['stock_batch_id'] : null,
                $options['batch_number'] ?? null,
            );

            $remaining = $quantity;
            $fulfillments = [];
            $transactions = [];
            $pharmacyBatches = [];

            foreach ($sourceBatches as $sourceBatch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (int) $sourceBatch->qty_on_hand);
                if ($take <= 0) {
                    continue;
                }

                $purchasePrice = array_key_exists('purchase_price', $options) && $options['purchase_price'] !== null
                    ? max(0, (float) $options['purchase_price'])
                    : (float) $sourceBatch->purchase_price;
                $salePrice = array_key_exists('sale_price', $options) && $options['sale_price'] !== null
                    ? max(0, (float) $options['sale_price'])
                    : max((float) $sourceBatch->sale_price, $purchasePrice);

                $sourceBatch->forceFill([
                    'qty_on_hand' => (int) $sourceBatch->qty_on_hand - $take,
                ])->save();

                $transactionNumber = DepotTransaction::nextTransactionNumber();
                $transactionDate = now()->toDateString();
                $unitName = ! empty($options['unit_id'])
                    ? optional(Unit::find($options['unit_id']))->name
                    : optional($sourceBatch->unit)->name;

                $fulfillment = PharmacyFulfillment::create([
                    'medicine_id' => $medicineId,
                    'unit_type' => $unitName,
                    'amount' => (string) $take,
                    'batch_code' => $sourceBatch->batch_code,
                    'expiry_date' => $sourceBatch->expiry_date,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                    'form_no' => $transactionNumber,
                    'date' => $transactionDate,
                    'pharmacy_id' => $pharmacy->id,
                    'user_id' => Auth::id() ?? 0,
                ]);

                $transaction = DepotTransaction::create([
                    'from_depot_id' => $depot->id,
                    'depot_id' => $depot->id,
                    'pharmacy_id' => $pharmacy->id,
                    'depot_request_id' => $options['depot_request_id'] ?? null,
                    'medicine_id' => $medicineId,
                    'unit_id' => $options['unit_id'] ?? $sourceBatch->unit_id,
                    'batch_number' => $sourceBatch->batch_code,
                    'stock_batch_id' => $sourceBatch->id,
                    'quantity' => $take,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                    'transaction_date' => $transactionDate,
                    'transaction_number' => $transactionNumber,
                    'expiry_date' => $sourceBatch->expiry_date,
                    'type' => DepotTransaction::TYPE_DEPOT_TO_PHARMACY,
                    'transaction_type' => 'out',
                    'status' => DepotTransaction::STATUS_COMPLETED,
                    'notes' => $options['notes'] ?? null,
                    'transactionable_type' => PharmacyFulfillment::class,
                    'transactionable_id' => $fulfillment->id,
                ]);

                $pharmacyBatch = $this->findOrCreateOwnerBatch(
                    $pharmacy,
                    [
                        'medicine_id' => $medicineId,
                        'unit_id' => $options['unit_id'] ?? $sourceBatch->unit_id,
                        'batch_code' => $sourceBatch->batch_code,
                        'expiry_date' => $sourceBatch->expiry_date?->format('Y-m-d'),
                        'purchase_price' => $purchasePrice,
                        'sale_price' => $salePrice,
                        'source_batch_id' => $sourceBatch->id,
                    ],
                    $take
                );

                $pharmacyBatch->forceFill([
                    'source_batch_id' => $sourceBatch->id,
                    'depot_transaction_id' => $transaction->id,
                    'pharmacy_fulfillment_id' => $fulfillment->id,
                    'purchase_price' => $purchasePrice,
                    'sale_price' => $salePrice,
                ])->save();

                $fulfillment->forceFill(['stock_batch_id' => $pharmacyBatch->id])->save();

                $fulfillments[] = $fulfillment;
                $transactions[] = $transaction;
                $pharmacyBatches[] = $pharmacyBatch;
                $remaining -= $take;
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient depot batch stock. Short by {$remaining}.",
                ]);
            }

            return compact('fulfillments', 'transactions', 'pharmacyBatches');
        });
    }

    public function transferRequestItemToPharmacy(
        Depot $depot,
        Pharmacy $pharmacy,
        DepotRequestItem $item,
        ?int $depotRequestId = null,
        ?string $notes = null
    ): DepotTransaction {
        $result = $this->transferMedicineToPharmacy(
            $depot,
            $pharmacy,
            (int) $item->medicine_id,
            (int) $item->quantity,
            [
                'stock_batch_id' => $item->stock_batch_id,
                'batch_number' => $item->batch_number,
                'unit_id' => $item->unit_id,
                'purchase_price' => $item->purchase_price,
                'sale_price' => $item->sale_price,
                'depot_request_id' => $depotRequestId,
                'notes' => $notes,
            ]
        );

        /** @var DepotTransaction $transaction */
        $transaction = $result['transactions'][0];

        // If FEFO split into multiple lots, keep the first transaction id on the request item
        // and leave audit trail on all created transactions.
        $item->update([
            'depot_transaction_id' => $transaction->id,
            'batch_number' => $transaction->batch_number,
            'expiry_date' => $transaction->expiry_date,
            'purchase_price' => $transaction->purchase_price,
            'sale_price' => $transaction->sale_price,
            'stock_batch_id' => $transaction->stock_batch_id,
        ]);

        return $transaction;
    }

    /**
     * Dispense a prescription item from pharmacy batches (FEFO) and return allocations.
     *
     * @return Collection<int, PrescriptionItemBatchAllocation>
     */
    public function allocatePrescriptionItem(Pharmacy $pharmacy, PrescriptionItem $item, int $quantity): Collection
    {
        $quantity = max(1, $quantity);
        $medicineId = (int) ($item->selectedAlternative?->medicine_id ?: $item->medicine_id);

        if ($medicineId <= 0) {
            throw ValidationException::withMessages([
                'medicine_id' => 'Prescription item has no medicine to dispense.',
            ]);
        }

        return DB::transaction(function () use ($pharmacy, $item, $medicineId, $quantity) {
            Pharmacy::whereKey($pharmacy->id)->lockForUpdate()->firstOrFail();

            $batches = StockBatch::query()
                ->forOwner($pharmacy)
                ->forMedicine($medicineId)
                ->withStock()
                ->fefo()
                ->lockForUpdate()
                ->get();

            $available = (int) $batches->sum('qty_on_hand');
            if ($available < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient pharmacy stock for this medicine. Available: {$available}.",
                ]);
            }

            $remaining = $quantity;
            $allocations = collect();

            foreach ($batches as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, (int) $batch->qty_on_hand);
                if ($take <= 0) {
                    continue;
                }

                $batch->forceFill([
                    'qty_on_hand' => (int) $batch->qty_on_hand - $take,
                ])->save();

                $unitSale = max(0, (float) $batch->sale_price);
                $allocations->push(PrescriptionItemBatchAllocation::create([
                    'prescription_item_id' => $item->id,
                    'stock_batch_id' => $batch->id,
                    'quantity' => $take,
                    'unit_sale_price' => $unitSale,
                    'amount' => $unitSale * $take,
                ]));

                $remaining -= $take;
            }

            return $allocations;
        });
    }

    public function availableForOwner(Model $owner, ?int $medicineId = null, ?int $toolId = null): int
    {
        $query = StockBatch::query()->forOwner($owner)->withStock();

        if ($medicineId) {
            $query->forMedicine($medicineId);
        } elseif ($toolId) {
            $query->where('tool_id', $toolId)->whereNull('medicine_id');
        }

        return (int) $query->sum('qty_on_hand');
    }

    /**
     * @return Collection<int, StockBatch>
     */
    public function listOwnerBatches(Model $owner, ?int $medicineId = null): Collection
    {
        $query = StockBatch::query()
            ->forOwner($owner)
            ->withStock()
            ->fefo()
            ->with(['medicine:id,name', 'unit:id,name']);

        if ($medicineId) {
            $query->forMedicine($medicineId);
        }

        return $query->get();
    }

    public function nextBatchCode(?Model $owner = null): string
    {
        $prefix = 'BT-'.now()->format('Ymd');
        $sequence = random_int(1000, 9999);

        if ($owner) {
            $count = StockBatch::query()
                ->forOwner($owner)
                ->where('batch_code', 'like', $prefix.'%')
                ->withTrashed()
                ->count();
            $sequence = $count + 1;
        }

        return sprintf('%s-%04d', $prefix, $sequence);
    }

    private function normalizeBatchCode(?string $raw, ?Model $owner = null): string
    {
        $code = trim((string) $raw);
        // Reject multi-lot packing via semicolon / comma lists — one lot per row.
        if ($code !== '' && preg_match('/[;,|]/', $code)) {
            throw ValidationException::withMessages([
                'batch_number' => 'Enter one batch code only. Create separate lines for multiple lots.',
            ]);
        }

        if ($code === '') {
            return $this->nextBatchCode($owner);
        }

        return $code;
    }

    /**
     * @return Collection<int, StockBatch>
     */
    private function lockAndPickDepotBatches(
        Depot $depot,
        int $medicineId,
        int $quantity,
        ?int $stockBatchId = null,
        ?string $batchNumber = null
    ): Collection {
        if ($stockBatchId) {
            $batch = StockBatch::query()
                ->forOwner($depot)
                ->forMedicine($medicineId)
                ->whereKey($stockBatchId)
                ->lockForUpdate()
                ->first();

            if (! $batch || (int) $batch->qty_on_hand < $quantity) {
                throw ValidationException::withMessages([
                    'stock_batch_id' => 'Selected depot batch does not have enough stock.',
                ]);
            }

            return collect([$batch]);
        }

        $normalized = trim((string) $batchNumber);
        if ($normalized !== '' && ! preg_match('/[;,|]/', $normalized)) {
            $matched = StockBatch::query()
                ->forOwner($depot)
                ->forMedicine($medicineId)
                ->where('batch_code', $normalized)
                ->withStock()
                ->fefo()
                ->lockForUpdate()
                ->get();

            if ($matched->isNotEmpty() && (int) $matched->sum('qty_on_hand') >= $quantity) {
                return $matched;
            }
        }

        $batches = StockBatch::query()
            ->forOwner($depot)
            ->forMedicine($medicineId)
            ->withStock()
            ->fefo()
            ->lockForUpdate()
            ->get();

        if ((int) $batches->sum('qty_on_hand') < $quantity) {
            // Fallback: if ledger has no batches yet, invent one from legacy depot transaction stock.
            $legacy = DepotTransaction::availableStock($depot->id, $medicineId);
            if ($legacy >= $quantity && $batches->isEmpty()) {
                $synthetic = $this->findOrCreateOwnerBatch(
                    $depot,
                    [
                        'medicine_id' => $medicineId,
                        'batch_code' => 'LEGACY-DEPOT-'.$depot->id.'-'.$medicineId,
                        'purchase_price' => 0,
                        'sale_price' => 0,
                    ],
                    $legacy
                );

                return collect([
                    StockBatch::query()->whereKey($synthetic->id)->lockForUpdate()->firstOrFail(),
                ]);
            }

            throw ValidationException::withMessages([
                'quantity' => 'Insufficient depot batch stock. Available quantity is '.(int) $batches->sum('qty_on_hand').'.',
            ]);
        }

        return $batches;
    }

    private function findOrCreateOwnerBatch(Model $owner, array $attributes, int $addQuantity): StockBatch
    {
        $query = StockBatch::query()
            ->forOwner($owner)
            ->where('batch_code', $attributes['batch_code']);

        if (! empty($attributes['medicine_id'])) {
            $query->where('medicine_id', $attributes['medicine_id'])->whereNull('tool_id');
        } elseif (! empty($attributes['tool_id'])) {
            $query->where('tool_id', $attributes['tool_id'])->whereNull('medicine_id');
        }

        if (! empty($attributes['expiry_date'])) {
            $query->whereDate('expiry_date', $attributes['expiry_date']);
        } else {
            $query->whereNull('expiry_date');
        }

        /** @var StockBatch|null $batch */
        $batch = $query->lockForUpdate()->first();

        if ($batch) {
            $batch->forceFill([
                'qty_on_hand' => (int) $batch->qty_on_hand + $addQuantity,
                'unit_id' => $attributes['unit_id'] ?? $batch->unit_id,
                'purchase_price' => $attributes['purchase_price'] ?? $batch->purchase_price,
                'sale_price' => $attributes['sale_price'] ?? $batch->sale_price,
                'source_batch_id' => $attributes['source_batch_id'] ?? $batch->source_batch_id,
            ])->save();

            return $batch->fresh();
        }

        return StockBatch::create([
            'owner_type' => $owner->getMorphClass(),
            'owner_id' => $owner->getKey(),
            'medicine_id' => $attributes['medicine_id'] ?? null,
            'tool_id' => $attributes['tool_id'] ?? null,
            'unit_id' => $attributes['unit_id'] ?? null,
            'batch_code' => $attributes['batch_code'],
            'expiry_date' => $attributes['expiry_date'] ?? null,
            'qty_on_hand' => $addQuantity,
            'purchase_price' => $attributes['purchase_price'] ?? 0,
            'sale_price' => $attributes['sale_price'] ?? 0,
            'source_batch_id' => $attributes['source_batch_id'] ?? null,
        ]);
    }
}
