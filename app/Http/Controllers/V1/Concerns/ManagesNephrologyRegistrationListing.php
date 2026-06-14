<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Http\Controllers\NephrologyRegistrationController as LegacyNephrologyRegistrationController;
use App\Models\Branch;
use App\Models\Disease;
use App\Models\DiseaseCategory;
use App\Models\Doctor;
use App\Models\HemodialysisSession;
use App\Models\NephrologyRegistration;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesNephrologyRegistrationListing
{
    private const LIST_FILTER_KEYS = [
        'patient_id',
        'patient_name',
        'status',
        'branch_id',
        'doctor_id',
        'visit_date_from',
        'visit_date_to',
        'per_page',
    ];

    protected function listFilters(Request $request): array
    {
        return $request->only(self::LIST_FILTER_KEYS);
    }

    protected function baseRegistrationQuery(): Builder
    {
        return NephrologyRegistration::query()->with([
            'patient:id,name,last_name,father_name,phone,age,gender,id_card',
            'doctor:id,name',
            'branch:id,name',
            'disease:id,name',
        ]);
    }

    protected function scopedRegistrationQuery(Request $request): Builder
    {
        $query = $this->baseRegistrationQuery();

        if ($request->user()->branch_id) {
            $query->where('branch_id', $request->user()->branch_id);
        }

        return $query;
    }

    protected function applyRegistrationFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['branch_id']) && ! $request->user()->branch_id) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (! empty($filters['patient_id'])) {
            $patientIdInput = trim((string) $filters['patient_id']);
            $query->where(function (Builder $patientQuery) use ($patientIdInput) {
                if (is_numeric($patientIdInput)) {
                    $patientQuery->where('patient_id', (int) $patientIdInput);
                } else {
                    $patientQuery->whereHas('patient', function (Builder $innerQuery) use ($patientIdInput) {
                        $innerQuery->where('id_card', 'like', '%'.$patientIdInput.'%');
                    });
                }
            });
        }

        if (! empty($filters['patient_name'])) {
            $name = $filters['patient_name'];
            $query->whereHas('patient', function (Builder $patientQuery) use ($name) {
                $patientQuery->where('name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            });
        }

        if (! empty($filters['visit_date_from'])) {
            try {
                $query->whereDate(
                    'visit_date',
                    '>=',
                    LegacyNephrologyRegistrationController::normalizeVisitDate($filters['visit_date_from']),
                );
            } catch (\Exception $e) {
                // ignore invalid filter
            }
        }

        if (! empty($filters['visit_date_to'])) {
            try {
                $query->whereDate(
                    'visit_date',
                    '<=',
                    LegacyNephrologyRegistrationController::normalizeVisitDate($filters['visit_date_to']),
                );
            } catch (\Exception $e) {
                // ignore invalid filter
            }
        }

        return $query->latest();
    }

    protected function paginateRegistrations(Builder $query, array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 25);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 25;

        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'data' => collect($paginator->items())
                ->map(fn (NephrologyRegistration $registration) => $this->transformListItem($registration))
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

    protected function registrationStats(Builder $query): array
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

    protected function filterOptions(Request $request): array
    {
        $branches = $request->user()->branch_id
            ? Branch::query()->where('id', $request->user()->branch_id)->orderBy('name')->get(['id', 'name'])
            : Branch::query()->orderBy('name')->get(['id', 'name']);

        return [
            'branches' => $branches,
            'doctors' => $this->nephrologistDoctorsForForm(),
        ];
    }

    protected function transformListItem(NephrologyRegistration $registration): array
    {
        $patient = $registration->patient;

        return [
            'id' => $registration->id,
            'ref_no' => $registration->ref_no,
            'patient_identifier' => $patient?->id_card,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'father_name' => $patient?->father_name,
            'phone' => $patient?->phone,
            'age' => $patient?->age,
            'gender' => $patient?->gender,
            'visit_date' => $registration->visit_date
                ? verta($registration->visit_date)->format('Y-m-d')
                : null,
            'doctor_name' => $registration->doctor?->name,
            'status' => $registration->status,
            'diagnosis' => $registration->displayDiagnosis(),
            'needs_acceptance' => $registration->needsAcceptance(),
        ];
    }

    protected function transformDetail(NephrologyRegistration $registration): array
    {
        $patient = $registration->patient ?? $registration->appointment?->patient;
        $disease = $registration->disease;

        return [
            'id' => $registration->id,
            'ref_no' => $registration->ref_no,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'doctor_id' => $registration->doctor_id,
            'doctor_name' => $registration->doctor?->name,
            'branch_name' => $registration->branch?->name,
            'visit_date' => $registration->visit_date
                ? verta($registration->visit_date)->format('Y-m-d')
                : null,
            'status' => $registration->status,
            'chief_complaint' => $registration->chief_complaint,
            'diagnosis' => $registration->diagnosis,
            'disease_id' => $registration->disease_id,
            'disease_name' => $disease?->name,
            'disease_category_id' => $disease?->disease_category_id,
            'disease_category_name' => $disease?->category?->name,
            'ckd_aki_stage' => $registration->ckd_aki_stage,
            'dialysis_required' => (bool) $registration->dialysis_required,
            'dialysis_type' => $registration->dialysis_type,
            'access_type' => $registration->access_type,
            'notes' => $registration->notes,
            'follow_up_plan' => $registration->follow_up_plan,
            'appointment_id' => $registration->appointment_id,
            'patient_id' => $registration->patient_id,
            'needs_acceptance' => $registration->needsAcceptance(),
            'hemodialysis_sessions' => $registration->hemodialysisSessions
                ->map(fn (HemodialysisSession $session) => $this->transformHemodialysisSession($session))
                ->values()
                ->all(),
            'counts' => [
                'diagnoses' => $registration->appointment?->diagnose?->count() ?? 0,
                'lab_tests' => $registration->appointment?->patientTestRegistrations?->count() ?? 0,
                'prescriptions' => $registration->appointment?->prescription?->count() ?? 0,
                'hemodialysis' => $registration->hemodialysisSessions->count(),
            ],
        ];
    }

    protected function transformHemodialysisSession(HemodialysisSession $session): array
    {
        return [
            'id' => $session->id,
            'ref_no' => $session->ref_no,
            'session_date' => $session->session_date
                ? verta($session->session_date)->format('Y-m-d')
                : null,
            'duration_minutes' => $session->duration_minutes,
            'doctor_name' => $session->doctor?->name,
            'status' => $session->status,
            'show_url' => route('react.hemodialysis-sessions.show', $session),
        ];
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    protected function nephrologyDiseaseFormData(): array
    {
        $nephrologyDiseases = Disease::query()
            ->orderBy('name')
            ->get(['id', 'name', 'disease_category_id']);

        $diseaseCategories = DiseaseCategory::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return [$diseaseCategories, $nephrologyDiseases];
    }

    protected function nephrologistDoctorsForForm()
    {
        $doctors = Doctor::query()
            ->where('active_status', true)
            ->where('is_nephrologist', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        if ($doctors->isEmpty()) {
            return Doctor::query()
                ->where('active_status', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return $doctors;
    }

    protected function listPermissions($user): array
    {
        return [
            'accept' => (bool) optional($user->doctor)->is_nephrologist,
        ];
    }

    protected function showPermissions($user): array
    {
        $canManage = $user->can('access-nephrology-registrations')
            && (bool) optional($user->doctor)->is_nephrologist;

        return [
            'edit' => $canManage,
            'delete' => $canManage,
            'markStatus' => $canManage,
        ];
    }

    protected function authorizeRegistrationAccess(NephrologyRegistration $registration, $user): void
    {
        $this->authorizeAccess($user);

        $branchId = $user->branch_id;
        if ($branchId && (int) $registration->branch_id !== (int) $branchId) {
            abort(403, localize('global.nephrology_access_branch_denied'));
        }
    }

    protected function authorizeAccess($user): void
    {
        abort_unless($user->can('access-nephrology-registrations'), 403);
        abort_unless(optional($user->doctor)->is_nephrologist, 403, localize('global.nephrology_access_nephrologist_only'));
    }
}
