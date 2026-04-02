<?php

namespace App\Services;

use App\Models\BloodBank;
use App\Models\BloodBranchTransfer;
use App\Models\BloodCrossmatch;
use App\Models\BloodStockMovement;
use App\Models\BloodUnit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BloodBankStockService
{
    /**
     * Auto-archive expired units so they can never be treated as available stock.
     */
    public function archiveExpiredUnits(?int $branchId = null, ?int $actorId = null): int
    {
        return DB::transaction(function () use ($branchId, $actorId) {
            $query = BloodUnit::query()
                ->whereIn('status', ['available', 'reserved', 'quarantine'])
                ->where('expires_at', '<=', now())
                ->lockForUpdate();

            if ($branchId !== null) {
                $query->where('branch_id', $branchId);
            }

            $expiredUnits = $query->get();
            if ($expiredUnits->isEmpty()) {
                return 0;
            }

            foreach ($expiredUnits as $unit) {
                if ($unit->status === 'reserved') {
                    DB::table('blood_bank_unit')
                        ->where('blood_unit_id', $unit->id)
                        ->whereNotNull('reserved_at')
                        ->update([
                            'reserved_at' => null,
                            'reserved_by' => null,
                            'crossmatch_id' => null,
                            'updated_at' => now(),
                        ]);
                }

                $unit->status = 'discarded';
                $unit->save();

                BloodStockMovement::create([
                    'blood_unit_id' => $unit->id,
                    'movement_type' => 'discarded',
                    'notes' => localize('global.blood_unit_auto_archived_expired'),
                    'user_id' => $actorId,
                ]);
            }

            return $expiredUnits->count();
        });
    }

    /**
     * Register a new blood unit (intake) and record a received movement.
     *
     * @param  array<string, mixed>  $data
     */
    public function receiveUnit(array $data): BloodUnit
    {
        return DB::transaction(function () use ($data) {
            $unit = BloodUnit::create([
                'branch_id' => $data['branch_id'],
                'donation_id' => $data['donation_id'] ?? null,
                'blood_group' => $data['blood_group'],
                'rh' => $data['rh'],
                'component_type' => $data['component_type'],
                'bag_number' => $data['bag_number'],
                'volume_ml' => $data['volume_ml'] ?? null,
                'collected_at' => $data['collected_at'] ?? null,
                'expires_at' => $data['expires_at'],
                'status' => 'quarantine',
            ]);

            BloodStockMovement::create([
                'blood_unit_id' => $unit->id,
                'movement_type' => 'received',
                'notes' => $data['notes'] ?? null,
                'user_id' => auth()->id(),
            ]);

            return $unit->fresh();
        });
    }

    /**
     * Issue units for an approved blood request (FIFO or explicit ids).
     *
     * @param  list<int>|null  $unitIds  If empty/null, picks FIFO by expiry.
     *
     * @throws \RuntimeException
     */
    public function deliverRequest(BloodBank $request, ?array $unitIds = null): void
    {
        if ($request->status !== 'approved') {
            throw new \RuntimeException(localize('global.blood_request_must_be_approved_to_deliver'));
        }

        $qty = max(1, (int) $request->quantity);

        DB::transaction(function () use ($request, $unitIds, $qty) {
            $units = $this->resolveUnitsForDelivery($request, $unitIds, $qty);

            foreach ($units as $unit) {
                $unit->status = 'issued';
                $unit->save();

                $request->bloodUnits()->syncWithoutDetaching([
                    $unit->id => [
                        'issued_at' => now(),
                        'issued_by' => auth()->id(),
                    ],
                ]);

                BloodStockMovement::create([
                    'blood_unit_id' => $unit->id,
                    'movement_type' => 'issued',
                    'reference_type' => BloodBank::class,
                    'reference_id' => $request->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $request->status = 'delivered';
            $request->save();
        });
    }

    /**
     * @return Collection<int, BloodUnit>
     */
    protected function resolveUnitsForDelivery(BloodBank $request, ?array $unitIds, int $qty): Collection
    {
        if ($this->hasCrossmatchWorkflow($request)) {
            return $this->resolveReservedCrossmatchUnits($request, $unitIds, $qty);
        }

        $base = BloodUnit::query()
            ->where('branch_id', $request->branch_id)
            ->where('blood_group', $request->group)
            ->where('rh', $request->rh)
            ->where('component_type', $request->type)
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->whereHas('test', fn ($q) => $q->where('overall_status', 'passed'))
            ->orderBy('expires_at')
            ->lockForUpdate();

        if ($unitIds !== null && count($unitIds) > 0) {
            if (count($unitIds) !== $qty) {
                throw new \RuntimeException(localize('global.blood_delivery_unit_count_mismatch'));
            }

            $units = (clone $base)->whereIn('id', $unitIds)->get();

            if ($units->count() !== $qty) {
                throw new \RuntimeException(localize('global.insufficient_blood_stock'));
            }

            foreach ($units as $u) {
                if (
                    $u->blood_group !== $request->group
                    || $u->rh !== $request->rh
                    || $u->component_type !== $request->type
                    || (int) $u->branch_id !== (int) $request->branch_id
                ) {
                    throw new \RuntimeException(localize('global.blood_unit_selection_invalid'));
                }
            }

            return $units;
        }

        $picked = (clone $base)->limit($qty)->get();

        if ($picked->count() < $qty) {
            throw new \RuntimeException(localize('global.insufficient_blood_stock'));
        }

        return $picked;
    }

    protected function hasCrossmatchWorkflow(BloodBank $request): bool
    {
        return BloodCrossmatch::where('blood_bank_id', $request->id)->exists();
    }

    /**
     * @return Collection<int, BloodUnit>
     */
    protected function resolveReservedCrossmatchUnits(BloodBank $request, ?array $unitIds, int $qty): Collection
    {
        $query = BloodUnit::query()
            ->select('blood_units.*')
            ->join('blood_bank_unit', function ($join) use ($request) {
                $join->on('blood_bank_unit.blood_unit_id', '=', 'blood_units.id')
                    ->where('blood_bank_unit.blood_bank_id', '=', $request->id)
                    ->whereNotNull('blood_bank_unit.reserved_at');
            })
            ->join('blood_crossmatches', function ($join) use ($request) {
                $join->on('blood_crossmatches.blood_unit_id', '=', 'blood_units.id')
                    ->where('blood_crossmatches.blood_bank_id', '=', $request->id);
            })
            ->where('blood_units.branch_id', $request->branch_id)
            ->whereIn('blood_units.status', ['available', 'reserved'])
            ->where('blood_units.expires_at', '>', now())
            ->whereIn('blood_crossmatches.status', ['compatible', 'overridden'])
            ->orderBy('blood_units.expires_at')
            ->lockForUpdate();

        if ($unitIds !== null && count($unitIds) > 0) {
            if (count($unitIds) !== $qty) {
                throw new \RuntimeException(localize('global.blood_delivery_unit_count_mismatch'));
            }

            $units = (clone $query)->whereIn('blood_units.id', $unitIds)->get();
            if ($units->count() !== $qty) {
                throw new \RuntimeException(localize('global.crossmatch_delivery_requires_reserved_units'));
            }

            return $units;
        }

        $units = (clone $query)->limit($qty)->get();
        if ($units->count() < $qty) {
            throw new \RuntimeException(localize('global.crossmatch_delivery_requires_reserved_units'));
        }

        return $units;
    }

    /**
     * Move units from supplying branch to requesting branch (inter-branch transfer).
     *
     * @param  list<int>|null  $unitIds  FIFO by expiry when null or empty.
     *
     * @throws \RuntimeException
     */
    public function fulfillBranchTransfer(BloodBranchTransfer $transfer, ?array $unitIds = null): void
    {
        if ($transfer->status !== 'pending') {
            throw new \RuntimeException(localize('global.blood_branch_transfer_not_pending'));
        }

        if ((int) $transfer->requesting_branch_id === (int) $transfer->supplying_branch_id) {
            throw new \RuntimeException(localize('global.blood_branch_transfer_same_branch'));
        }

        $qty = max(1, (int) $transfer->quantity);

        DB::transaction(function () use ($transfer, $unitIds, $qty) {
            $base = BloodUnit::query()
                ->where('branch_id', $transfer->supplying_branch_id)
                ->where('blood_group', $transfer->blood_group)
                ->where('rh', $transfer->rh)
                ->where('component_type', $transfer->component_type)
                ->where('status', 'available')
                ->where('expires_at', '>', now())
                ->whereHas('test', fn ($q) => $q->where('overall_status', 'passed'))
                ->orderBy('expires_at')
                ->lockForUpdate();

            if ($unitIds !== null && count($unitIds) > 0) {
                if (count($unitIds) !== $qty) {
                    throw new \RuntimeException(localize('global.blood_delivery_unit_count_mismatch'));
                }

                $units = (clone $base)->whereIn('id', $unitIds)->get();

                if ($units->count() !== $qty) {
                    throw new \RuntimeException(localize('global.insufficient_blood_stock'));
                }

                foreach ($units as $u) {
                    if (
                        (int) $u->branch_id !== (int) $transfer->supplying_branch_id
                        || $u->blood_group !== $transfer->blood_group
                        || $u->rh !== $transfer->rh
                        || $u->component_type !== $transfer->component_type
                    ) {
                        throw new \RuntimeException(localize('global.blood_unit_selection_invalid'));
                    }
                }
            } else {
                $units = (clone $base)->limit($qty)->get();
                if ($units->count() < $qty) {
                    throw new \RuntimeException(localize('global.insufficient_blood_stock'));
                }
            }

            foreach ($units as $unit) {
                $unit->branch_id = $transfer->requesting_branch_id;
                $unit->save();

                BloodStockMovement::create([
                    'blood_unit_id' => $unit->id,
                    'movement_type' => 'transferred',
                    'reference_type' => BloodBranchTransfer::class,
                    'reference_id' => $transfer->id,
                    'notes' => null,
                    'user_id' => auth()->id(),
                ]);
            }

            $transfer->status = 'completed';
            $transfer->fulfilled_at = now();
            $transfer->fulfilled_by = auth()->id();
            $transfer->save();
        });
    }

    /**
     * Remove a unit from usable stock (damaged, wasted, etc.).
     *
     * @throws \RuntimeException
     */
    public function discardUnit(BloodUnit $unit, ?string $reason = null): void
    {
        DB::transaction(function () use ($unit, $reason) {
            $unit->refresh();

            if (! in_array($unit->status, ['available', 'quarantine'], true)) {
                throw new \RuntimeException(localize('global.blood_unit_cannot_discard'));
            }

            $unit->status = 'discarded';
            $unit->save();

            BloodStockMovement::create([
                'blood_unit_id' => $unit->id,
                'movement_type' => 'discarded',
                'notes' => $reason,
                'user_id' => auth()->id(),
            ]);
        });
    }

    /**
     * Toggle quarantine (hold) status for screening or quality control.
     *
     * @throws \RuntimeException
     */
    public function setQuarantine(BloodUnit $unit, bool $on, ?string $reason = null): void
    {
        DB::transaction(function () use ($unit, $on, $reason) {
            $unit->refresh();

            if ($on) {
                if ($unit->status !== 'available') {
                    throw new \RuntimeException(localize('global.blood_unit_quarantine_only_available'));
                }
                $unit->status = 'quarantine';
            } else {
                if ($unit->status !== 'quarantine') {
                    throw new \RuntimeException(localize('global.blood_unit_not_in_quarantine'));
                }
                $unit->status = 'available';
            }

            $unit->save();

            BloodStockMovement::create([
                'blood_unit_id' => $unit->id,
                'movement_type' => 'adjusted',
                'notes' => ($on ? localize('global.blood_quarantine_on') : localize('global.blood_quarantine_off'))
                    .($reason ? ': '.$reason : ''),
                'user_id' => auth()->id(),
            ]);
        });
    }
}
