<?php

namespace App\Policies;

use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin'])
            || $user->can('view-prescriptions')
            || $user->can('show-prescriptions-menu')
            || $user->hasActivePharmacyRole(['manager', 'staff']);
    }

    public function view(User $user, Prescription $prescription): bool
    {
        return $this->viewAny($user) && $this->canAccess($user, $prescription);
    }

    public function update(User $user, Prescription $prescription): bool
    {
        return ($user->hasRole(['super_admin', 'admin']) || $user->can('edit-prescriptions'))
            && $this->canAccess($user, $prescription);
    }

    public function delete(User $user, Prescription $prescription): bool
    {
        return ($user->hasRole(['super_admin', 'admin']) || $user->can('delete-prescriptions'))
            && $this->canAccess($user, $prescription);
    }

    public function export(User $user): bool
    {
        return $user->hasRole(['super_admin', 'admin']) || $user->can('export-prescriptions');
    }

    public function manageItems(User $user, Prescription $prescription): bool
    {
        return $this->update($user, $prescription) && ! $prescription->is_completed;
    }

    private function canAccess(User $user, Prescription $prescription): bool
    {
        if ($prescription->branch_id !== $user->branch_id) {
            return false;
        }

        return Prescription::query()
            ->whereKey($prescription->id)
            ->visibleToClinicType($user->clinic_type)
            ->exists();
    }
}
