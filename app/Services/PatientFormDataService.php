<?php

namespace App\Services;

use App\Models\Department;
use App\Models\MiliteryType;
use App\Models\Province;
use App\Models\Recipient;
use App\Models\Relation;
use App\Models\User;

class PatientFormDataService
{
    public function createFormData(?User $user = null): array
    {
        $user ??= auth()->user();

        $departments = $user->category_id
            ? Department::query()->where('category_id', $user->category_id)->orderBy('name')->get(['id', 'name'])
            : Department::query()->orderBy('name')->get(['id', 'name']);

        return [
            'branchId' => $user->branch_id,
            'clinicType' => $user->clinic_type,
            'registrationDate' => verta()->format('Y-m-d'),
            'provinces' => Province::query()->orderBy('name_dr')->get(['id', 'name_dr']),
            'recipients' => Recipient::query()->orderBy('name')->get(['id', 'name']),
            'relations' => Relation::query()->orderBy('name')->get(['id', 'name']),
            'militeryTypes' => MiliteryType::query()->orderBy('name')->get(['id', 'name']),
            'departments' => $departments,
        ];
    }
}
