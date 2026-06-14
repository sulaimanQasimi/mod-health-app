<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

trait ManagesPhysiotherapyReport
{
    /**
     * @return array{start: string, end: string}|null
     */
    protected function physiotherapyReportDateRange(Request $request): ?array
    {
        if (! $request->filled('start_date') || ! $request->filled('end_date')) {
            return null;
        }

        try {
            return [
                'start' => Verta::parse($request->start_date)->datetime()->format('Y-m-d H:i:s'),
                'end' => Verta::parse($request->end_date)->datetime()->format('Y-m-d H:i:s'),
            ];
        } catch (\Throwable) {
            return null;
        }
    }

    protected function physiotherapyReportHasSearch(Request $request): bool
    {
        return $request->boolean('search')
            && $request->filled('start_date')
            && $request->filled('end_date');
    }

    /**
     * @return array<string, mixed>
     */
    protected function generatePhysiotherapySummaryReport(string $startDate, string $endDate): array
    {
        $procedures = PhysiotherapyProcedure::whereBetween('start_date', [$startDate, $endDate])
            ->with(['appointment.patient:id,name,last_name', 'physiotherapyType:id,name', 'doctor:id,name'])
            ->get();

        $totalProcedures = $procedures->count();
        $completedProcedures = $procedures->where('status', 'completed')->count();

        return [
            'total_procedures' => $totalProcedures,
            'completed_procedures' => $completedProcedures,
            'in_progress_procedures' => $procedures->where('status', 'in_progress')->count(),
            'pending_procedures' => $procedures->where('status', 'pending')->count(),
            'cancelled_procedures' => $procedures->where('status', 'cancelled')->count(),
            'total_duration' => $procedures->sum('duration'),
            'average_duration' => $totalProcedures > 0
                ? round($procedures->sum('duration') / $totalProcedures, 2)
                : 0,
            'completion_rate' => $totalProcedures > 0
                ? round(($completedProcedures / $totalProcedures) * 100, 2)
                : 0,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function generatePhysiotherapyDetailedReport(string $startDate, string $endDate): array
    {
        return PhysiotherapyProcedure::whereBetween('start_date', [$startDate, $endDate])
            ->with(['appointment.patient:id,name,last_name', 'physiotherapyType:id,name', 'doctor:id,name'])
            ->orderByDesc('start_date')
            ->get()
            ->map(fn (PhysiotherapyProcedure $procedure) => $this->transformPhysiotherapyProcedure($procedure))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function generatePhysiotherapyByTypeReport(string $startDate, string $endDate): array
    {
        return PhysiotherapyType::with(['physiotherapyProcedures' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        }])
            ->get()
            ->map(function (PhysiotherapyType $type) {
                $procedures = $type->physiotherapyProcedures;
                $total = $procedures->count();
                $completed = $procedures->where('status', 'completed')->count();

                return [
                    'type_id' => $type->id,
                    'type_name' => $type->name,
                    'total_procedures' => $total,
                    'completed_procedures' => $completed,
                    'in_progress_procedures' => $procedures->where('status', 'in_progress')->count(),
                    'pending_procedures' => $procedures->where('status', 'pending')->count(),
                    'cancelled_procedures' => $procedures->where('status', 'cancelled')->count(),
                    'total_duration' => $procedures->sum('duration'),
                    'average_duration' => $total > 0
                        ? round($procedures->sum('duration') / $total, 2)
                        : 0,
                    'completion_rate' => $total > 0
                        ? round(($completed / $total) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function generatePhysiotherapyByPhysiotherapistReport(string $startDate, string $endDate): array
    {
        return Doctor::whereHas('physiotherapyProcedures', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        })
            ->with([
                'user:id,email',
                'physiotherapyProcedures' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('start_date', [$startDate, $endDate]);
                },
            ])
            ->get()
            ->map(function (Doctor $doctor) {
                $procedures = $doctor->physiotherapyProcedures;
                $completed = $procedures->where('status', 'completed')->count();
                $total = $procedures->count();

                return [
                    'name' => $doctor->name,
                    'email' => $doctor->user->email ?? null,
                    'total_procedures' => $total,
                    'completed_procedures' => $completed,
                    'in_progress_procedures' => $procedures->where('status', 'in_progress')->count(),
                    'pending_procedures' => $procedures->where('status', 'pending')->count(),
                    'cancelled_procedures' => $procedures->where('status', 'cancelled')->count(),
                    'total_duration' => $procedures->sum('duration'),
                    'average_duration' => $total > 0
                        ? round($procedures->sum('duration') / $total, 2)
                        : 0,
                    'completion_rate' => $total > 0
                        ? round(($completed / $total) * 100, 2)
                        : 0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformPhysiotherapyProcedure(PhysiotherapyProcedure $procedure): array
    {
        $patient = $procedure->appointment?->patient;
        $startDate = null;

        if ($procedure->start_date) {
            try {
                $startDate = verta($procedure->start_date)->format('Y/m/d');
            } catch (\Throwable) {
                $startDate = (string) $procedure->start_date;
            }
        }

        return [
            'id' => $procedure->id,
            'patient_name' => $patient
                ? trim($patient->name.' '.($patient->last_name ?? ''))
                : null,
            'type_name' => $procedure->physiotherapyType?->name,
            'doctor_name' => $procedure->doctor?->name,
            'status' => $procedure->status,
            'duration' => $procedure->duration,
            'start_date' => $startDate,
        ];
    }
}
