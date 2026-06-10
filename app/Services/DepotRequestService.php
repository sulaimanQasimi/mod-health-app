<?php

namespace App\Services;

use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Models\DepotRequestStatusLog;
use App\Models\DepotTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepotRequestService
{
    public function __construct(
        private readonly DepotStockService $stockService
    ) {
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function syncItems(DepotRequest $request, array $items): void
    {
        $request->items()->delete();

        foreach (array_values($items) as $index => $item) {
            $request->items()->create([
                'medicine_id' => $item['medicine_id'] ?? null,
                'tool_id' => $item['tool_id'] ?? null,
                'unit_id' => $item['unit_id'] ?? null,
                'quantity' => (int) $item['quantity'],
                'batch_number' => $item['batch_number'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }

    public function submit(DepotRequest $request): DepotRequest
    {
        if ($request->status !== DepotRequest::STATUS_DRAFT) {
            throw ValidationException::withMessages([
                'status' => 'Only draft requests can be submitted.',
            ]);
        }

        $request->loadCount('items');

        if ($request->items_count < 1) {
            throw ValidationException::withMessages([
                'items' => 'Add at least one transfer line before submitting.',
            ]);
        }

        $requestingDepot = $request->requestingDepot;
        if (! $requestingDepot || ! $requestingDepot->is_active) {
            throw ValidationException::withMessages([
                'requesting_depot_id' => 'Requesting depot must be active.',
            ]);
        }

        $sourceDepot = $request->sourceDepot;
        if (! $sourceDepot || ! $sourceDepot->is_active) {
            throw ValidationException::withMessages([
                'source_depot_id' => 'Source depot must be active.',
            ]);
        }

        return $this->transition($request, DepotRequest::STATUS_PENDING, 'Submitted for approval');
    }

    public function approve(DepotRequest $request): DepotRequest
    {
        if ($request->status !== DepotRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be approved.',
            ]);
        }

        return DB::transaction(function () use ($request) {
            $request = $this->transition($request, DepotRequest::STATUS_APPROVED, 'Approved');

            $request->update([
                'approved_by' => Auth::id(),
                'approved_at' => now(),
            ]);

            return $request->fresh();
        });
    }

    public function reject(DepotRequest $request, string $reason): DepotRequest
    {
        if ($request->status !== DepotRequest::STATUS_PENDING) {
            throw ValidationException::withMessages([
                'status' => 'Only pending requests can be rejected.',
            ]);
        }

        return DB::transaction(function () use ($request, $reason) {
            $request->update(['rejection_reason' => $reason]);

            return $this->transition($request, DepotRequest::STATUS_REJECTED, $reason);
        });
    }

    public function fulfill(DepotRequest $request): DepotRequest
    {
        if ($request->status !== DepotRequest::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => 'Only approved requests can be fulfilled.',
            ]);
        }

        $request->load('items');

        if ($request->items->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'This request has no transfer lines to fulfill.',
            ]);
        }

        return DB::transaction(function () use ($request) {
            Depot::whereKey($request->source_depot_id)->lockForUpdate()->firstOrFail();
            Depot::whereKey($request->requesting_depot_id)->lockForUpdate()->firstOrFail();

            foreach ($request->items as $item) {
                if ($item->depot_transaction_id) {
                    continue;
                }

                $itemType = $item->itemType();
                $itemId = $itemType === DepotTransaction::ITEM_MEDICINE
                    ? (int) $item->medicine_id
                    : (int) $item->tool_id;

                $this->stockService->lockLedger((int) $request->source_depot_id, $itemType, $itemId);
                $this->stockService->ensureAvailable($itemType, (int) $request->source_depot_id, $itemId, (int) $item->quantity);

                $transaction = DepotTransaction::create([
                    'depot_id' => $request->source_depot_id,
                    'from_depot_id' => $request->source_depot_id,
                    'to_depot_id' => $request->requesting_depot_id,
                    'depot_request_id' => $request->id,
                    'medicine_id' => $item->medicine_id,
                    'tool_id' => $item->tool_id,
                    'unit_id' => $item->unit_id,
                    'batch_number' => $item->batch_number,
                    'quantity' => $item->quantity,
                    'type' => DepotTransaction::TYPE_DEPOT_TO_DEPOT,
                    'transaction_type' => 'transfer',
                    'status' => DepotTransaction::STATUS_COMPLETED,
                    'notes' => $request->notes,
                    'user_id' => Auth::id(),
                ]);

                $item->update(['depot_transaction_id' => $transaction->id]);
            }

            $request->update([
                'fulfilled_by' => Auth::id(),
                'fulfilled_at' => now(),
            ]);

            return $this->transition($request, DepotRequest::STATUS_FULFILLED, 'Fulfilled with transfers');
        });
    }

    public function cancel(DepotRequest $request): DepotRequest
    {
        if (! in_array($request->status, [DepotRequest::STATUS_DRAFT, DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED], true)) {
            throw ValidationException::withMessages([
                'status' => 'This request cannot be cancelled.',
            ]);
        }

        return $this->transition($request, DepotRequest::STATUS_CANCELLED, 'Cancelled');
    }

    private function transition(DepotRequest $request, string $toStatus, ?string $notes = null): DepotRequest
    {
        $fromStatus = $request->status;

        $request->update(['status' => $toStatus]);

        DepotRequestStatusLog::create([
            'depot_request_id' => $request->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'user_id' => Auth::id(),
            'notes' => $notes,
        ]);

        return $request->fresh();
    }
}
