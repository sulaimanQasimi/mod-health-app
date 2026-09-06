<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\BloodBank;
use App\Models\BloodCheckRecord;
use App\Models\BloodCrossmatch;
use App\Models\BloodBankTest;
use App\Models\BloodPatientSample;
use App\Models\BloodUnit;
use App\Services\BloodCrossmatchService;
use App\Support\PersianDateParser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

trait ManagesBloodBankWorkflow
{
    protected function ensureCanManageCrossmatch(Request $request): void
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }
    }

    public function storePatientSample(Request $request, BloodBank $bloodBank): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        $validated = $request->validate([
            'sample_id' => 'nullable|string|max:100',
            'collected_date' => 'nullable|string|max:32',
            'collected_time' => 'nullable|date_format:H:i',
            'collected_at' => 'nullable|string|max:64',
            'notes' => 'nullable|string|max:2000',
        ], [
            'collected_time.date_format' => localize('global.expires_time_invalid'),
        ]);

        $collectedAt = PersianDateParser::parseDateTimeOrLegacy(
            $validated['collected_date'] ?? null,
            $validated['collected_time'] ?? null,
            $validated['collected_at'] ?? null,
            '00:00',
            'collected_date',
            'collected_time',
        );

        BloodPatientSample::create([
            'blood_bank_id' => $bloodBank->id,
            'patient_id' => $bloodBank->patient_id,
            'sample_id' => $validated['sample_id'] ?? null,
            'collected_at' => $collectedAt ?? now(),
            'collected_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.crossmatch_sample_saved'));
    }

    public function storeBloodCheck(Request $request, BloodBank $bloodBank): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ($bloodBank->status !== 'approved') {
            return redirect()
                ->route('blood-banks.show', $bloodBank)
                ->with('error', localize('global.blood_check_only_when_approved'));
        }

        $componentTypes = BloodCheckRecord::COMPONENT_TYPES;

        $validated = $request->validate([
            'abo_group' => ['required', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh' => ['required', 'string', Rule::in(['+', '-'])],
            'component_type' => ['required', 'string', Rule::in($componentTypes)],
            'quantity' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'patient_typed_group' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'patient_typed_rh' => ['nullable', 'string', Rule::in(['+', '-'])],
            'verify_lab_typing' => ['nullable', 'boolean'],
        ]);

        $userId = (int) $request->user()->id;
        $verify = $request->boolean('verify_lab_typing');

        $payload = [
            'branch_id' => $bloodBank->branch_id,
            'appointment_id' => $bloodBank->appointment_id,
            'patient_id' => $bloodBank->patient_id,
            'department_id' => $bloodBank->department_id,
            'operation_id' => $bloodBank->operation_id,
            'hospitalization_id' => $bloodBank->hospitalization_id,
            'anesthesia_id' => $bloodBank->anesthesia_id,
            'i_c_u_id' => $bloodBank->i_c_u_id,
            'under_review_id' => $bloodBank->under_review_id,
            'abo_group' => $validated['abo_group'],
            'rh' => $validated['rh'],
            'component_type' => $validated['component_type'],
            'quantity' => (int) $validated['quantity'],
            'status' => $bloodBank->status,
            'notes' => $validated['notes'] ?? null,
            'patient_typed_group' => $validated['patient_typed_group'] ?? null,
            'patient_typed_rh' => $validated['patient_typed_rh'] ?? null,
            'updated_by' => $userId,
        ];

        if ($verify) {
            $payload['verified_at'] = now();
            $payload['verified_by'] = $userId;
        }

        $existing = BloodCheckRecord::where('blood_bank_id', $bloodBank->id)->first();

        BloodCheckRecord::updateOrCreate(
            ['blood_bank_id' => $bloodBank->id],
            array_merge($payload, [
                'created_by' => $existing?->created_by ?? $userId,
            ])
        );

        $bloodBank->update([
            'group' => $validated['abo_group'],
            'rh' => $validated['rh'],
            'type' => $validated['component_type'],
            'quantity' => (int) $validated['quantity'],
        ]);

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.blood_check_saved'));
    }

    public function fillBloodBankTest(Request $request, BloodBank $bloodBank, BloodBankTest $bloodBankTest): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ((int) $bloodBankTest->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        if ($bloodBank->status !== 'approved') {
            return redirect()
                ->route('blood-banks.show', $bloodBank)
                ->with('error', localize('global.blood_bank_tests_only_when_approved'));
        }

        $validated = $request->validate([
            'result' => ['required', Rule::in(BloodBankTest::RESULT_VALUES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $bloodBankTest->update([
            'result' => $validated['result'],
            'notes' => $validated['notes'] ?? null,
            'filled_test_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.blood_bank_test_saved'));
    }

    public function saveCrossmatch(Request $request, BloodBank $bloodBank, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ((int) $bloodUnit->branch_id !== (int) $bloodBank->branch_id) {
            abort(404);
        }

        $validated = $request->validate([
            'patient_sample_id' => ['nullable', 'integer', Rule::exists('blood_patient_samples', 'id')],
            'major_result' => ['required', Rule::in(BloodCrossmatch::RESULT_VALUES)],
            'minor_result' => ['required', Rule::in(BloodCrossmatch::RESULT_VALUES)],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        if (! empty($validated['patient_sample_id'])) {
            $sample = BloodPatientSample::where('id', $validated['patient_sample_id'])
                ->where('blood_bank_id', $bloodBank->id)
                ->first();
            if (! $sample) {
                return redirect()
                    ->route('blood-banks.show', $bloodBank)
                    ->with('error', localize('global.crossmatch_invalid_sample'));
            }
        }

        $decision = app(BloodCrossmatchService::class)->evaluateCompatibility($bloodBank, $bloodUnit, $validated);

        BloodCrossmatch::updateOrCreate(
            [
                'blood_bank_id' => $bloodBank->id,
                'blood_unit_id' => $bloodUnit->id,
            ],
            [
                'patient_id' => $bloodBank->patient_id,
                'patient_sample_id' => $validated['patient_sample_id'] ?? null,
                'major_result' => $validated['major_result'],
                'minor_result' => $validated['minor_result'],
                'status' => $decision['status'],
                'auto_decision' => $decision['auto_decision'],
                'auto_reason' => $decision['auto_reason'],
                'is_overridden' => false,
                'override_by' => null,
                'override_reason' => null,
                'tested_at' => now(),
                'tested_by' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
            ]
        );

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.crossmatch_saved'));
    }

    public function overrideCrossmatch(Request $request, BloodBank $bloodBank, BloodCrossmatch $crossmatch): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);

        if (! $request->user()->can('manage-blood-inventory')) {
            abort(403);
        }

        if ((int) $crossmatch->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        $validated = $request->validate([
            'override_reason' => ['required', 'string', 'max:1000'],
        ]);

        app(BloodCrossmatchService::class)->overrideCompatible($crossmatch, (int) $request->user()->id, $validated['override_reason']);

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.crossmatch_override_saved'));
    }

    public function reserveCrossmatchUnit(Request $request, BloodBank $bloodBank, BloodCrossmatch $crossmatch): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        if ((int) $crossmatch->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        if (! in_array($crossmatch->status, ['compatible', 'overridden'], true)) {
            return redirect()
                ->route('blood-banks.show', $bloodBank)
                ->with('error', localize('global.crossmatch_cannot_reserve_incompatible'));
        }

        try {
            DB::transaction(function () use ($bloodBank, $crossmatch, $request) {
                $unit = BloodUnit::where('id', $crossmatch->blood_unit_id)
                    ->where('branch_id', $bloodBank->branch_id)
                    ->lockForUpdate()
                    ->first();

                if (! $unit || ! in_array($unit->status, ['available', 'reserved'], true) || $unit->expires_at <= now()) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_not_reservable'));
                }

                $bloodBank->loadMissing(['bloodUnits', 'crossmatches']);
                $unitMl = $bloodBank->effectiveUnitVolumeMl($unit);
                $alreadyReservedMl = $bloodBank->reservedCompatibleVolumeMl();
                if ($alreadyReservedMl + $unitMl > $bloodBank->remainingVolumeMl()) {
                    throw new \RuntimeException(localize('global.crossmatch_reserve_volume_exceeds_remaining'));
                }

                $pivotForUnit = DB::table('blood_bank_unit')
                    ->where('blood_unit_id', $unit->id)
                    ->first();

                if ($pivotForUnit && (int) $pivotForUnit->blood_bank_id !== (int) $bloodBank->id) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_linked_to_other_request'));
                }

                $bloodBank->bloodUnits()->syncWithoutDetaching([
                    $unit->id => [
                        'reserved_at' => now(),
                        'reserved_by' => $request->user()->id,
                        'crossmatch_id' => $crossmatch->id,
                    ],
                ]);
                $unit->status = 'reserved';
                $unit->save();
            });
        } catch (\Throwable $e) {
            return redirect()
                ->route('blood-banks.show', $bloodBank)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.crossmatch_unit_reserved'));
    }

    public function unreserveCrossmatchUnit(Request $request, BloodBank $bloodBank, BloodUnit $bloodUnit): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        $this->ensureCanManageCrossmatch($request);

        try {
            DB::transaction(function () use ($bloodBank, $bloodUnit) {
                $row = DB::table('blood_bank_unit')
                    ->where('blood_bank_id', $bloodBank->id)
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->lockForUpdate()
                    ->first();
                if (! $row) {
                    throw new \RuntimeException(localize('global.crossmatch_unit_not_reserved_for_request'));
                }

                DB::table('blood_bank_unit')
                    ->where('blood_bank_id', $bloodBank->id)
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->update([
                        'reserved_at' => null,
                        'reserved_by' => null,
                        'crossmatch_id' => null,
                        'updated_at' => now(),
                    ]);

                $hasOtherReservations = DB::table('blood_bank_unit')
                    ->where('blood_unit_id', $bloodUnit->id)
                    ->whereNotNull('reserved_at')
                    ->exists();
                if (! $hasOtherReservations && $bloodUnit->status === 'reserved') {
                    $bloodUnit->status = 'available';
                    $bloodUnit->save();
                }
            });
        } catch (\Throwable $e) {
            return redirect()
                ->route('blood-banks.show', $bloodBank)
                ->with('error', $e->getMessage());
        }

        return redirect()
            ->route('blood-banks.show', $bloodBank)
            ->with('success', localize('global.crossmatch_unit_unreserved'));
    }
}
