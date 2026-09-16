<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Database\Eloquent\Builder;
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

    protected function proceduresInUserBranchQuery(): Builder
    {
        return PhysiotherapyProcedure::query()->when(auth()->user()?->branch_id, function (Builder $query) {
            $branchId = auth()->user()->branch_id;
            $query->whereIn('appointment_id', function ($sub) use ($branchId) {
                $sub->select('id')
                    ->from('appointments')
                    ->where('branch_id', $branchId)
                    ->whereNull('deleted_at');
            });
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function generatePhysiotherapySummaryReport(string $startDate, string $endDate): array
    {
        $base = $this->proceduresInUserBranchQuery()
            ->whereBetween('start_date', [$startDate, $endDate]);

        $row = (clone $base)
            ->reorder()
            ->toBase()
            ->selectRaw('
                COUNT(*) as total_procedures,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_procedures,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as in_progress_procedures,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending_procedures,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_procedures,
                COALESCE(SUM(duration), 0) as total_duration
            ', ['completed', 'in_progress', 'pending', 'cancelled'])
            ->first();

        $totalProcedures = (int) ($row->total_procedures ?? 0);
        $completedProcedures = (int) ($row->completed_procedures ?? 0);
        $totalDuration = (float) ($row->total_duration ?? 0);

        return [
            'total_procedures' => $totalProcedures,
            'completed_procedures' => $completedProcedures,
            'in_progress_procedures' => (int) ($row->in_progress_procedures ?? 0),
            'pending_procedures' => (int) ($row->pending_procedures ?? 0),
            'cancelled_procedures' => (int) ($row->cancelled_procedures ?? 0),
            'total_duration' => $totalDuration,
            'average_duration' => $totalProcedures > 0
                ? round($totalDuration / $totalProcedures, 2)
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
        return $this->proceduresInUserBranchQuery()
            ->whereBetween('start_date', [$startDate, $endDate])
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
        $branchProcedureIds = $this->proceduresInUserBranchQuery()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->pluck('id');

        return PhysiotherapyType::with(['physiotherapyProcedures' => function ($query) use ($startDate, $endDate, $branchProcedureIds) {
            $query->whereIn('id', $branchProcedureIds)
                ->whereBetween('start_date', [$startDate, $endDate]);
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
        $branchId = auth()->user()?->branch_id;

        return Doctor::query()
            ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
            ->whereHas('physiotherapyProcedures', function ($query) use ($startDate, $endDate, $branchId) {
                $query->whereBetween('start_date', [$startDate, $endDate]);
                if ($branchId) {
                    $query->whereHas('appointment', fn (Builder $appointmentQuery) => $appointmentQuery->where('branch_id', $branchId));
                }
            })
            ->with([
                'user:id,email',
                'physiotherapyProcedures' => function ($query) use ($startDate, $endDate, $branchId) {
                    $query->whereBetween('start_date', [$startDate, $endDate]);
                    if ($branchId) {
                        $query->whereHas('appointment', fn (Builder $appointmentQuery) => $appointmentQuery->where('branch_id', $branchId));
                    }
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
