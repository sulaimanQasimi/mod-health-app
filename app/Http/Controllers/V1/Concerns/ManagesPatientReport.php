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
     * @return array{total: int, male: int, female: int, military: int, civilian: int}
     */
    protected function patientReportSummary(Builder $query): array
    {
        $total = (clone $query)->count();

        return [
            'total' => $total,
            'male' => (clone $query)->where('patients.gender', '0')->count(),
            'female' => (clone $query)->where('patients.gender', '1')->count(),
            'military' => (clone $query)->where('patients.job_category', '0')->count(),
            'civilian' => (clone $query)->where('patients.job_category', '1')->count(),
        ];
    }

    /**
     * @return array{
     *     by_gender: list<array{name: string, count: int}>,
     *     by_type: list<array{name: string, count: int}>,
     *     by_date: list<array{date: string, count: int}>
     * }
     */
    protected function patientReportAnalytics(Builder $query): array
    {
        $byGender = $this->patientReportAggregateQuery($query, 'patients.gender as value, COUNT(*) as aggregate_count')
            ->groupBy('patients.gender')
            ->get()
            ->map(fn ($row) => [
                'name' => (string) $row->value === '1' ? 'female' : ((string) $row->value === '0' ? 'male' : 'unknown'),
                'count' => (int) $row->aggregate_count,
            ])->values()->all();

        $byType = $this->patientReportAggregateQuery($query, 'patients.type as value, COUNT(*) as aggregate_count')
            ->groupBy('patients.type')
            ->get()
            ->map(fn ($row) => [
                'name' => match ((string) $row->value) {
                    '0' => 'mod',
                    '1' => 'recipient',
                    '2' => 'family',
                    default => 'unknown',
                },
                'count' => (int) $row->aggregate_count,
            ])->values()->all();

        $byDate = $this->patientReportAggregateQuery(
            $query,
            'DATE(patients.registration_date) as day, COUNT(*) as aggregate_count'
        )
            ->whereNotNull('patients.registration_date')
            ->groupBy('day')
            ->orderBy('day')
            ->get()
            ->map(function ($row) {
                try {
                    $date = $row->day ? verta($row->day)->format('Y/m/d') : '—';
                } catch (\Throwable) {
                    $date = (string) $row->day;
                }

                return ['date' => $date, 'count' => (int) $row->aggregate_count];
            })->values()->all();

        return [
            'by_gender' => $byGender,
            'by_type' => $byType,
            'by_date' => $byDate,
        ];
    }

    /**
     * Clone the report query and replace its SELECT list for aggregate-only analytics.
     *
     * @return Builder<Patient>
     */
    protected function patientReportAggregateQuery(Builder $query, string $selectRaw): Builder
    {
        $aggregate = (clone $query)->reorder();
        $aggregate->getQuery()->columns = null;

        return $aggregate->selectRaw($selectRaw);
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
                'show' => route('patients.show', $patient),
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
