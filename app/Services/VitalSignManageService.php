<?php

namespace App\Services;

use App\Models\Hospitalization;
use App\Models\Nurse;
use App\Models\UnderReview;
use App\Models\VitalSign;
use App\Models\VitalSignSchedule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VitalSignManageService
{
    /** @var array<string, class-string<Model>> */
    private const MORPHABLE_TYPES = [
        'App\\Models\\Hospitalization' => Hospitalization::class,
        'App\\Models\\UnderReview' => UnderReview::class,
    ];

    public function resolveMorphable(string $morphableType, int $morphableId): ?Model
    {
        $modelClass = self::MORPHABLE_TYPES[$morphableType] ?? null;

        if (!$modelClass) {
            return null;
        }

        return $modelClass::query()->find($morphableId);
    }

    public function isAllowedMorphableType(string $morphableType): bool
    {
        return isset(self::MORPHABLE_TYPES[$morphableType]);
    }

    /**
     * @return Collection<int, VitalSign>
     */
    public function loadVitalSignsForMorphable(string $morphableType, int $morphableId): Collection
    {
        return VitalSign::query()
            ->with(['vitalSignType', 'schedules' => fn ($q) => $q->orderBy('id')])
            ->where('morphable_type', $morphableType)
            ->where('morphable_id', $morphableId)
            ->orderBy('id')
            ->get();
    }

    /**
     * Persist creates, updates, and deletions for one morphable record.
     */
    public function syncMorphable(
        string $morphableType,
        int $morphableId,
        array $newVitalSigns,
        array $existingVitalSigns,
        array $deleteVitalSignIds,
        array $deleteScheduleIds,
        ?Nurse $authNurse,
        callable $authorizeUpdate,
        callable $authorizeDelete
    ): void {
        $deleteVitalSignIds = array_values(array_unique(array_map('intval', $deleteVitalSignIds)));
        $deleteScheduleIds = array_values(array_unique(array_map('intval', $deleteScheduleIds)));

        DB::transaction(function () use (
            $morphableType,
            $morphableId,
            $newVitalSigns,
            $existingVitalSigns,
            $deleteVitalSignIds,
            $deleteScheduleIds,
            $authNurse,
            $authorizeUpdate,
            $authorizeDelete
        ) {
            $this->deleteSchedules($morphableType, $morphableId, $deleteScheduleIds, $authorizeDelete);
            $this->deleteVitalSigns($morphableType, $morphableId, $deleteVitalSignIds, $authorizeDelete);

            $deleteSet = array_flip($deleteVitalSignIds);

            foreach ($existingVitalSigns as $row) {
                $vitalSignId = (int) ($row['id'] ?? 0);
                if ($vitalSignId < 1 || isset($deleteSet[$vitalSignId])) {
                    continue;
                }

                $vitalSign = $this->findMorphableVitalSign($morphableType, $morphableId, $vitalSignId);
                if (!$vitalSign) {
                    continue;
                }

                $authorizeUpdate($vitalSign);

                $vitalSign->update([
                    'vital_sign_type_id' => (int) $row['vital_sign_type_id'],
                ]);

                $schedules = is_array($row['schedules'] ?? null) ? $row['schedules'] : [];
                $this->syncSchedulesForVitalSign($vitalSign, $schedules, $authNurse, $authorizeUpdate);
            }

            foreach ($newVitalSigns as $row) {
                $typeId = (int) ($row['vital_sign_type_id'] ?? 0);
                if ($typeId < 1) {
                    continue;
                }

                $vitalSign = VitalSign::create([
                    'vital_sign_type_id' => $typeId,
                    'morphable_type' => $morphableType,
                    'morphable_id' => $morphableId,
                ]);

                $schedules = is_array($row['schedules'] ?? null) ? $row['schedules'] : [];
                $this->createSchedulesForVitalSign($vitalSign, $schedules, $authNurse);
            }
        });
    }

    private function deleteSchedules(
        string $morphableType,
        int $morphableId,
        array $deleteScheduleIds,
        callable $authorizeDelete
    ): void {
        if ($deleteScheduleIds === []) {
            return;
        }

        VitalSignSchedule::query()
            ->whereIn('id', $deleteScheduleIds)
            ->whereHas(
                'vitalSign',
                fn ($q) => $q->where('morphable_type', $morphableType)->where('morphable_id', $morphableId)
            )
            ->each(function (VitalSignSchedule $schedule) use ($authorizeDelete) {
                $authorizeDelete($schedule);
                $schedule->delete();
            });
    }

    private function deleteVitalSigns(
        string $morphableType,
        int $morphableId,
        array $deleteVitalSignIds,
        callable $authorizeDelete
    ): void {
        foreach ($deleteVitalSignIds as $vitalSignId) {
            $vitalSign = $this->findMorphableVitalSign($morphableType, $morphableId, $vitalSignId);
            if (!$vitalSign) {
                continue;
            }

            $authorizeDelete($vitalSign);
            $vitalSign->schedules()->delete();
            $vitalSign->delete();
        }
    }

    private function findMorphableVitalSign(string $morphableType, int $morphableId, int $vitalSignId): ?VitalSign
    {
        return VitalSign::query()
            ->where('id', $vitalSignId)
            ->where('morphable_type', $morphableType)
            ->where('morphable_id', $morphableId)
            ->first();
    }

    /**
     * @param  array<int, array<string, mixed>>  $schedules
     */
    private function syncSchedulesForVitalSign(
        VitalSign $vitalSign,
        array $schedules,
        ?Nurse $authNurse,
        callable $authorizeUpdate
    ): void {
        $existingDays = $this->existingDayLabels($vitalSign->id);
        $newRows = [];

        foreach ($schedules as $scheduleRow) {
            $scheduleId = (int) ($scheduleRow['id'] ?? 0);
            $payload = $this->schedulePayload($scheduleRow);

            if ($scheduleId > 0) {
                $schedule = VitalSignSchedule::query()
                    ->where('id', $scheduleId)
                    ->where('vital_sign_id', $vitalSign->id)
                    ->first();

                if (!$schedule) {
                    continue;
                }

                $authorizeUpdate($schedule);

                if ($this->scheduleIsEmpty($payload)) {
                    $schedule->delete();
                    continue;
                }

                $schedule->update($payload);
                continue;
            }

            if ($this->scheduleIsEmpty($payload)) {
                continue;
            }

            $newRows[] = $payload;
        }

        $this->createSchedulesForVitalSign($vitalSign, $newRows, $authNurse, $existingDays);
    }

    /**
     * @param  array<int, array<string, mixed>>  $schedules
     * @param  array<int, string>  $existingDays
     */
    private function createSchedulesForVitalSign(
        VitalSign $vitalSign,
        array $schedules,
        ?Nurse $authNurse,
        ?array $existingDays = null
    ): void {
        $existingDays ??= $this->existingDayLabels($vitalSign->id);
        $nurseId = $authNurse?->id;

        foreach ($schedules as $scheduleRow) {
            $payload = $this->schedulePayload(is_array($scheduleRow) ? $scheduleRow : []);

            if ($this->scheduleIsEmpty($payload)) {
                continue;
            }

            $dayNumber = $this->nextDayNumber($existingDays);

            VitalSignSchedule::create([
                'vital_sign_id' => $vitalSign->id,
                'day' => 'Day ' . $dayNumber,
                'date' => $payload['date'],
                'morning_time' => $payload['morning_time'],
                'evening_time' => $payload['evening_time'],
                'nurse_id' => $nurseId,
            ]);

            $existingDays[] = 'Day ' . $dayNumber;
        }
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{date: ?string, morning_time: ?string, evening_time: ?string}
     */
    private function schedulePayload(array $row): array
    {
        return [
            'date' => !empty($row['date']) ? $row['date'] : null,
            'morning_time' => !empty($row['morning_time']) ? $row['morning_time'] : null,
            'evening_time' => !empty($row['evening_time']) ? $row['evening_time'] : null,
        ];
    }

    /**
     * @param  array{date: ?string, morning_time: ?string, evening_time: ?string}  $payload
     */
    private function scheduleIsEmpty(array $payload): bool
    {
        return !$payload['date'] && !$payload['morning_time'] && !$payload['evening_time'];
    }

    /** @return array<int, string> */
    private function existingDayLabels(int $vitalSignId): array
    {
        return VitalSignSchedule::query()
            ->where('vital_sign_id', $vitalSignId)
            ->whereNotNull('day')
            ->pluck('day')
            ->all();
    }

    /** @param  array<int, string>  $existingDays */
    private function nextDayNumber(array $existingDays): int
    {
        $dayNumber = 1;
        while (in_array('Day ' . $dayNumber, $existingDays, true)) {
            $dayNumber++;
        }

        return $dayNumber;
    }
}
