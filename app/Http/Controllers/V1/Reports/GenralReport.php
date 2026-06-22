<?php

namespace App\Http\Controllers\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Branch;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class GenralReport extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $branches = $user?->branch_id
            ? Branch::query()->where('id', $user->branch_id)->orderBy('name')->get(['id', 'name'])
            : Branch::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Reports/GeneralReport', [
            'filters' => [
                'branch_id' => $request->string('branch_id')->toString(),
                'date_from' => $request->string('date_from')->toString(),
                'date_to' => $request->string('date_to')->toString(),
            ],
            'hasSearch' => $request->boolean('search'),
            'filterOptions' => [
                'branches' => $branches,
            ],
            'urls' => [
                'current' => route('react.reports.general.index'),
            ],
        ]);
    }

    public function number_of_patients_base_on_department(Request $request)
    {
        $rows = $this->appointmentReportQuery($request)
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->selectRaw('
                appointments.department_id,
                departments.name as department_name,
                patients.gender,
                patients.job_category,
                patients.job_type,
                patients.type as patient_type,
                patients.militery_type_id,
                militery_types.name as militery_type_name,
                patients.relation_id,
                relations.name as relation_name,
                patients.commanded_by,
                COUNT(*) as count
            ')
            ->groupBy(
                'appointments.department_id',
                'departments.name',
                'patients.gender',
                'patients.job_category',
                'patients.job_type',
                'patients.type',
                'patients.militery_type_id',
                'militery_types.name',
                'patients.relation_id',
                'relations.name',
                'patients.commanded_by',
            )
            ->orderBy('departments.name')
            ->get();

        $data = $rows
            ->groupBy('department_id')
            ->map(function (Collection $departmentRows) {
                $first = $departmentRows->first();

                return [
                    'department_id' => $first->department_id,
                    'department_name' => $first->department_name,
                    'count' => (int) $departmentRows->sum('count'),
                    'genders' => $this->aggregateBreakdown($departmentRows, 'gender'),
                    'job_categories' => $this->aggregateBreakdown($departmentRows, 'job_category'),
                    'job_types' => $this->aggregateBreakdown($departmentRows, 'job_type'),
                    'patient_types' => $this->aggregateBreakdown($departmentRows, 'patient_type'),
                    'militery_types' => $this->aggregateBreakdown($departmentRows, 'militery_type_id', 'militery_type_name'),
                    'relations' => $this->aggregateBreakdown($departmentRows, 'relation_id', 'relation_name'),
                    'commanded_by' => $this->aggregateBreakdown(
                        $departmentRows,
                        'commanded_by',
                        'commanded_by',
                        fn ($value) => filled($value),
                    ),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'General report fetched successfully',
            'data' => $data,
        ]);
    }

    public function number_of_patients_base_on_patient_militery_types(Request $request)
    {
        $rows = Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('appointments.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('appointments.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('appointments.branch_id', $request->branch_id))
            ->selectRaw('appointments.department_id, departments.name as department_name, patients.militery_type_id, militery_types.name as militery_type_name, COUNT(*) as count')
            ->groupBy('appointments.department_id', 'departments.name', 'patients.militery_type_id', 'militery_types.name')
            ->orderBy('departments.name')
            ->orderBy('militery_types.name')
            ->get();

        $data = $rows
            ->groupBy('department_id')
            ->map(function ($departmentRows) {
                $first = $departmentRows->first();

                return [
                    'department_id' => $first->department_id,
                    'department_name' => $first->department_name,
                    'count' => (int) $departmentRows->sum('count'),
                    'militery_types' => $departmentRows->map(fn ($row) => [
                        'militery_type_id' => $row->militery_type_id,
                        'militery_type_name' => $row->militery_type_name,
                        'count' => (int) $row->count,
                    ])->values(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'General report fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * @return Builder<Appointment>
     */
    private function appointmentReportQuery(Request $request): Builder
    {
        return Appointment::query()
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'appointments.patient_id', '=', 'patients.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('appointments.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('appointments.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('appointments.branch_id', $request->branch_id));
    }

    /**
     * @param  (callable(mixed): bool)|null  $valueFilter
     * @return list<array{key: mixed, label: string|null, count: int}>
     */
    private function aggregateBreakdown(
        Collection $rows,
        string $keyColumn,
        ?string $labelColumn = null,
        ?callable $valueFilter = null,
    ): array {
        $totals = [];

        foreach ($rows as $row) {
            $key = $row->{$keyColumn};

            if ($valueFilter && ! $valueFilter($key)) {
                continue;
            }

            $bucketKey = $key === null ? '__null__' : (string) $key;

            if (! isset($totals[$bucketKey])) {
                $label = null;

                if ($labelColumn && filled($row->{$labelColumn})) {
                    $label = (string) $row->{$labelColumn};
                }

                $totals[$bucketKey] = [
                    'key' => $key,
                    'label' => $label,
                    'count' => 0,
                ];
            }

            $totals[$bucketKey]['count'] += (int) $row->count;
        }

        return array_values($totals);
    }
}
