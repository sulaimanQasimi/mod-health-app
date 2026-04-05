<?php

namespace App\Blood;

use App\Models\BloodBank;
use App\Models\BloodUnit;
use App\Services\BloodCrossmatchService;

/**
 * Value object for a patient's blood need / request context (ABO, Rh, component, quantity)
 * plus related IDs (appointment, patient, branch, clinical links). Use {@see BloodBank::bloodCheck()}
 * or {@see self::fromAttributes()} so the same checks can run from the blood bank UI, appointments, or APIs.
 */
final class BloodCheck
{
    public function __construct(
        public readonly ?int $bloodRequestId,
        public readonly ?int $branchId,
        public readonly ?int $appointmentId,
        public readonly ?int $patientId,
        public readonly ?int $departmentId,
        public readonly ?int $operationId,
        public readonly ?int $hospitalizationId,
        public readonly ?int $anesthesiaId,
        public readonly ?int $icuId,
        public readonly ?int $underReviewId,
        public readonly string $aboGroup,
        public readonly string $rh,
        public readonly string $componentType,
        public readonly int $quantity,
        public readonly string $status,
        public readonly ?string $rejectReason,
        public readonly ?string $patientName,
    ) {}

    public static function fromBloodBank(BloodBank $bloodBank): self
    {
        return new self(
            bloodRequestId: (int) $bloodBank->id,
            branchId: $bloodBank->branch_id !== null ? (int) $bloodBank->branch_id : null,
            appointmentId: $bloodBank->appointment_id !== null ? (int) $bloodBank->appointment_id : null,
            patientId: $bloodBank->patient_id !== null ? (int) $bloodBank->patient_id : null,
            departmentId: $bloodBank->department_id !== null ? (int) $bloodBank->department_id : null,
            operationId: $bloodBank->operation_id !== null ? (int) $bloodBank->operation_id : null,
            hospitalizationId: $bloodBank->hospitalization_id !== null ? (int) $bloodBank->hospitalization_id : null,
            anesthesiaId: $bloodBank->anesthesia_id !== null ? (int) $bloodBank->anesthesia_id : null,
            icuId: $bloodBank->i_c_u_id !== null ? (int) $bloodBank->i_c_u_id : null,
            underReviewId: $bloodBank->under_review_id !== null ? (int) $bloodBank->under_review_id : null,
            aboGroup: (string) $bloodBank->group,
            rh: (string) $bloodBank->rh,
            componentType: (string) $bloodBank->type,
            quantity: max(0, (int) $bloodBank->quantity),
            status: (string) $bloodBank->status,
            rejectReason: $bloodBank->reject_reason,
            patientName: $bloodBank->patient?->name,
        );
    }

    /**
     * Build from raw attributes (e.g. appointment form, API) when no {@see BloodBank} row exists yet.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromAttributes(array $attributes): self
    {
        return new self(
            bloodRequestId: isset($attributes['blood_request_id']) ? (int) $attributes['blood_request_id'] : null,
            branchId: isset($attributes['branch_id']) ? (int) $attributes['branch_id'] : null,
            appointmentId: isset($attributes['appointment_id']) ? (int) $attributes['appointment_id'] : null,
            patientId: isset($attributes['patient_id']) ? (int) $attributes['patient_id'] : null,
            departmentId: isset($attributes['department_id']) ? (int) $attributes['department_id'] : null,
            operationId: isset($attributes['operation_id']) ? (int) $attributes['operation_id'] : null,
            hospitalizationId: isset($attributes['hospitalization_id']) ? (int) $attributes['hospitalization_id'] : null,
            anesthesiaId: isset($attributes['anesthesia_id']) ? (int) $attributes['anesthesia_id'] : null,
            icuId: isset($attributes['i_c_u_id']) ? (int) $attributes['i_c_u_id'] : null,
            underReviewId: isset($attributes['under_review_id']) ? (int) $attributes['under_review_id'] : null,
            aboGroup: (string) ($attributes['group'] ?? $attributes['abo_group'] ?? ''),
            rh: (string) ($attributes['rh'] ?? '+'),
            componentType: (string) ($attributes['type'] ?? $attributes['component_type'] ?? 'Fresh'),
            quantity: max(0, (int) ($attributes['quantity'] ?? 0)),
            status: (string) ($attributes['status'] ?? 'new'),
            rejectReason: isset($attributes['reject_reason']) ? (string) $attributes['reject_reason'] : null,
            patientName: isset($attributes['patient_name']) ? (string) $attributes['patient_name'] : null,
        );
    }

    public function aboRhLabel(): string
    {
        return $this->aboGroup.$this->rh;
    }

    /**
     * @return array<string, int>
     */
    public function contextIds(): array
    {
        return array_filter([
            'blood_request_id' => $this->bloodRequestId,
            'branch_id' => $this->branchId,
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'department_id' => $this->departmentId,
            'operation_id' => $this->operationId,
            'hospitalization_id' => $this->hospitalizationId,
            'anesthesia_id' => $this->anesthesiaId,
            'i_c_u_id' => $this->icuId,
            'under_review_id' => $this->underReviewId,
        ], fn (?int $id) => $id !== null);
    }

