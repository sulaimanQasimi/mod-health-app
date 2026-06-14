<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesAppointmentReport
{
    private const APPOINTMENT_REPORT_FILTER_KEYS = [
        'patient_name',
        'doctor_id',
        'processed_by',
        'registered_by',
        'is_completed',
        'start',
        'end',
        'time',
        'clinic_type',
        'job',
        'job_type',
        'gender',
        'rank',
        'relation_id',
        'province_id',
        'district_id',
        'per_page',
    ];

    protected function appointmentReportHasSearch(Request $request): bool
    {
        if ($request->boolean('search')) {
            return true;
        }

        foreach (self::APPOINTMENT_REPORT_FILTER_KEYS as $key) {
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
     * @return Builder<Appointment>
     */
    protected function appointmentReportBaseQuery(Request $request, int $branchId): Builder
    {
        $query = Appointment::query()
            ->where('branch_id', $branchId)
            ->with([
                'patient.relation:id,name',
                'patient.province:id,name,name_dr',
                'patient.district:id,name,name_dr',
                'creator:id,name,last_name',
                'doctor:id,name',
                'processedBy:id,name,last_name',
                'branch:id,name',
            ])
            ->select([
                'appointments.id',
                'appointments.patient_id',
                'appointments.doctor_id',
                'appointments.branch_id',
                'appointments.clinic_type',
                'appointments.is_completed',
                'appointments.date',
                'appointments.time',
                'appointments.processed_by',
                'appointments.created_by',
            ]);

        $this->applyAppointmentReportFilters($query, $request);

        return $query->orderByDesc('appointments.date')
            ->orderByDesc('appointments.time');
    }

    /**
     * @param  Builder<Appointment>  $query
     */
    protected function applyAppointmentReportFilters(Builder $query, Request $request): void
    {
        $hasPatientFilter = $request->filled('patient_name')
            || $request->filled('job')
            || $request->filled('job_type')
            || ($request->filled('gender') && $request->gender !== '')
            || $request->filled('rank')
            || $request->filled('relation_id')
            || $request->filled('province_id')
            || $request->filled('district_id');

        if ($hasPatientFilter) {
            $query->whereHas('patient', function ($patientQuery) use ($request) {
                if ($request->filled('patient_name')) {
                    $patientQuery->where('name', 'like', '%'.$request->patient_name.'%');
                }
                if ($request->filled('job')) {
                    $patientQuery->where('job', 'like', '%'.$request->job.'%');
                }
                if ($request->filled('job_type')) {
                    $patientQuery->where('job_type', $request->job_type);
                }
                if ($request->filled('gender') && $request->gender !== '') {
                    $patientQuery->where('gender', $request->gender);
                }
                if ($request->filled('rank')) {
                    $patientQuery->where('rank', 'like', '%'.$request->rank.'%');
                }
                if ($request->filled('relation_id')) {
                    $patientQuery->where('relation_id', $request->relation_id);
                }
                if ($request->filled('province_id')) {
                    $patientQuery->where('province_id', $request->province_id);
                }
                if ($request->filled('district_id')) {
                    $patientQuery->where('district_id', $request->district_id);
                }
            });
        }

        if ($request->filled('registered_by')) {
            $query->where('appointments.created_by', $request->registered_by);
        }

        if ($request->filled('clinic_type')) {
            $query->where('appointments.clinic_type', $request->clinic_type);
        }

        if ($request->filled('doctor_id')) {
            $query->where('appointments.doctor_id', $request->doctor_id);
        }

        if ($request->filled('processed_by')) {
            $query->where('appointments.processed_by', $request->processed_by);
        }

        if ($request->filled('start') && $request->filled('end')) {
            try {
                $startDate = verta()->parse($request->start)->datetime();
                $endDate = verta()->parse($request->end)->datetime();
                $query->whereDate('appointments.date', '>=', $startDate)
                    ->whereDate('appointments.date', '<=', $endDate);
            } catch (\Throwable) {
                // Skip invalid jalali range.
            }
        }

        if ($request->filled('time')) {
            $query->where('appointments.time', $request->time);
        }

        if ($request->filled('is_completed') && $request->is_completed !== '') {
            $query->where('appointments.is_completed', $request->is_completed);
        }
    }

    /**
     * @return array{total: int, completed: int, ongoing: int}
     */
    protected function appointmentReportSummary(Builder $query): array
    {
        $total = (clone $query)->count();
        $completed = (clone $query)->where('appointments.is_completed', '1')->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'ongoing' => max(0, $total - $completed),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformAppointmentReportItem(Appointment $appointment): array
    {
        $patient = $appointment->patient;
        $processor = $appointment->processedBy;
        $creator = $appointment->creator;

        return [
            'id' => $appointment->id,
            'patient_name' => $patient?->name,
            'doctor_name' => $appointment->doctor?->name,
            'branch_name' => $appointment->branch?->name,
            'clinic_type' => $appointment->clinic_type,
            'processed_by_name' => $processor
                ? trim($processor->name.' '.$processor->last_name)
                : null,
            'registered_by_name' => $creator
                ? trim($creator->name.' '.$creator->last_name)
                : null,
            'job' => $patient?->job,
            'job_type' => $patient?->job_type,
            'gender' => $patient?->gender,
            'rank' => $patient?->rank,
            'relation_name' => $patient?->relation?->name,
            'province_name' => $patient?->province?->name_dr ?? $patient?->province?->name,
            'district_name' => $patient?->district?->name_dr ?? $patient?->district?->name,
            'is_completed' => (bool) $appointment->is_completed,
            'date' => $this->formatAppointmentReportDate($appointment->date),
            'time' => $appointment->time,
            'urls' => [
                'show' => route('react.appointments.show', $appointment),
            ],
        ];
    }

    protected function formatAppointmentReportDate(mixed $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return verta($date)->format('Y/m/d');
        } catch (\Throwable) {
            return is_string($date) ? $date : null;
        }
    }
}
