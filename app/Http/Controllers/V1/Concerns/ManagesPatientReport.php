<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesPatientReport
{
    private const PATIENT_REPORT_FILTER_KEYS = [
        'patient_name',
        'nid',
        'id_card',
        'referral_name',
        'age',
        'gender',
        'job_category',
        'type',
        'referred_by',
        'province_id',
        'district_id',
        'from',
        'to',
        'per_page',
    ];

    protected function patientReportHasSearch(Request $request): bool
    {
        if ($request->boolean('search')) {
            return true;
        }

        foreach (self::PATIENT_REPORT_FILTER_KEYS as $key) {
            if ($key === 'per_page') {
                continue;
            }

            if ($request->filled($key)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Builder<Patient>
     */
    protected function patientReportBaseQuery(Request $request, int $branchId): Builder
    {
        $query = Patient::query()
            ->where('branch_id', $branchId)
            ->with([
                'province:id,name_dr',
                'district:id,name_dr',
                'recipient:id,name',
            ])
            ->select([
                'patients.id',
                'patients.name',
                'patients.nid',
                'patients.id_card',
                'patients.referral_name',
                'patients.age',
                'patients.gender',
                'patients.job_category',
                'patients.type',
                'patients.referred_by',
                'patients.province_id',
                'patients.district_id',
                'patients.registration_date',
            ]);

        $this->applyPatientReportFilters($query, $request);

        return $query->orderByDesc('patients.registration_date');
    }

    /**
     * @param  Builder<Patient>  $query
     */
    protected function applyPatientReportFilters(Builder $query, Request $request): void
    {
        if ($request->filled('patient_name')) {
            $query->where('patients.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('nid')) {
            $query->where('patients.nid', 'like', '%'.$request->nid.'%');
        }

        if ($request->filled('id_card')) {
            $query->where('patients.id_card', $request->id_card);
        }

        if ($request->filled('referral_name')) {
            $query->where('patients.referral_name', 'like', '%'.$request->referral_name.'%');
        }

        if ($request->filled('job_category')) {
            $query->where('patients.job_category', $request->job_category);
        }

        if ($request->filled('type')) {
            $query->where('patients.type', $request->type);
        }

        if ($request->filled('referred_by')) {
            $query->where('patients.referred_by', $request->referred_by);
        }

        if ($request->filled('age')) {
            $query->where('patients.age', $request->age);
        }

        if ($request->filled('gender')) {
            $query->where('patients.gender', $request->gender);
        }

        if ($request->filled('province_id')) {
            $query->where('patients.province_id', $request->province_id);
        }

        if ($request->filled('district_id')) {
            $query->where('patients.district_id', $request->district_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            try {
                $fromDate = verta()->parse($request->from)->datetime();
                $toDate = verta()->parse($request->to)->datetime();

                $query->whereDate('patients.registration_date', '>=', $fromDate)
                    ->whereDate('patients.registration_date', '<=', $toDate)
                    ->whereHas('appointments', function ($appointmentQuery) use ($fromDate, $toDate) {
                        $appointmentQuery->whereDate('date', '>=', $fromDate)
                            ->whereDate('date', '<=', $toDate);
                    });
            } catch (\Throwable) {
                // Skip invalid jalali range.
            }
        }
    }

    /**
     * @return array{total: int}
     */
    protected function patientReportSummary(Builder $query): array
    {
        return [
            'total' => (clone $query)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformPatientReportItem(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'patient_name' => $patient->name,
            'nid' => $patient->nid,
            'id_card' => $patient->id_card,
            'referral_name' => $patient->referral_name,
            'age' => $patient->age,
            'gender' => $patient->gender,
            'job_category' => $patient->job_category,
            'type' => $patient->type,
            'referred_by_name' => $patient->recipient?->name,
            'province_name' => $patient->province?->name_dr,
            'district_name' => $patient->district?->name_dr,
            'registration_date' => $patient->registration_date
                ? verta($patient->registration_date)->format('Y/m/d')
                : null,
            'urls' => [
                'show' => route('react.patients.show', $patient),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function patientReportFilterKeys(): array
    {
        return self::PATIENT_REPORT_FILTER_KEYS;
    }
}