    /**
     * Linked record IDs for UI chips (excludes blood_request_id; optionally drops appointment_id when shown elsewhere).
     *
     * @return array<string, int>
     */
    public function linkedContextIds(bool $excludeAppointmentId = false): array
    {
        $ids = array_filter([
            'branch_id' => $this->branchId,
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'department_id' => $this->departmentId,
            'operation_id' => $this->operationId,
            'hospitalization_id' => $this->hospitalizationId,
            'anesthesia_id' => $this->anesthesiaId,
            'i_c_u_id' => $this->icuId,
            'under_review_id' => $this->underReviewId,
        ], fn (?int $id) => $id !== null);

        if ($excludeAppointmentId) {
            unset($ids['appointment_id']);
        }

        return $ids;
    }

    public function isAboCompatibleWithBloodUnit(BloodUnit $unit): bool
    {
        return app(BloodCrossmatchService::class)->isAboCompatible($this->aboGroup, $unit->blood_group);
    }

    public function isRhCompatibleWithBloodUnit(BloodUnit $unit): bool
    {
        return app(BloodCrossmatchService::class)->isRhCompatible($this->rh, $unit->rh);
    }

    public function isComponentCompatibleWithBloodUnit(BloodUnit $unit): bool
    {
        return $this->componentType === $unit->component_type;
    }

    /**
     * Same rules as the crossmatch "auto check" column (ABO + Rh; component is validated separately in lab flow).
     */
    public function isAboRhAutoCompatibleWithBloodUnit(BloodUnit $unit): bool
    {
        return $this->isAboCompatibleWithBloodUnit($unit) && $this->isRhCompatibleWithBloodUnit($unit);
    }

    /**
     * Full inventory match: ABO, Rh, and component type.
     */
    public function isFullyCompatibleWithBloodUnit(BloodUnit $unit): bool
    {
        return $this->isAboRhAutoCompatibleWithBloodUnit($unit)
            && $this->isComponentCompatibleWithBloodUnit($unit);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'blood_request_id' => $this->bloodRequestId,
            'branch_id' => $this->branchId,
            'appointment_id' => $this->appointmentId,
            'patient_id' => $this->patientId,
            'department_id' => $this->departmentId,
            'operation_id' => $this->operationId,
            'hospitalization_id' => $this->hospitalizationId,
            'anesthesia_id' => $this->anesthesiaId,
            'i_c_u_id' => $this->icuId,
            'under_review_id' => $this->underReviewId,
            'group' => $this->aboGroup,
            'rh' => $this->rh,
            'abo_rh_label' => $this->aboRhLabel(),
            'type' => $this->componentType,
            'quantity' => $this->quantity,
            'status' => $this->status,
            'reject_reason' => $this->rejectReason,
            'patient_name' => $this->patientName,
        ];
    }
}
