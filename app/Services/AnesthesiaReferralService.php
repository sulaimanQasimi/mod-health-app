<?php

namespace App\Services;

use App\Jobs\SendNewAnesthesiaNotification;
use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\Doctor;
use App\Models\Hospitalization;
use App\Models\User;
use HanifHefaz\Dcter\Dcter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnesthesiaReferralService
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public function create(array $validated, User $user): Anesthesia
    {
        return DB::transaction(function () use ($validated) {
            $data = collect($validated)
                ->except(['room_id', 'bed_id'])
                ->all();

            $data['operation_assistants_id'] = json_encode($data['operation_assistants_id'] ?? []);
            $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));

            $anesthesia = Anesthesia::create($data);

            $hospitalization = $this->resolveSourceHospitalization($validated);
            if ($hospitalization) {
                $this->clearHospitalizationBed($hospitalization);
            }

            SendNewAnesthesiaNotification::dispatch($anesthesia->created_by, $anesthesia->id);

            return $anesthesia->fresh(['operationType:id,name', 'patient:id,name']);
        });
    }

    public function normalizeReferralInput(Request $request): void
    {
        $request->merge([
            'operation_surgion_id' => $request->filled('operation_surgion_id')
                ? $request->input('operation_surgion_id')
                : null,
            'anesthesia_type' => $request->filled('anesthesia_type')
                ? $request->input('anesthesia_type')
                : null,
        ]);
    }

    /**
     * Rules for fields submitted by the referral form (React modal / API).
     *
     * @return array<string, string>
     */
    public function formValidationRules(): array
    {
        return [
            'operation_type_id' => 'required|exists:operation_types,id',
            'date' => 'required',
            'time' => 'required',
            'plan' => 'required|string',
            'position_on_bed' => 'required|string',
            'planned_duration' => 'required|string',
            'estimated_blood_waste' => 'required|string',
            'other_problems' => 'required|string',
            'anesthesia_type' => 'nullable|in:local,spinal,general',
            'operation_assistants_id' => 'nullable|array',
            'operation_surgion_id' => 'nullable|exists:doctors,id',
        ];
    }

    /**
     * Full rules including context ids (legacy Blade forms with hidden inputs).
     *
     * @return array<string, string>
     */
    public function validationRules(): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required',
            'branch_id' => 'required|exists:branches,id',
            'appointment_id' => 'required|exists:appointments,id',
            'hospitalization_id' => 'nullable|exists:hospitalizations,id',
            ...$this->formValidationRules(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    private function resolveSourceHospitalization(array $validated): ?Hospitalization
    {
        if (! empty($validated['hospitalization_id'])) {
            return Hospitalization::query()->find($validated['hospitalization_id']);
        }

        if (empty($validated['appointment_id'])) {
            return null;
        }

        return Hospitalization::query()
            ->where('appointment_id', $validated['appointment_id'])
            ->where(function ($query) {
                $query->where('is_discharged', 0)->orWhereNull('is_discharged');
            })
            ->latest('id')
            ->first();
    }

    private function clearHospitalizationBed(Hospitalization $hospitalization): void
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

    public static function resolveDoctorId(?int $appointmentDoctorId, ?int $requestedDoctorId, ?User $user): int
    {
        if ($requestedDoctorId && Doctor::query()->whereKey($requestedDoctorId)->exists()) {
            return (int) $requestedDoctorId;
        }

        if ($appointmentDoctorId && Doctor::query()->whereKey($appointmentDoctorId)->exists()) {
            return (int) $appointmentDoctorId;
        }

        return (int) ($user?->doctor?->id ?? $user?->id ?? 0);
    }
}
