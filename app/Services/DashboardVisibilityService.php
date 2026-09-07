<?php

namespace App\Services;

use App\Models\User;

class DashboardVisibilityService
{
    /**
     * @return array<string, bool>
     */
    public function forUser(User $user): array
    {
        $isAdmin = $user->hasRole(['admin', 'super_admin']);

        $information = $isAdmin || $user->can('show-information-menu');
        $myVisits = $isAdmin || $user->can('show-my-visits-menu');
        $consultations = $isAdmin || $user->can('show-my-consultations-menu');
        $hospitalizations = $isAdmin || $user->can('show-hospitalizations-menu');
        $labs = $isAdmin || $user->can('show-labs-menu');
        $icu = $isAdmin || $user->can('show-icu-menu');
        $prescriptions = $isAdmin
            || $user->can('show-prescriptions-menu')
            || $user->hasActivePharmacyRole(['manager', 'staff']);
        $operations = $isAdmin || $user->can('show-operations-menu');
        $physiotherapy = $isAdmin || $user->can('show-physiotherapy-menu');
        $reports = $isAdmin || $user->can('show-reports-menu');

        return [
            'today_patients' => $information,
            'emergency_today_patients' => $information,
            'all_patients' => $information,
            'all_appointments' => $information || $myVisits,
            'consultations' => $consultations,
            'hospitalizations' => $hospitalizations,
            'checkups' => $labs,
            'icu' => $icu,
            'ccu' => $hospitalizations,
            'prescriptions' => $prescriptions,
            'operations' => $operations,
            'physiotherapy' => $physiotherapy,
            'beds' => $hospitalizations,
            'patients_trend' => $information,
            'appointments_trend' => $information || $myVisits,
            'appointments_by_user' => $information,
            'doctors_activity' => $reports,
            'nurses_activity' => $hospitalizations || $reports,
        ];
    }

    /**
     * @param  array<string, bool>  $visible
     */
    public function needsAny(array $visible, string ...$keys): bool
    {
        foreach ($keys as $key) {
            if (! empty($visible[$key])) {
                return true;
            }
        }

        return false;
    }
}
