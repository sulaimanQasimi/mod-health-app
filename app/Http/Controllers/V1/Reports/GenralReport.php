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
        $base = $this->appointmentReportQuery($request);

        $departments = (clone $base)
            ->selectRaw('appointments.department_id, departments.name as department_name, COUNT(*) as count')
            ->groupBy('appointments.department_id', 'departments.name')
            ->orderBy('departments.name')
            ->get()
            ->keyBy('department_id');

        $genderByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.gender',
            ['patients.gender'],
        );

        $jobCategoryByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.job_category',
            ['patients.job_category'],
        );

        $jobTypeByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.job_type',
            ['patients.job_type'],
        );

        $patientTypeByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.type',
            ['patients.type'],
        );

        $militeryTypeByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.militery_type_id',
            ['patients.militery_type_id', 'militery_types.name'],
            function (Builder $query) {
                $query->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id');
            },
            'militery_types.name',
        );

        $relationByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.relation_id',
            ['patients.relation_id', 'relations.name'],
            function (Builder $query) {
                $query->leftJoin('relations', 'patients.relation_id', '=', 'relations.id');
            },
            'relations.name',
        );

        $commandedByDepartment = $this->fetchDepartmentBreakdown(
            $base,
            'patients.commanded_by',
            ['patients.commanded_by'],
            null,
            'patients.commanded_by',
            fn ($value) => filled($value),
        );

        $data = $departments
            ->map(function ($department) use (
                $genderByDepartment,
                $jobCategoryByDepartment,
                $jobTypeByDepartment,
                $patientTypeByDepartment,
                $militeryTypeByDepartment,
                $relationByDepartment,
                $commandedByDepartment,
            ) {
                $departmentId = $department->department_id;

                return [
                    'department_id' => $departmentId,
                    'department_name' => $department->department_name,
                    'count' => (int) $department->count,
                    'genders' => $this->mapBreakdownItems($genderByDepartment->get($departmentId)),
                    'job_categories' => $this->mapBreakdownItems($jobCategoryByDepartment->get($departmentId)),
                    'job_types' => $this->mapBreakdownItems($jobTypeByDepartment->get($departmentId)),
                    'patient_types' => $this->mapBreakdownItems($patientTypeByDepartment->get($departmentId)),
                    'militery_types' => $this->mapBreakdownItems($militeryTypeByDepartment->get($departmentId)),
                    'relations' => $this->mapBreakdownItems($relationByDepartment->get($departmentId)),
                    'commanded_by' => $this->mapBreakdownItems($commandedByDepartment->get($departmentId)),
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
     * @param  list<string>  $groupColumns
     * @param  (callable(mixed): bool)|null  $valueFilter
     */
    private function fetchDepartmentBreakdown(
        Builder $base,
        string $valueColumn,
        array $groupColumns,
        ?callable $join = null,
        ?string $labelColumn = null,
        ?callable $valueFilter = null,
    ): Collection {
        $query = clone $base;

        if ($join) {
            $join($query);
        }

        $labelSelect = $labelColumn
            ? ", {$labelColumn} as breakdown_label"
            : ', NULL as breakdown_label';

        $query
            ->selectRaw("appointments.department_id, departments.name as department_name, {$valueColumn} as breakdown_value{$labelSelect}, COUNT(*) as count")
            ->groupBy('appointments.department_id', 'departments.name', ...$groupColumns)
            ->orderBy('departments.name');

        return $query
            ->get()
            ->filter(function ($row) use ($valueFilter) {
                if (! $valueFilter) {
                    return true;
                }

                return $valueFilter($row->breakdown_value);
            })
            ->groupBy('department_id')
            ->map(function (Collection $rows) {
                return $rows->map(function ($row) {
                    return [
                        'key' => $row->breakdown_value,
                        'label' => $row->breakdown_label ?? $row->breakdown_value,
                        'count' => (int) $row->count,
                    ];
                })->values();
            });
    }

    /**
     * @param  Collection<int, array{key: mixed, label: mixed, count: int}>|null  $items
     * @return list<array{key: mixed, label: mixed, count: int}>
     */
    private function mapBreakdownItems(?Collection $items): array
    {
        if (! $items) {
            return [];
        }

        return $items
            ->map(fn ($item) => [
                'key' => $item['key'],
                'label' => $item['label'],
                'count' => $item['count'],
            ])
            ->values()
            ->all();
    }
}
