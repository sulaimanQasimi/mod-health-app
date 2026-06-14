<?php

namespace App\Http\Controllers\V1\Concerns;

use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait ManagesPrescriptionReport
{
    private const PRESCRIPTION_REPORT_FILTER_KEYS = [
        'patient_name',
        'father_name',
        'is_completed',
        'pharmacy_id',
        'processed_by_user_id',
        'start',
        'end',
        'per_page',
    ];

    protected function prescriptionReportHasSearch(Request $request): bool
    {
        if ($request->boolean('search')) {
            return true;
        }

        foreach (self::PRESCRIPTION_REPORT_FILTER_KEYS as $key) {
            if ($key === 'per_page') {
                continue;
            }

            if ($request->filled($key)) {
                return true;
            }
        }

        return false;
    }

    protected function prescriptionReportBaseQuery(Request $request, ?string $viewerClinicType): Builder
    {
        $query = DB::table('prescriptions as a')
            ->leftJoin('patients as p', 'a.patient_id', '=', 'p.id')
            ->leftJoin('doctors as d', 'a.doctor_id', '=', 'd.id')
            ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
            ->leftJoin('pharmacies as ph', 'a.pharmacy_id', '=', 'ph.id')
            ->leftJoin('appointments as app', 'a.appointment_id', '=', 'app.id')
            ->leftJoin('departments as dept', 'app.department_id', '=', 'dept.id')
            ->leftJoin('users as processor', 'a.updated_by', '=', 'processor.id')
            ->leftJoin('users as creator', 'a.created_by', '=', 'creator.id')
            ->select(
                'a.id',
                'p.name as patient_name',
                'p.father_name as patient_father_name',
                'p.id_card as patient_id_card',
                'd.name as doctor_name',
                'b.name as branch_name',
                'ph.name as pharmacy_name',
                'dept.name as department_name',
                'dept.id as department_id',
                'a.is_completed',
                'a.created_at',
                'a.pharmacy_id',
                DB::raw("TRIM(CONCAT(COALESCE(processor.name,''), ' ', COALESCE(processor.last_name,''))) as processor_name")
            );

        if ($viewerClinicType && $viewerClinicType !== 'both') {
            $query->whereIn('creator.clinic_type', [$viewerClinicType, 'both']);
        }

        $branchId = auth()->user()?->branch_id;
        if ($branchId) {
            $query->where('a.branch_id', $branchId);
        }

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('father_name')) {
            $query->where('p.father_name', 'like', '%'.$request->father_name.'%');
        }

        if ($request->filled('is_completed') && $request->is_completed !== '') {
            $query->where('a.is_completed', $request->is_completed);
        }

        if ($request->filled('pharmacy_id')) {
            $query->where('a.pharmacy_id', $request->pharmacy_id);
        }

        if ($request->filled('processed_by_user_id')) {
            $query->where('a.updated_by', $request->processed_by_user_id);
        }

        if ($request->filled('start') && $request->filled('end')) {
            try {
                $fromDate = Verta::parse($request->start)->datetime();
                $toDate = Verta::parse($request->end)->datetime();
                $query->whereDate('a.created_at', '>=', $fromDate)
                    ->whereDate('a.created_at', '<=', $toDate);
            } catch (\Throwable) {
                // Skip invalid jalali range.
            }
        }

        return $query->orderByDesc('a.created_at');
    }

    /**
     * @return array{total: int, completed: int, pending: int}
     */
    protected function prescriptionReportSummary(Builder $query): array
    {
        $total = (clone $query)->count();
        $completed = (clone $query)->where('a.is_completed', '1')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'pending' => max(0, $total - $completed),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformPrescriptionReportItem(object $row): array
    {
        $createdAt = null;
        if ($row->created_at) {
            try {
                $createdAt = verta($row->created_at)->format('Y/m/d H:i');
            } catch (\Throwable) {
                $createdAt = (string) $row->created_at;
            }
        }

        return [
            'id' => (int) $row->id,
            'patient_name' => $row->patient_name,
            'patient_father_name' => $row->patient_father_name,
            'patient_id_card' => $row->patient_id_card,
            'doctor_name' => $row->doctor_name,
            'branch_name' => $row->branch_name,
            'pharmacy_name' => $row->pharmacy_name,
            'department_name' => $row->department_name,
            'processor_name' => $row->processor_name ?: null,
            'is_completed' => (bool) $row->is_completed,
            'created_at' => $createdAt,
            'urls' => [
                'show' => route('react.prescriptions.show', $row->id),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function prescriptionReportFilterKeys(): array
    {
        return self::PRESCRIPTION_REPORT_FILTER_KEYS;
    }
}
