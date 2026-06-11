<?php

namespace App\Services;

use App\Jobs\SendNewOperationNotification;
use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Hospitalization;
use HanifHefaz\Dcter\Dcter;
use Illuminate\Support\Facades\DB;

class OperationReferralService
{
    public function __construct(
        private readonly AnesthesiaReferralService $anesthesiaReferralService,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public function createDirect(array $validated): Anesthesia
    {
        return DB::transaction(function () use ($validated) {
            $data = collect($validated)
                ->except(['room_id', 'bed_id'])
                ->all();

            $data['operation_assistants_id'] = json_encode($data['operation_assistants_id'] ?? []);
            $data['date'] = Dcter::JalaliToGregorian(Dcter::Carbonize($data['date']));
            $data['status'] = 'approved';
            $data['is_referred_to_operation'] = true;

            $anesthesia = Anesthesia::create($data);

            $this->anesthesiaReferralService->clearBedForReferral($anesthesia);

            SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);

            return $anesthesia->fresh(['operationType:id,name', 'patient:id,name']);
        });
    }

    public function refer(Anesthesia $anesthesia): void
    {
        abort_unless($anesthesia->status === 'approved', 422);
        abort_if($anesthesia->is_referred_to_operation, 422);

        DB::transaction(function () use ($anesthesia) {
            $this->anesthesiaReferralService->clearBedForReferral($anesthesia);
            $anesthesia->update(['is_referred_to_operation' => true]);
        });

        SendNewOperationNotification::dispatch($anesthesia->created_by, $anesthesia->id);
    }

    public function ensureAnesthesiaBelongsToHospitalization(Anesthesia $anesthesia, Hospitalization $hospitalization): void
    {
        if ($hospitalization->appointment_id) {
            abort_unless((int) $anesthesia->appointment_id === (int) $hospitalization->appointment_id, 404);

            return;
        }

        abort_unless((int) $anesthesia->hospitalization_id === (int) $hospitalization->id, 404);
    }

    public function ensureAnesthesiaBelongsToAppointment(Anesthesia $anesthesia, Appointment $appointment): void
    {
        abort_unless((int) $anesthesia->appointment_id === (int) $appointment->id, 404);
    }
}
