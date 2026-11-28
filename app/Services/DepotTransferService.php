<?php

namespace App\Services;

use App\Models\DepotTransferRequest;
use App\Models\DepotTransferRequestItem;
use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Support\PackQuantity;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepotTransferService
{
    public function __construct(
        private readonly StockService $stocks,
    ) {
    }

    public function create(array $data, array $items): DepotTransferRequest
    {
        return DB::transaction(function () use ($data, $items) {
            $this->assertDestination($data);

            $request = DepotTransferRequest::query()->create([
                'request_number' => $this->nextRequestNumber(),
                'source_depot_id' => $data['source_depot_id'],
                'pharmacy_id' => $data['pharmacy_id'] ?? null,
                'destination_depot_id' => $data['destination_depot_id'] ?? null,
                'status' => DepotTransferRequest::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            $this->syncItems($request, $items);

            return $request->fresh(['items.medicine', 'sourceDepot', 'pharmacy', 'destinationDepot']);
        });
    }

    public function update(DepotTransferRequest $request, array $data, array $items): DepotTransferRequest
    {
        if ($request->status !== DepotTransferRequest::STATUS_DRAFT) {
            throw ValidationException::withMessages(['status' => 'Only draft transfers can be updated.']);
        }

        return DB::transaction(function () use ($request, $data, $items) {
            $this->assertDestination($data);

            $request->update([
                'source_depot_id' => $data['source_depot_id'] ?? $request->source_depot_id,
                'pharmacy_id' => $data['pharmacy_id'] ?? $request->pharmacy_id,
                'destination_depot_id' => $data['destination_depot_id'] ?? $request->destination_depot_id,
                'notes' => $data['notes'] ?? $request->notes,
            ]);

            $request->items()->delete();
            $this->syncItems($request, $items);

            return $request->fresh(['items.medicine', 'sourceDepot', 'pharmacy', 'destinationDepot']);
        });
    }

    public function submit(DepotTransferRequest $request): DepotTransferRequest
    {
        $this->assertStatus($request, [DepotTransferRequest::STATUS_DRAFT]);

        if ($request->items()->count() < 1) {
            throw ValidationException::withMessages(['items' => 'Add at least one item.']);
        }

        $request->update([
            'status' => DepotTransferRequest::STATUS_PENDING,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        return $request->fresh(['items.medicine']);
    }

    public function approve(DepotTransferRequest $request): DepotTransferRequest
    {
        $this->assertStatus($request, [DepotTransferRequest::STATUS_PENDING]);

        // Approve + move stock in one step (depot ↓ / pharmacy ↑).
        return $this->fulfill($request);
    }

    public function reject(DepotTransferRequest $request, ?string $reason = null): DepotTransferRequest
    {
        $this->assertStatus($request, [DepotTransferRequest::STATUS_PENDING, DepotTransferRequest::STATUS_APPROVED]);

        $request->update([
            'status' => DepotTransferRequest::STATUS_REJECTED,
            'rejected_by' => auth()->id(),
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);

        return $request->fresh();
    }

    public function cancel(DepotTransferRequest $request): DepotTransferRequest
    {
        $this->assertStatus($request, [
            DepotTransferRequest::STATUS_DRAFT,
            DepotTransferRequest::STATUS_PENDING,
            DepotTransferRequest::STATUS_APPROVED,
        ]);

        $request->update(['status' => DepotTransferRequest::STATUS_CANCELLED]);

        return $request->fresh();
    }

    public function fulfill(DepotTransferRequest $request): DepotTransferRequest
    {
        $this->assertStatus($request, [DepotTransferRequest::STATUS_APPROVED, DepotTransferRequest::STATUS_PENDING]);

        return DB::transaction(function () use ($request) {
            $request->load(['items.medicine', 'sourceDepot', 'pharmacy', 'destinationDepot']);

            $toType = $request->pharmacy_id
                ? Stock::LOCATION_PHARMACY
                : Stock::LOCATION_DEPOT;
            $toId = $request->pharmacy_id
                ? (int) $request->pharmacy_id
                : (int) $request->destination_depot_id;

            $movementType = $request->pharmacy_id
                ? StockMovement::TYPE_DEPOT_TO_PHARMACY
                : StockMovement::TYPE_DEPOT_TO_DEPOT;

            foreach ($request->items as $item) {
                $moved = $this->stocks->transfer(
                    Stock::LOCATION_DEPOT,
                    (int) $request->source_depot_id,
                    $toType,
                    $toId,
                    $item->medicine,
                    (int) $item->qty_units,
                    $item->batch_number,
                    $request,
                    $movementType
                );

                $first = $moved[0] ?? null;
                if ($first) {
                    $item->forceFill([
                        'from_stock_id' => $first['from_stock']->id,
                        'to_stock_id' => $first['to_stock']->id,
                        'batch_number' => $first['batch_number'],
                    ])->save();
                }
            }

            $request->update([
                'status' => DepotTransferRequest::STATUS_FULFILLED,
                'fulfilled_by' => auth()->id(),
                'fulfilled_at' => now(),
                'approved_by' => $request->approved_by ?? auth()->id(),
                'approved_at' => $request->approved_at ?? now(),
            ]);

            return $request->fresh(['items.medicine', 'items.fromStock', 'items.toStock', 'sourceDepot', 'pharmacy']);
        });
    }

    private function syncItems(DepotTransferRequest $request, array $items): void
    {
        foreach ($items as $row) {
            $medicine = Medicine::query()->findOrFail($row['medicine_id']);
            $unitsPerPack = max(1, (int) ($row['units_per_pack'] ?? $medicine->unitsPerPack()));
            $packs = (int) ($row['packs'] ?? 0);
            $units = (int) ($row['units'] ?? 0);
            $qtyUnits = PackQuantity::toUnits($packs, $units, $unitsPerPack);

            DepotTransferRequestItem::query()->create([
                'depot_transfer_request_id' => $request->id,
                'medicine_id' => $medicine->id,
                'batch_number' => isset($row['batch_number']) ? trim((string) $row['batch_number']) : null,
                'packs' => $packs,
                'units' => $units,
                'qty_units' => $qtyUnits,
                'units_per_pack' => $unitsPerPack,
            ]);
        }
    }

    private function assertDestination(array $data): void
    {
        $hasPharmacy = ! empty($data['pharmacy_id']);
        $hasDepot = ! empty($data['destination_depot_id']);

        if ($hasPharmacy === $hasDepot) {
            throw ValidationException::withMessages([
                'pharmacy_id' => 'Choose either a pharmacy or a destination depot (not both).',
            ]);
        }
    }

    /**
     * @param  array<int, string>  $allowed
     */
    private function assertStatus(DepotTransferRequest $request, array $allowed): void
    {
        if (! in_array($request->status, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => 'This transfer cannot be updated from status ['.$request->status.'].',
            ]);
        }
    }

    private function nextRequestNumber(): string
    {
        $prefix = 'TR-'.now()->format('Ymd').'-';
        $last = DepotTransferRequest::query()
            ->withTrashed()
            ->where('request_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('request_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
