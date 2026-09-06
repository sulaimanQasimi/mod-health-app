<?php

namespace App\Http\Controllers\V1\Reports;

use App\Http\Controllers\Controller;
use App\Models\Anesthesia;
use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\PatientTestRegistration;
use App\Models\PrescriptionItem;
use App\Models\UnderReview;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
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
                'current' => route('reports.general.index'),
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
                appointments.clinic_type,
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
                'appointments.clinic_type',
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
                    'clinic_types' => $this->aggregateBreakdown($departmentRows, 'clinic_type'),
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

    public function hospitalization(Request $request)
    {
        $rows = $this->hospitalizationReportQuery($request)
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->leftJoin('appointments', 'hospitalizations.appointment_id', '=', 'appointments.id')
            ->selectRaw('
                hospitalizations.department_id,
                departments.name as department_name,
                patients.gender,
                patients.job_category,
                patients.job_type,
                patients.type as patient_type,
                appointments.clinic_type,
                patients.militery_type_id,
                militery_types.name as militery_type_name,
                patients.relation_id,
                relations.name as relation_name,
                patients.commanded_by,
                COUNT(*) as count
            ')
            ->groupBy(
                'hospitalizations.department_id',
                'departments.name',
                'patients.gender',
                'patients.job_category',
                'patients.job_type',
                'patients.type',
                'appointments.clinic_type',
                'patients.militery_type_id',
                'militery_types.name',
                'patients.relation_id',
                'relations.name',
                'patients.commanded_by',
            )
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Hospitalization report fetched successfully',
            'data' => $this->mapDepartmentDemographicRows($rows),
        ]);
    }

    public function operations(Request $request): JsonResponse
    {
        $rows = $this->anesthesiaDemographicQuery($request)
            ->where('anesthesias.is_referred_to_operation', true)
            ->selectRaw($this->departmentDemographicSelect('appointments.department_id'))
            ->groupBy(...$this->departmentDemographicGroupBy('appointments.department_id'))
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Operations report fetched successfully',
            'data' => $this->mapDepartmentDemographicRows($rows),
        ]);
    }

    public function anesthesias(Request $request): JsonResponse
    {
        $rows = $this->anesthesiaDemographicQuery($request)
            ->selectRaw($this->departmentDemographicSelect('appointments.department_id'))
            ->groupBy(...$this->departmentDemographicGroupBy('appointments.department_id'))
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Anesthesias report fetched successfully',
            'data' => $this->mapDepartmentDemographicRows($rows),
        ]);
    }

    public function icus(Request $request): JsonResponse
    {
        $icuTable = (new ICU)->getTable();

        $rows = ICU::query()
            ->leftJoin('appointments', "{$icuTable}.appointment_id", '=', 'appointments.id')
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('patients', "{$icuTable}.patient_id", '=', 'patients.id')
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where("{$icuTable}.created_at", '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where("{$icuTable}.created_at", '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where("{$icuTable}.branch_id", $request->branch_id))
            ->selectRaw($this->departmentDemographicSelect('appointments.department_id'))
            ->groupBy(...$this->departmentDemographicGroupBy('appointments.department_id'))
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'ICU report fetched successfully',
            'data' => $this->mapDepartmentDemographicRows($rows),
        ]);
    }

    public function under_reviews(Request $request): JsonResponse
    {
        $rows = UnderReview::query()
            ->leftJoin('departments', 'under_reviews.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'under_reviews.patient_id', '=', 'patients.id')
            ->leftJoin('appointments', 'under_reviews.appointment_id', '=', 'appointments.id')
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('under_reviews.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('under_reviews.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('under_reviews.branch_id', $request->branch_id))
            ->selectRaw($this->departmentDemographicSelect('under_reviews.department_id'))
            ->groupBy(...$this->departmentDemographicGroupBy('under_reviews.department_id'))
            ->orderBy('departments.name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Under review report fetched successfully',
            'data' => $this->mapDepartmentDemographicRows($rows),
        ]);
    }

    public function patient_test_registrations(Request $request)
    {
        $rows = $this->patientTestRegistrationReportQuery($request)
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->selectRaw('
                patient_test_registrations.lab_type_id,
                lab_types.name as test_name,
                patients.gender,
                patients.job_category,
                patients.job_type,
                patients.type as patient_type,
                appointments.clinic_type,
                patients.militery_type_id,
                militery_types.name as militery_type_name,
                patients.relation_id,
                relations.name as relation_name,
                patients.commanded_by,
                COUNT(*) as count
            ')
            ->groupBy(
                'patient_test_registrations.lab_type_id',
                'lab_types.name',
                'patients.gender',
                'patients.job_category',
                'patients.job_type',
                'patients.type',
                'appointments.clinic_type',
                'patients.militery_type_id',
                'militery_types.name',
                'patients.relation_id',
                'relations.name',
                'patients.commanded_by',
            )
            ->orderBy('lab_types.name')
            ->get();

        $data = $rows
            ->groupBy('lab_type_id')
            ->map(function (Collection $testRows) {
                $first = $testRows->first();

                return [
                    'lab_type_id' => $first->lab_type_id,
                    'test_name' => $first->test_name,
                    'count' => (int) $testRows->sum('count'),
                    'genders' => $this->aggregateBreakdown($testRows, 'gender'),
                    'job_categories' => $this->aggregateBreakdown($testRows, 'job_category'),
                    'job_types' => $this->aggregateBreakdown($testRows, 'job_type'),
                    'patient_types' => $this->aggregateBreakdown($testRows, 'patient_type'),
                    'clinic_types' => $this->aggregateBreakdown($testRows, 'clinic_type'),
                    'militery_types' => $this->aggregateBreakdown($testRows, 'militery_type_id', 'militery_type_name'),
                    'relations' => $this->aggregateBreakdown($testRows, 'relation_id', 'relation_name'),
                    'commanded_by' => $this->aggregateBreakdown(
                        $testRows,
                        'commanded_by',
                        'commanded_by',
                        fn ($value) => filled($value),
                    ),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Patient test registration report fetched successfully',
            'data' => $data,
            'departments' => $this->departmentsForReportFilter($request),
        ]);
    }

    public function prescriptions(Request $request)
    {
        $rows = $this->prescriptionReportQuery($request)
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->selectRaw('
                prescription_items.medicine_id,
                medicines.name as medicine_name,
                patients.gender,
                patients.job_category,
                patients.job_type,
                patients.type as patient_type,
                appointments.clinic_type,
                patients.militery_type_id,
                militery_types.name as militery_type_name,
                patients.relation_id,
                relations.name as relation_name,
                patients.commanded_by,
                COUNT(*) as count
            ')
            ->groupBy(
                'prescription_items.medicine_id',
                'medicines.name',
                'patients.gender',
                'patients.job_category',
                'patients.job_type',
                'patients.type',
                'appointments.clinic_type',
                'patients.militery_type_id',
                'militery_types.name',
                'patients.relation_id',
                'relations.name',
                'patients.commanded_by',
            )
            ->orderBy('medicines.name')
            ->get();

        $data = $rows
            ->groupBy('medicine_id')
            ->map(function (Collection $medicineRows) {
                $first = $medicineRows->first();

                return [
                    'medicine_id' => $first->medicine_id,
                    'medicine_name' => $first->medicine_name,
                    'count' => (int) $medicineRows->sum('count'),
                    'genders' => $this->aggregateBreakdown($medicineRows, 'gender'),
                    'job_categories' => $this->aggregateBreakdown($medicineRows, 'job_category'),
                    'job_types' => $this->aggregateBreakdown($medicineRows, 'job_type'),
                    'patient_types' => $this->aggregateBreakdown($medicineRows, 'patient_type'),
                    'clinic_types' => $this->aggregateBreakdown($medicineRows, 'clinic_type'),
                    'militery_types' => $this->aggregateBreakdown($medicineRows, 'militery_type_id', 'militery_type_name'),
                    'relations' => $this->aggregateBreakdown($medicineRows, 'relation_id', 'relation_name'),
                    'commanded_by' => $this->aggregateBreakdown(
                        $medicineRows,
                        'commanded_by',
                        'commanded_by',
                        fn ($value) => filled($value),
                    ),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Prescription report fetched successfully',
            'data' => $data,
        ]);
    }

    /**
     * @return Builder<PrescriptionItem>
     */
    private function prescriptionReportQuery(Request $request): Builder
    {
        return PrescriptionItem::query()
            ->join('prescriptions', 'prescription_items.prescription_id', '=', 'prescriptions.id')
            ->leftJoin('medicines', 'prescription_items.medicine_id', '=', 'medicines.id')
            ->leftJoin('patients', 'prescriptions.patient_id', '=', 'patients.id')
            ->leftJoin('appointments', 'prescriptions.appointment_id', '=', 'appointments.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('prescriptions.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('prescriptions.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('prescriptions.branch_id', $request->branch_id));
    }

    /**
     * @return Builder<PatientTestRegistration>
     */
    private function patientTestRegistrationReportQuery(Request $request): Builder
    {
        $icuTable = (new ICU)->getTable();

        return PatientTestRegistration::query()
            ->leftJoin('lab_types', 'patient_test_registrations.lab_type_id', '=', 'lab_types.id')
            ->leftJoin('appointments', function ($join) {
                $join->on('patient_test_registrations.testable_id', '=', 'appointments.id')
                    ->where('patient_test_registrations.testable_type', '=', Appointment::class);
            })
            ->leftJoin('hospitalizations', function ($join) {
                $join->on('patient_test_registrations.testable_id', '=', 'hospitalizations.id')
                    ->where('patient_test_registrations.testable_type', '=', Hospitalization::class);
            })
            ->leftJoin('under_reviews', function ($join) {
                $join->on('patient_test_registrations.testable_id', '=', 'under_reviews.id')
                    ->where('patient_test_registrations.testable_type', '=', UnderReview::class);
            })
            ->leftJoin($icuTable, function ($join) use ($icuTable) {
                $join->on('patient_test_registrations.testable_id', '=', "{$icuTable}.id")
                    ->where('patient_test_registrations.testable_type', '=', ICU::class);
            })
            ->leftJoin('patients', function ($join) use ($icuTable) {
                $join->whereRaw("patients.id = COALESCE(appointments.patient_id, hospitalizations.patient_id, under_reviews.patient_id, {$icuTable}.patient_id)");
            })
            ->when($request->filled('date_from'), fn ($query) => $query->where('patient_test_registrations.registration_date', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('patient_test_registrations.registration_date', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('patient_test_registrations.branch_id', $request->branch_id))
            ->when($request->filled('department_id'), function ($query) use ($request) {
                $departmentId = $request->department_id;

                $query->where(function ($departmentQuery) use ($departmentId) {
                    $departmentQuery
                        ->where('lab_types.department_id', $departmentId)
                        ->orWhere('appointments.department_id', $departmentId)
                        ->orWhere('hospitalizations.department_id', $departmentId)
                        ->orWhere('under_reviews.department_id', $departmentId);
                });
            });
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function departmentsForReportFilter(Request $request): array
    {
        return Department::query()
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->branch_id))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Department $department) => [
                'id' => $department->id,
                'name' => $department->name,
            ])
            ->values()
            ->all();
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
     * @return Builder<Hospitalization>
     */
    private function hospitalizationReportQuery(Request $request): Builder
    {
        return Hospitalization::query()
            ->leftJoin('departments', 'hospitalizations.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'hospitalizations.patient_id', '=', 'patients.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('hospitalizations.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('hospitalizations.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('hospitalizations.branch_id', $request->branch_id));
    }

    /**
     * @return Builder<Anesthesia>
     */
    private function anesthesiaDemographicQuery(Request $request): Builder
    {
        return Anesthesia::query()
            ->leftJoin('appointments', 'anesthesias.appointment_id', '=', 'appointments.id')
            ->leftJoin('departments', 'appointments.department_id', '=', 'departments.id')
            ->leftJoin('patients', 'anesthesias.patient_id', '=', 'patients.id')
            ->leftJoin('militery_types', 'patients.militery_type_id', '=', 'militery_types.id')
            ->leftJoin('relations', 'patients.relation_id', '=', 'relations.id')
            ->when($request->filled('date_from'), fn ($query) => $query->where('anesthesias.created_at', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn ($query) => $query->where('anesthesias.created_at', '<=', Verta::parse($request->date_to)->datetime()))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('anesthesias.branch_id', $request->branch_id));
    }

    private function departmentDemographicSelect(string $departmentColumn): string
    {
        return "
            {$departmentColumn} as department_id,
            departments.name as department_name,
            patients.gender,
            patients.job_category,
            patients.job_type,
            patients.type as patient_type,
            appointments.clinic_type,
            patients.militery_type_id,
            militery_types.name as militery_type_name,
            patients.relation_id,
            relations.name as relation_name,
            patients.commanded_by,
            COUNT(*) as count
        ";
    }

    /**
     * @return list<string>
     */
    private function departmentDemographicGroupBy(string $departmentColumn): array
    {
        return [
            $departmentColumn,
            'departments.name',
            'patients.gender',
            'patients.job_category',
            'patients.job_type',
            'patients.type',
            'appointments.clinic_type',
            'patients.militery_type_id',
            'militery_types.name',
            'patients.relation_id',
            'relations.name',
            'patients.commanded_by',
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function mapDepartmentDemographicRows(Collection $rows): Collection
    {
        return $rows
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
                    'clinic_types' => $this->aggregateBreakdown($departmentRows, 'clinic_type'),
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
