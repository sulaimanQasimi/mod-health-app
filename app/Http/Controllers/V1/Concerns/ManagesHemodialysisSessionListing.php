<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Http\Controllers\HemodialysisSessionController as LegacyHemodialysisSessionController;
use App\Http\Controllers\NephrologyRegistrationController;
use App\Models\Doctor;
use App\Models\HemodialysisSession;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesHemodialysisSessionListing
{
    private const LIST_FILTER_KEYS = [
        'patient_id',
        'patient_name',
        'session_date',
        'date_from',
        'date_to',
        'doctor_id',
        'status',
        'per_page',
    ];

    protected function listFilters(Request $request): array
    {
        return $request->only(self::LIST_FILTER_KEYS);
    }

    protected function baseSessionQuery(): Builder
    {
        return HemodialysisSession::query()->with([
            'patient:id,name,last_name,id_card',
            'doctor:id,name',
            'nephrologyRegistration.disease:id,name',
            'branch:id,name',
        ]);
    }

    protected function scopedSessionQuery(Request $request): Builder
    {
        $query = $this->baseSessionQuery();

        if ($request->user()->branch_id) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        return $query;
    }

    protected function applySessionFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (! empty($filters['patient_id'])) {
            $query->where('patient_id', $filters['patient_id']);
        }

        if (! empty($filters['patient_name'])) {
            $name = $filters['patient_name'];
            $query->whereHas('patient', function (Builder $patientQuery) use ($name) {
                $patientQuery->where('name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%')
                    ->orWhere('id_card', 'like', '%'.$name.'%');
            });
        }

        foreach (['session_date' => '=', 'date_from' => '>=', 'date_to' => '<='] as $field => $operator) {
            if (empty($filters[$field])) {
                continue;
            }

            try {
                $date = LegacyHemodialysisSessionController::normalizeSessionDate($filters[$field]);
                $query->whereDate('session_date', $operator, $date);
            } catch (\Exception $e) {
                // ignore invalid filter
            }
        }

        return $query->latest('session_date')->latest('id');
    }

    protected function paginateSessions(Builder $query, array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 25);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 25;

        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'data' => collect($paginator->items())
                ->map(fn (HemodialysisSession $session) => $this->transformListItem($session))
                ->values()
                ->all(),
            'links' => $paginator->linkCollection()->toArray(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }

    protected function sessionStats(Builder $query): array
    {
        $rows = (clone $query)->get(['id', 'status']);

        return [
            'total' => $rows->count(),
            'pending' => $rows->where('status', 'pending')->count(),
            'in_progress' => $rows->where('status', 'in_progress')->count(),
            'completed' => $rows->where('status', 'completed')->count(),
            'cancelled' => $rows->where('status', 'cancelled')->count(),
        ];
    }

    protected function filterOptions(): array
    {
        return [
            'doctors' => $this->nephrologistDoctorsForForm(),
        ];
    }

    protected function transformListItem(HemodialysisSession $session): array
    {
        $patient = $session->patient;
        $diagnosis = $session->diagnosis ?: $session->nephrologyRegistration?->displayDiagnosis();

        return [
            'id' => $session->id,
            'ref_no' => $session->ref_no,
            'patient_identifier' => $patient?->id_card ?? $session->patient_id,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'diagnosis' => $diagnosis ? mb_strimwidth($diagnosis, 0, 40, '…') : null,
            'session_date' => $session->session_date
                ? verta($session->session_date)->format('Y-m-d')
                : null,
            'session_time' => $session->session_time
                ? \Carbon\Carbon::parse($session->session_time)->format('H:i')
                : null,
            'duration_minutes' => $session->duration_minutes,
            'doctor_name' => $session->doctor?->name,
            'status' => $session->status,
        ];
    }

    protected function transformDetail(HemodialysisSession $session): array
    {
        $patient = $session->patient;
        $registration = $session->nephrologyRegistration;

        return [
            'id' => $session->id,
            'ref_no' => $session->ref_no,
            'status' => $session->status,
            'patient_id' => $session->patient_id,
            'patient_identifier' => $patient?->id_card ?? $session->patient_id,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'nephrology_registration_id' => $session->nephrology_registration_id,
            'nephrology_registration_ref_no' => $registration?->ref_no,
            'doctor_id' => $session->doctor_id,
            'doctor_name' => $session->doctor?->name,
            'diagnosis' => $session->diagnosis,
            'dialysis_schedule' => $session->dialysis_schedule,
            'session_date' => $session->session_date
                ? verta($session->session_date)->format('Y-m-d')
                : null,
            'session_time' => $session->session_time
                ? \Carbon\Carbon::parse($session->session_time)->format('H:i')
                : null,
            'duration_minutes' => $session->duration_minutes,
            'vascular_access_type' => $session->vascular_access_type,
            'pre_blood_pressure' => $session->pre_blood_pressure,
            'pre_weight' => $session->pre_weight,
            'pre_pulse' => $session->pre_pulse,
            'pre_temperature' => $session->pre_temperature,
            'post_blood_pressure' => $session->post_blood_pressure,
            'post_weight' => $session->post_weight,
            'post_pulse' => $session->post_pulse,
            'post_temperature' => $session->post_temperature,
            'fluid_removed_ml' => $session->fluid_removed_ml,
            'dialyzer_type' => $session->dialyzer_type,
            'blood_type' => $session->blood_type,
            'complications_notes' => $session->complications_notes,
            'branch_name' => $session->branch?->name,
        ];
    }

    protected function transformFormSession(HemodialysisSession $session): array
    {
        $detail = $this->transformDetail($session);
        $patient = $session->patient;
        $registration = $session->nephrologyRegistration;

        return array_merge($detail, [
            'patient_label' => $patient
                ? trim($patient->name.' '.$patient->last_name).' ('.($patient->id_card ?? $patient->id).')'
                : null,
            'nephrology_registration_label' => $registration
                ? localize('global.ref_no').': '.$registration->ref_no
                : null,
            'default_diagnosis' => $session->diagnosis
                ?: $registration?->displayDiagnosis(),
        ]);
    }

    protected function nephrologistDoctorsForForm()
    {
        return NephrologyRegistrationController::nephrologistDoctors()
            ->map(fn ($doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
            ->values();
    }

    protected function listPermissions($user): array
    {
        return [
            'edit' => $this->canManageSessions($user),
            'delete' => $this->canManageSessions($user),
        ];
    }

    protected function canManageSessions($user): bool
    {
        return $user->can('access-nephrology-registrations')
            && (bool) optional($user->doctor)->is_nephrologist;
    }

    protected function authorizeSessionAccess(HemodialysisSession $session, $user): void
    {
        $this->authorizeAccess($user);

        $branchId = $user->branch_id;
        if ($branchId && (int) $session->branch_id !== (int) $branchId) {
            abort(403, localize('global.nephrology_access_branch_denied'));
        }
    }

    protected function authorizeAccess($user): void
    {
        abort_unless($user->can('access-nephrology-registrations'), 403);
        abort_unless(optional($user->doctor)->is_nephrologist, 403, localize('global.nephrology_access_nephrologist_only'));
    }
}
