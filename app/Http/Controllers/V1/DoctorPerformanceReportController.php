<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPerformanceReportController extends Controller
{
    public function performance(Request $request): Response
    {
        $this->authorize('viewAny', Patient::class);

        $user = $request->user();
        $branchId = (int) ($user->branch_id ?? 0);

        $hasSearch = $request->boolean('search')
            && $request->filled('startDate')
            && $request->filled('endDate');

        $results = [];
        $summary = $this->emptySummary();
        $analytics = $this->emptyAnalytics();
        $error = null;

        if ($hasSearch) {
            $validated = $request->validate([
                'startDate' => 'required|string',
                'endDate' => 'required|string',
                'doctorId' => 'nullable|integer|exists:doctors,id',
                'department_id' => 'nullable|integer|exists:departments,id',
            ]);

            try {
                $startDate = Verta::parse($validated['startDate'])->format('Y-m-d');
                $endDate = Verta::parse($validated['endDate'])->format('Y-m-d');

                if ($startDate > $endDate) {
                    $error = 'global.end_date_must_be_after_start_date';
                } else {
                    $doctorId = ! empty($validated['doctorId']) ? (int) $validated['doctorId'] : null;
                    $departmentId = ! empty($validated['department_id']) ? (int) $validated['department_id'] : null;

                    if ($doctorId && $branchId) {
                        $owned = Doctor::query()
                            ->whereKey($doctorId)
                            ->where('branch_id', $branchId)
                            ->exists();

                        if (! $owned) {
                            abort(403);
                        }
                    }

                    $rows = $this->performanceRows(
                        $branchId,
                        $startDate,
                        $endDate,
                        $doctorId,
                        $departmentId,
                    );

                    $results = $rows->map(fn (array $row, int $index) => [
                        ...$row,
                        'rank' => $index + 1,
                        'percentage' => 0,
                    ])->values()->all();

                    $summary = $this->buildSummary($rows);
                    $analytics = $this->buildAnalytics($rows, $summary);

                    $grandTotal = max(1, (int) $summary['total']);
                    $results = array_map(function (array $row) use ($grandTotal) {
                        $row['percentage'] = round(($row['total'] / $grandTotal) * 100, 1);

                        return $row;
                    }, $results);
                }
            } catch (\Throwable) {
                $error = 'global.invalid_date_format';
            }
        }

        return Inertia::render('DoctorPerformance/Report', [
            'results' => $results,
            'summary' => $summary,
            'analytics' => $analytics,
            'hasSearch' => $hasSearch && $error === null,
            'error' => $error,
            'filters' => [
                'startDate' => (string) $request->input('startDate', ''),
                'endDate' => (string) $request->input('endDate', ''),
                'doctorId' => (string) $request->input('doctorId', ''),
                'department_id' => (string) $request->input('department_id', ''),
            ],
            'filterOptions' => [
                'doctors' => $this->doctorsForFilter($branchId),
                'departments' => $this->departmentsForFilter($user->category_id),
            ],
            'urls' => [
                'current' => route('doctor-performance-report'),
                'index' => route('patients.index'),
            ],
        ]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function performanceRows(
        int $branchId,
        string $startDate,
        string $endDate,
        ?int $doctorId,
        ?int $departmentId,
    ): Collection {
        $endDateTime = $endDate.' 23:59:59';

        $appointments = DB::table('appointments')
            ->select('doctor_id', DB::raw('COUNT(*) as aggregate_count'))
            ->whereNull('deleted_at')
            ->whereBetween('date', [$startDate, $endDate])
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('doctor_id');

        $prescriptions = DB::table('prescriptions')
            ->select('doctor_id', DB::raw('COUNT(*) as aggregate_count'))
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$startDate, $endDateTime])
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->groupBy('doctor_id');

        $labTests = DB::table('patient_test_registrations')
            ->select('doctor_id', DB::raw('COUNT(*) as aggregate_count'))
            ->whereNotNull('doctor_id')
            ->whereBetween('registration_date', [$startDate, $endDateTime])
            ->groupBy('doctor_id');

        $anesthesias = DB::table('anesthesias')
            ->select('doctor_id', DB::raw('COUNT(*) as aggregate_count'))
            ->whereBetween('created_at', [$startDate, $endDateTime])
            ->groupBy('doctor_id');

        $rows = Doctor::query()
            ->leftJoin('departments', 'doctors.department_id', '=', 'departments.id')
            ->leftJoinSub($appointments, 'appt', 'appt.doctor_id', '=', 'doctors.id')
            ->leftJoinSub($prescriptions, 'rx', 'rx.doctor_id', '=', 'doctors.id')
            ->leftJoinSub($labTests, 'lab', 'lab.doctor_id', '=', 'doctors.id')
            ->leftJoinSub($anesthesias, 'anes', 'anes.doctor_id', '=', 'doctors.id')
            ->where('doctors.active_status', true)
            ->when($branchId > 0, fn ($q) => $q->where('doctors.branch_id', $branchId))
            ->when($doctorId, fn ($q) => $q->where('doctors.id', $doctorId))
            ->when($departmentId, fn ($q) => $q->where('doctors.department_id', $departmentId))
            ->select([
                'doctors.id',
                'doctors.name',
                'doctors.specialization',
                'departments.name as department_name',
                DB::raw('COALESCE(appt.aggregate_count, 0) as appointments'),
                DB::raw('COALESCE(rx.aggregate_count, 0) as prescriptions'),
                DB::raw('COALESCE(lab.aggregate_count, 0) as lab_tests'),
                DB::raw('COALESCE(anes.aggregate_count, 0) as anesthesias'),
            ])
            ->orderBy('doctors.name')
            ->get()
            ->map(function ($row) {
                $appointments = (int) $row->appointments;
                $prescriptions = (int) $row->prescriptions;
                $labTests = (int) $row->lab_tests;
                $anesthesias = (int) $row->anesthesias;
                $total = $appointments + $prescriptions + $labTests + $anesthesias;

                return [
                    'id' => (int) $row->id,
                    'user' => $row->name,
                    'specialization' => $row->specialization,
                    'department_name' => $row->department_name,
                    'appointments' => $appointments,
                    'prescriptions' => $prescriptions,
                    'lab_tests' => $labTests,
                    'anesthesias' => $anesthesias,
                    'total' => $total,
                ];
            });

        // Prefer active doctors when browsing all; keep zeros when a single doctor is selected.
        if (! $doctorId) {
            $rows = $rows->filter(fn (array $row) => $row['total'] > 0)->values();
        }

        return $rows
            ->sortByDesc('total')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, int|float>
     */
    private function buildSummary(Collection $rows): array
    {
        $doctorCount = $rows->count();
        $total = (int) $rows->sum('total');

        return [
            'appointments' => (int) $rows->sum('appointments'),
            'prescriptions' => (int) $rows->sum('prescriptions'),
            'lab_tests' => (int) $rows->sum('lab_tests'),
            'anesthesias' => (int) $rows->sum('anesthesias'),
            'total' => $total,
            'doctor_count' => $doctorCount,
            'avg_per_doctor' => $doctorCount > 0 ? round($total / $doctorCount, 1) : 0,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, int|float>  $summary
     * @return array<string, mixed>
     */
    private function buildAnalytics(Collection $rows, array $summary): array
    {
        $byActivity = [
            ['name' => 'appointments', 'count' => (int) $summary['appointments']],
            ['name' => 'prescriptions', 'count' => (int) $summary['prescriptions']],
            ['name' => 'lab_tests', 'count' => (int) $summary['lab_tests']],
            ['name' => 'anesthesias', 'count' => (int) $summary['anesthesias']],
        ];

        $byDoctor = $rows
            ->take(10)
            ->map(fn (array $row) => [
                'name' => $row['user'] ?: '—',
                'count' => (int) $row['total'],
            ])
            ->values()
            ->all();

        $byDepartment = $rows
            ->groupBy(fn (array $row) => $row['department_name'] ?: '—')
            ->map(fn (Collection $group, string $name) => [
                'name' => $name,
                'count' => (int) $group->sum('total'),
            ])
            ->sortByDesc('count')
            ->take(10)
            ->values()
            ->all();

        return [
            'by_activity' => $byActivity,
            'by_doctor' => $byDoctor,
            'by_department' => $byDepartment,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    private function emptySummary(): array
    {
        return [
            'appointments' => 0,
            'prescriptions' => 0,
            'lab_tests' => 0,
            'anesthesias' => 0,
            'total' => 0,
            'doctor_count' => 0,
            'avg_per_doctor' => 0,
        ];
    }

    /**
     * @return array<string, list<array{name: string, count: int}>>
     */
    private function emptyAnalytics(): array
    {
        return [
            'by_activity' => [],
            'by_doctor' => [],
            'by_department' => [],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function doctorsForFilter(int $branchId): array
    {
        return Doctor::query()
            ->leftJoin('departments', 'doctors.department_id', '=', 'departments.id')
            ->select(
                'doctors.id',
                'doctors.name',
                'doctors.specialization',
                'doctors.department_id',
                'departments.name as department_name'
            )
            ->where('doctors.active_status', true)
            ->when($branchId > 0, fn ($query) => $query->where('doctors.branch_id', $branchId))
            ->orderBy('doctors.name')
            ->get()
            ->map(fn ($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
                'department_id' => $doctor->department_id,
                'department_name' => $doctor->department_name,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function departmentsForFilter(mixed $categoryId): array
    {
        return Department::query()
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($department) => [
                'id' => $department->id,
                'name' => $department->name,
            ])
            ->all();
    }
}
