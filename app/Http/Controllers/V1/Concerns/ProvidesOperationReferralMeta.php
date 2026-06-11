<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Doctor;
use App\Models\OperationType;

trait ProvidesOperationReferralMeta
{
    /**
     * @return list<array{id: int, name: string}>
     */
    protected function operationTypes(?int $branchId): array
    {
        return OperationType::query()
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (OperationType $type) => ['id' => $type->id, 'name' => $type->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    protected function hospitalDoctors(?int $branchId): array
    {
        return Doctor::query()
            ->where('clinic_type', 'hospital')
            ->where('active_status', true)
            ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Doctor $doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
            ->values()
            ->all();
    }
}
