<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DoctorPerformanceReportController extends Controller
{
    public function performance(Request $request): Response
    {
        $doctors = $this->doctorsWithRelations();
        $hasSearch = $request->boolean('search')
            && $request->filled('startDate')
            && $request->filled('endDate');

        $results = [];
        $summary = [
            'appointments' => 0,
            'prescriptions' => 0,
            'lab_tests' => 0,
            'anesthesias' => 0,
            'total' => 0,
        ];
        $error = null;

        if ($hasSearch) {
            $validated = $request->validate([
                'startDate' => 'required|string',
                'endDate' => 'required|string',
                'doctorId' => 'nullable|integer|exists:doctors,id',
            ]);

            try {
                $startDate = Verta::parse($validated['startDate'])->format('Y-m-d');
                $endDate = Verta::parse($validated['endDate'])->format('Y-m-d');

                if ($startDate > $endDate) {
                    $error = 'global.end_date_must_be_after_start_date';
                } else {
                    $rows = DB::select('CALL sp_doctor_performance_dynamic(?, ?, ?)', [
                        $startDate,
                        $endDate,
                        $validated['doctorId'] ?? null,
                    ]);

                    $results = collect($rows)->map(function ($row) {
                        return [
                            'user' => $row->User ?? null,
                            'appointments' => (int) ($row->Appointments ?? 0),
                            'prescriptions' => (int) ($row->Prescriptions ?? 0),
                            'lab_tests' => (int) ($row->LabTests ?? 0),
                            'anesthesias' => (int) ($row->Anesthesias ?? 0),
                            'total' => (int) ($row->Total ?? 0),
                        ];
                    })->values()->all();

                    $summary = [
                        'appointments' => collect($results)->sum('appointments'),
                        'prescriptions' => collect($results)->sum('prescriptions'),
                        'lab_tests' => collect($results)->sum('lab_tests'),
                        'anesthesias' => collect($results)->sum('anesthesias'),
                        'total' => collect($results)->sum('total'),
                    ];
                }
            } catch (\Throwable) {
                $error = 'global.invalid_date_format';
            }
        }

        return Inertia::render('DoctorPerformance/Report', [
            'results' => $results,
            'summary' => $summary,
            'hasSearch' => $hasSearch && $error === null,
            'error' => $error,
            'filters' => [
                'startDate' => (string) $request->input('startDate', ''),
                'endDate' => (string) $request->input('endDate', ''),
                'doctorId' => (string) $request->input('doctorId', ''),
            ],
            'filterOptions' => [
                'doctors' => $doctors,
            ],
            'urls' => [
                'current' => route('react.doctor-performance-report'),
            ],
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function doctorsWithRelations(): array
    {
        return Doctor::query()
            ->leftJoin('departments', 'doctors.department_id', '=', 'departments.id')
            ->select(
                'doctors.id',
                'doctors.name',
                'doctors.specialization',
                'departments.name as department_name'
            )
            ->where('doctors.active_status', true)
            ->orderBy('doctors.name')
            ->get()
            ->map(fn ($doctor) => [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'specialization' => $doctor->specialization,
                'department_name' => $doctor->department_name,
            ])
            ->all();
    }
}
