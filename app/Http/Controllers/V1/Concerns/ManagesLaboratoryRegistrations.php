<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\User;
use Illuminate\Support\Collection;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesLaboratoryRegistrations
{
    /**
     * @return Builder<PatientTestRegistration>
     */
    protected function scopedRegistrationQuery(User $user): Builder
    {
        return PatientTestRegistration::query()
            ->where('branch_id', $user->branch_id)
            ->visibleToClinicType($user->clinic_type);
    }

    /**
     * @param  Builder<PatientTestRegistration>  $query
     * @return Builder<PatientTestRegistration>
     */
    protected function applyResultsAccessControl(Builder $query, User $user, string $listMode): Builder
    {
        if ($user->hasRole(['super_admin', 'admin']) || $user->can('view-all-sections')) {
            return $query;
        }

        return match ($listMode) {
            'pending' => $query->whereNull('assigned_to'),
            'in_progress' => $query->where('assigned_to', $user->id),
            'completed' => $query->where(function ($statusQuery) use ($user) {
                $statusQuery->where('completed_by', $user->id)
                    ->orWhere('assigned_to', $user->id);
            }),
            default => $query,
        };
    }

    /**
     * @param  Builder<PatientTestRegistration>  $query
     * @return Builder<PatientTestRegistration>
     */
    protected function applyResultsFilters(Builder $query, Request $request, ?string $forcedStatus = null): Builder
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('testable', function ($testableQuery) use ($search) {
                $testableQuery->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%');
                });
            });
        }

        if ($request->filled('patient_id')) {
            $query->whereHas('testable', function ($testableQuery) use ($request) {
                $testableQuery->whereHas('patient', function ($patientQuery) use ($request) {
                    $patientQuery->where('id', $request->patient_id);
                });
            });
        }

        if ($forcedStatus) {
            $query->where('status', $forcedStatus);
        } elseif ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('doctor')) {
            $query->where('doctor_id', $request->doctor);
        }

        return $this->applyDateFilters($query, $request);
    }

    /**
     * @param  Builder<PatientTestRegistration>  $query
     * @return Builder<PatientTestRegistration>
     */
    protected function applyDateFilters(Builder $query, Request $request, string $dateField = 'registration_date'): Builder
    {
        if ($request->filled('date_from_gregorian')) {
            $query->whereDate($dateField, '>=', $request->date_from_gregorian);
        } elseif ($request->filled('date_from')) {
            $dateFrom = $this->convertPersianDate($request->date_from);
            if ($dateFrom !== null) {
                $query->whereDate($dateField, '>=', $dateFrom);
            }
        }

        if ($request->filled('date_to_gregorian')) {
            $query->whereDate($dateField, '<=', $request->date_to_gregorian);
        } elseif ($request->filled('date_to')) {
            $dateTo = $this->convertPersianDate($request->date_to);
            if ($dateTo !== null) {
                $query->whereDate($dateField, '<=', $dateTo);
            }
        }

        if ($request->filled('from')) {
            $dateFrom = $this->convertPersianDate($request->from);
            if ($dateFrom !== null) {
                $query->whereDate($dateField, '>=', $dateFrom);
            }
        }

        if ($request->filled('to')) {
            $dateTo = $this->convertPersianDate($request->to);
            if ($dateTo !== null) {
                $query->whereDate($dateField, '<=', $dateTo);
            }
        }

        return $query;
    }

    protected function convertPersianDate(mixed $value): mixed
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = trim((string) $value);

        try {
            return Verta::parse($value)->datetime();
        } catch (\Exception $e) {
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return $value;
            }

            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function transformPatientGroups($paginator, User $user): array
    {
        $grouped = collect($paginator->items())->groupBy(function (PatientTestRegistration $registration) {
            return $registration->testable?->patient?->id ?? 'unknown';
        });

        return $grouped
            ->map(function ($registrations, $patientId) use ($user) {
                $patient = $registrations->first()?->testable?->patient;

                if (! $patient) {
                    return null;
                }

                return [
                    'patient_id' => (int) $patientId,
                    'patient_name' => trim("{$patient->name} {$patient->last_name}"),
                    'father_name' => $patient->father_name,
                    'phone' => $patient->phone,
                    'age' => $patient->age,
                    'registration_count' => $registrations->count(),
                    'pending_accept_count' => $registrations
                        ->where('status', 'pending')
                        ->whereNull('assigned_to')
                        ->count(),
                    'registrations' => $registrations
                        ->map(fn (PatientTestRegistration $registration) => $this->transformRegistrationListItem($registration, $user))
                        ->values()
                        ->all(),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformRegistrationListItem(PatientTestRegistration $registration, User $user): array
    {
        $parameterCount = $registration->labType?->direct_lab_test_parameters_count ?? 0;
        $displayDate = $registration->testable?->date ?? $registration->registration_date;

        return [
            'id' => $registration->id,
            'ref_no' => $registration->ref_no,
            'lab_type_name' => $registration->labType?->name,
            'category_name' => $registration->labType?->category?->name,
            'is_parametered' => $parameterCount > 0,
            'status' => $registration->status,
            'priority' => $registration->priority,
            'doctor_name' => $registration->doctor?->name,
            'assigned_to_name' => $registration->assignedTo
                ? trim("{$registration->assignedTo->name} {$registration->assignedTo->last_name}")
                : null,
            'date' => $displayDate ? verta($displayDate)->format('Y-m-d') : null,
            'registration_date' => $registration->registration_date
                ? verta($registration->registration_date)->format('Y-m-d')
                : null,
            'completed_at' => $registration->completed_at
                ? verta($registration->completed_at)->format('Y-m-d H:i')
                : null,
            'permissions' => [
                'accept' => $user->can('accept', $registration),
                'enterResults' => in_array($registration->status, ['pending', 'in_progress'], true)
                    && $user->can('fillResults', $registration),
                'markCompleted' => $registration->status === 'in_progress'
                    && $user->can('updateStatus', $registration),
                'cancel' => in_array($registration->status, ['pending', 'in_progress'], true)
                    && $user->can('updateStatus', $registration),
                'print' => $registration->status === 'completed',
            ],
            'urls' => [
                'accept' => route('react.laboratory.results.accept', $registration),
                'enterResults' => route('react.laboratory.results.show', $registration),
                'print' => route('react.laboratory.reports.print', $registration->ref_no),
                'markCompleted' => route('react.laboratory.registrations.mark-completed', $registration),
                'cancel' => route('react.laboratory.registrations.cancel', $registration),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function resultsFiltersFromRequest(Request $request): array
    {
        return [
            'search' => (string) $request->input('search', ''),
            'patient_id' => (string) $request->input('patient_id', ''),
            'status' => (string) $request->input('status', ''),
            'priority' => (string) $request->input('priority', ''),
            'date_from' => (string) $request->input('date_from', ''),
            'date_to' => (string) $request->input('date_to', ''),
            'per_page' => (string) $request->input('per_page', '50'),
        ];
    }

    /**
     * @return Collection<int, PatientTestResult>
     */
    protected function loadOrCreateResultEntries(PatientTestRegistration $registration): Collection
    {
        $results = PatientTestResult::query()
            ->where('test_registration_id', $registration->id)
            ->with('parameter')
            ->get();

        if ($results->isNotEmpty()) {
            return $results;
        }

        $registration->loadMissing('labType.directLabTestParameters');

        return collect($registration->labType?->directLabTestParameters ?? [])
            ->map(fn ($parameter) => new PatientTestResult([
                'lab_parameter_id' => $parameter->id,
                'parameter' => $parameter,
                'unit' => $parameter->unit,
                'normal_range' => $parameter->normal_range,
                'result' => null,
                'text_result' => null,
            ]));
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformResultEntry(PatientTestResult $result): array
    {
        return [
            'id' => $result->id,
            'lab_parameter_id' => $result->lab_parameter_id,
            'parameter_name' => $result->parameter?->parameter_name,
            'unit' => $result->unit ?? $result->parameter?->unit,
            'normal_range' => $result->normal_range ?? $result->parameter?->normal_range,
            'result' => $result->result,
            'text_result' => $result->text_result,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function paginatedInertiaPayload($paginator): array
    {
        return [
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
}
