<?php

namespace App\Services;

use App\Models\BloodBank;
use App\Models\BloodCrossmatch;
use App\Models\BloodUnit;

class BloodCrossmatchService
{
    public function evaluateCompatibility(BloodBank $request, BloodUnit $unit, array $payload): array
    {
        $major = $payload['major_result'] ?? 'pending';
        $minor = $payload['minor_result'] ?? 'pending';
        $status = 'pending';
        $reason = localize('global.crossmatch_pending_results');

        if ($major === 'incompatible' || $minor === 'incompatible') {
            $status = 'incompatible';
            $reason = localize('global.crossmatch_lab_incompatible');
        } elseif ($major === 'compatible' && $minor === 'compatible') {
            if (! $this->isAboCompatible($request->group, $unit->blood_group)) {
                $status = 'incompatible';
                $reason = localize('global.crossmatch_abo_mismatch');
            } elseif (! $this->isRhCompatible($request->rh, $unit->rh)) {
                $status = 'incompatible';
                $reason = localize('global.crossmatch_rh_mismatch');
            } elseif ($request->type !== null && trim((string) $request->type) !== '' && $request->type !== $unit->component_type) {
                $status = 'incompatible';
                $reason = localize('global.crossmatch_component_mismatch');
            } else {
                $status = 'compatible';
                $reason = localize('global.crossmatch_auto_compatible');
            }
        }

        return [
            'status' => $status,
            'auto_decision' => true,
            'auto_reason' => $reason,
        ];
    }

    public function overrideCompatible(BloodCrossmatch $crossmatch, int $userId, string $reason): BloodCrossmatch
    {
        $crossmatch->status = 'overridden';
        $crossmatch->is_overridden = true;
        $crossmatch->override_by = $userId;
        $crossmatch->override_reason = $reason;
        $crossmatch->save();

        return $crossmatch->fresh();
    }

    public function isAboCompatible(?string $recipientGroup, string $donorGroup): bool
    {
        if ($recipientGroup === null || trim($recipientGroup) === '') {
            return true;
        }

        $matrix = [
            'O' => ['O'],
            'A' => ['O', 'A'],
            'B' => ['O', 'B'],
            'AB' => ['O', 'A', 'B', 'AB'],
        ];

        return in_array($donorGroup, $matrix[$recipientGroup] ?? [], true);
    }

    public function isRhCompatible(?string $recipientRh, string $donorRh): bool
    {
        if ($recipientRh === null || trim($recipientRh) === '') {
            return true;
        }

        if ($recipientRh === '+') {
            return in_array($donorRh, ['+', '-'], true);
        }

        return $donorRh === '-';
    }
}
