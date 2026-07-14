<?php

namespace App\Http\Controllers\V1\PatientSections\Concerns;

use App\Models\Patient;
use App\Models\User;

trait AuthorizesPatientAccess
{
    protected function authorizePatientView(Patient $patient): void
    {
        $this->authorize('view', $patient);
    }

    /**
     * @return array<string, mixed>
     */
    protected function patientMeta(Patient $patient): array
    {
        return [
            'patient_id' => $patient->id,
            'branch_id' => $patient->branch_id,
        ];
    }

    protected function belongsToPatientBranch(User $user, Patient $patient): bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return (int) $user->branch_id === (int) $patient->branch_id;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, bool>  $permissions
     * @param  array<string, mixed>  $extra
     */
    protected function sectionIndexResponse(
        array $items,
        Patient $patient,
        array $permissions,
        array $extra = [],
    ): \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => true,
            'data' => array_merge([
                'items' => $items,
                'count' => count($items),
                'meta' => $this->patientMeta($patient),
                'permissions' => $permissions,
            ], $extra),
        ]);
    }
}
