<?php

namespace App\Services;

use App\Models\BloodUnit;
use App\Models\BloodUnitTest;
use Illuminate\Validation\ValidationException;

class BloodUnitManagementService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function saveTests(BloodUnit $unit, array $validated, int $userId): void
    {
        $overall = $this->computeOverallTestStatus($validated);

        BloodUnitTest::updateOrCreate(
            ['blood_unit_id' => $unit->id],
            array_merge($validated, [
                'overall_status' => $overall,
                'tested_at' => now(),
                'tested_by' => $userId,
            ]),
        );
    }

    public function approveAfterTests(BloodUnit $unit): void
    {
        $unit->load('test');

        if (! $unit->test || $unit->test->overall_status !== 'passed') {
            throw ValidationException::withMessages([
                'tests' => localize('global.blood_unit_tests_must_pass_before_release'),
            ]);
        }

        if ($unit->status === 'quarantine') {
            app(BloodBankStockService::class)->setQuarantine(
                $unit,
                false,
                localize('global.blood_release_after_tests'),
            );
        }
    }

    public function discard(BloodUnit $unit, ?string $reason): void
    {
        app(BloodBankStockService::class)->discardUnit($unit, $reason);
    }

    public function setQuarantine(BloodUnit $unit, bool $quarantine, ?string $reason): void
    {
        app(BloodBankStockService::class)->setQuarantine($unit, $quarantine, $reason);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public function computeOverallTestStatus(array $validated): string
    {
        $keys = ['dct_result', 'ict_result', 'hbs_result', 'hcv_result', 'hiv_result', 'vdrl_result'];

        foreach ($keys as $key) {
            if (($validated[$key] ?? 'pending') === 'positive') {
                return 'failed';
            }
            if (($validated[$key] ?? 'pending') === 'inconclusive' || ($validated[$key] ?? 'pending') === 'pending') {
                return 'pending';
            }
        }

        return 'passed';
    }
}
