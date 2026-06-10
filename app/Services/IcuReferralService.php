<?php

namespace App\Services;

use App\Jobs\SendNewICUNotification;
use App\Models\Appointment;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class IcuReferralService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, User $user): ICU
    {
        return DB::transaction(function () use ($validated, $user) {
            $sourceHospitalization = null;
            if (! empty($validated['hospitalization_id'])) {
                $sourceHospitalization = Hospitalization::query()->find($validated['hospitalization_id']);
            }

            $icu = ICU::create(collect($validated)->except(['room_id', 'bed_id'])->all());

            if (! empty($validated['room_id']) && ! empty($validated['bed_id'])) {
                $this->assignPlacement(
                    $icu,
                    (int) $validated['room_id'],
                    (int) $validated['bed_id'],
                    $sourceHospitalization,
                    $validated,
                    $user,
                );
            } elseif ($sourceHospitalization) {
                $this->clearSourceHospitalizationBed($sourceHospitalization);
            }

            SendNewICUNotification::dispatch($icu->created_by, $icu->id);

            return $icu->fresh(['patient:id,name', 'hospitalization.room:id,name', 'hospitalization.bed:id,number']);
        });
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function assignPlacement(
        ICU $icu,
        int $roomId,
        int $bedId,
        ?Hospitalization $sourceHospitalization,
        array $validated,
        User $user,
    ): void {
        $bed = Bed::query()->findOrFail($bedId);
        if ((bool) $bed->is_occupied) {
            throw ValidationException::withMessages([
                'bed_id' => [localize('global.bed_is_occupied') ?: 'Selected bed is already occupied.'],
            ]);
        }

        if ((int) $bed->room_id !== $roomId) {
            throw ValidationException::withMessages([
                'bed_id' => [localize('global.invalid_bed_for_room') ?: 'Selected bed does not belong to the selected room.'],
            ]);
        }

        if ($sourceHospitalization) {
            $this->clearSourceHospitalizationBed($sourceHospitalization);

            $placement = Hospitalization::create([
                'reason' => localize('global.refere_to_icu') ?: 'Referral to ICU',
                'remarks' => $validated['description'] ?? (localize('global.refere_to_icu') ?: 'ICU referral'),
                'appointment_id' => $sourceHospitalization->appointment_id ?? $validated['appointment_id'] ?? null,
                'patient_id' => $sourceHospitalization->patient_id,
                'doctor_id' => $this->resolveDoctorId($validated, $sourceHospitalization->appointment_id),
                'branch_id' => $sourceHospitalization->branch_id ?? $validated['branch_id'],
                'department_id' => $sourceHospitalization->department_id,
                'room_id' => $roomId,
                'bed_id' => $bedId,
                'is_discharged' => 0,
                'i_c_u_id' => $icu->id,
                'food_type_id' => json_encode([]),
            ]);

            $icu->update(['hospitalization_id' => $sourceHospitalization->id]);
            $bed->update(['is_occupied' => true]);

            return;
        }

        $appointment = ! empty($validated['appointment_id'])
            ? Appointment::query()->find($validated['appointment_id'])
            : null;

        $placement = Hospitalization::create([
            'reason' => localize('global.refere_to_icu') ?: 'Referral to ICU',
            'remarks' => $validated['description'] ?? (localize('global.refere_to_icu') ?: 'ICU referral'),
            'appointment_id' => $validated['appointment_id'] ?? null,
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $this->resolveDoctorId($validated, $appointment?->id),
            'branch_id' => $validated['branch_id'],
            'department_id' => $appointment?->department_id,
            'room_id' => $roomId,
            'bed_id' => $bedId,
            'is_discharged' => 0,
            'i_c_u_id' => $icu->id,
            'food_type_id' => json_encode([]),
        ]);

        $icu->update(['hospitalization_id' => $placement->id]);
        $bed->update(['is_occupied' => true]);
    }

    private function clearSourceHospitalizationBed(Hospitalization $hospitalization): void
    {
        if ($hospitalization->bed_id) {
            Bed::query()
                ->where('id', $hospitalization->bed_id)
                ->update(['is_occupied' => false]);
        }

        $hospitalization->update([
            'room_id' => null,
            'bed_id' => null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveDoctorId(array $validated, ?int $appointmentId): ?int
    {
        if (! empty($validated['doctor_id']) && Doctor::query()->whereKey($validated['doctor_id'])->exists()) {
            return (int) $validated['doctor_id'];
        }

        if ($appointmentId) {
            $appointmentDoctorId = Appointment::query()->whereKey($appointmentId)->value('doctor_id');
            if ($appointmentDoctorId && Doctor::query()->whereKey($appointmentDoctorId)->exists()) {
                return (int) $appointmentDoctorId;
            }
        }

        $authDoctorId = request()->user()?->doctor?->id;

        return $authDoctorId ? (int) $authDoctorId : null;
    }

    public static function placementHospitalization(ICU $icu): ?Hospitalization
    {
        if ($icu->relationLoaded('placementHospitalization')) {
            return $icu->getRelation('placementHospitalization');
        }

        return Hospitalization::query()
            ->with(['room:id,name', 'bed:id,number'])
            ->where('i_c_u_id', $icu->id)
            ->latest('id')
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public static function formatListItem(ICU $icu): array
    {
        $placement = self::placementHospitalization($icu);

        return [
            'id' => $icu->id,
            'patient_name' => $icu->patient?->name,
            'description' => $icu->description,
            'status' => $icu->status,
            'room_name' => $placement?->room?->name,
            'bed_number' => $placement?->bed?->number,
            'created_at' => $icu->created_at ? verta($icu->created_at)->format('Y/m/d H:i') : null,
            'permissions' => [
                'edit' => request()->user()?->can('edit-icus') ?? false,
                'delete' => request()->user()?->can('delete-icus') ?? false,
            ],
            'urls' => [
                'show' => route('react.icus.show', $icu),
                'edit' => route('react.icus.show', $icu),
            ],
        ];
    }
}
