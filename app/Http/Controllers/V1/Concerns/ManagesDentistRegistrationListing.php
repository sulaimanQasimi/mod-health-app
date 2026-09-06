<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Branch;
use App\Models\DentalChart;
use App\Models\DentalNote;
use App\Models\DentalTreatment;
use App\Models\DentalXray;
use App\Models\DentistRegistration;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesDentistRegistrationListing
{
    private const LIST_FILTER_KEYS = [
        'search',
        'status',
        'branch_id',
        'dentist_id',
        'sort_by',
        'sort_order',
        'per_page',
    ];

    protected function listFilters(Request $request): array
    {
        return $request->only(self::LIST_FILTER_KEYS);
    }

    protected function baseRegistrationQuery(): Builder
    {
        return DentistRegistration::query()->with([
            'appointment.patient:id,name,last_name',
            'appointment:id,patient_id,date,branch_id',
            'dentist:id,name',
            'branch:id,name',
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

    protected function applyRegistrationFilters(Builder $query, array $filters, ?int $userBranchId = null): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('appointment.patient', function (Builder $patientQuery) use ($search) {
                $patientQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['branch_id']) && ! $userBranchId) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['dentist_id'])) {
            $query->where('dentist_id', $filters['dentist_id']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'registration_date', 'status', 'ref_no'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
    }

    protected function paginateRegistrations(Builder $query, array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 25);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 25;

        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'data' => collect($paginator->items())
                ->map(fn (DentistRegistration $registration) => $this->transformListItem($registration))
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
        $branchId = $request->user()->branch_id;

        return [
            'branches' => $branchId
                ? Branch::query()->where('id', $branchId)->orderBy('name')->get(['id', 'name'])
                : Branch::query()->orderBy('name')->get(['id', 'name']),
            'dentists' => Doctor::query()
                ->where('active_status', true)
                ->where('is_dentist', true)
                ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    protected function transformListItem(DentistRegistration $registration): array
    {
        $patient = $registration->appointment?->patient;

        return [
            'id' => $registration->id,
            'ref_no' => $registration->ref_no,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'appointment_date' => $registration->appointment?->date
                ? verta($registration->appointment->date)->format('Y-m-d')
                : null,
            'dentist_name' => $registration->dentist?->name,
            'branch_name' => $registration->branch?->name,
            'registration_date' => $registration->registration_date
                ? verta($registration->registration_date)->format('Y-m-d')
                : null,
            'status' => $registration->status,
        ];
    }

    protected function transformDetail(DentistRegistration $registration): array
    {
        $patient = $registration->appointment?->patient;
        $latestCharts = $this->latestChartsForRegistration($registration);

        return [
            'id' => $registration->id,
            'ref_no' => $registration->ref_no,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'dentist_id' => $registration->dentist_id,
            'dentist_name' => $registration->dentist?->name,
            'branch_name' => $registration->branch?->name,
            'registration_date' => $registration->registration_date
                ? verta($registration->registration_date)->format('Y-m-d')
                : null,
            'status' => $registration->status,
            'notes' => $registration->notes,
            'appointment_id' => $registration->appointment_id,
            'appointment_date' => $registration->appointment?->date
                ? verta($registration->appointment->date)->format('Y-m-d')
                : null,
            'appointment_completed' => (bool) ($registration->appointment?->is_completed ?? false),
            'treatments' => $registration->treatments
                ->map(fn (DentalTreatment $treatment) => $this->transformTreatment($treatment))
                ->values()
                ->all(),
            'xrays' => $registration->xrays
                ->map(fn (DentalXray $xray) => $this->transformXray($xray))
                ->values()
                ->all(),
            'dental_notes' => $registration->dentalNotes
                ->map(fn (DentalNote $note) => $this->transformDentalNote($note))
                ->values()
                ->all(),
            'chart_entries' => $latestCharts
                ->map(fn (DentalChart $chart) => $this->transformChartEntry($chart))
                ->values()
                ->all(),
            'counts' => [
                'treatments' => $registration->treatments->count(),
                'xrays' => $registration->xrays->count(),
                'notes' => $registration->dentalNotes->count(),
                'charts' => $registration->dentalCharts->count(),
                'prescriptions' => $registration->appointment?->prescription?->count() ?? 0,
            ],
            'created_at' => $registration->created_at
                ? verta($registration->created_at)->format('Y-m-d H:i')
                : null,
        ];
    }

    /**
     * @return Collection<int, DentalChart>
     */
    protected function latestChartsForRegistration(DentistRegistration $registration): Collection
    {
        return $registration->dentalCharts
            ->sortByDesc(fn (DentalChart $chart) => $chart->chart_date?->format('Y-m-d').$chart->created_at?->format('Y-m-d H:i:s'))
            ->unique('tooth_number')
            ->values();
    }

    protected function transformTreatment(DentalTreatment $treatment): array
    {
        return [
            'id' => $treatment->id,
            'treatment_type' => $treatment->treatment_type,
            'tooth_number' => $treatment->tooth_number,
            'treatment_description' => $treatment->treatment_description,
            'treatment_date' => $treatment->treatment_date
                ? verta($treatment->treatment_date)->format('Y-m-d')
                : null,
            'status' => $treatment->status,
            'cost' => $treatment->cost,
            'notes' => $treatment->notes,
        ];
    }

    protected function transformXray(DentalXray $xray): array
    {
        return [
            'id' => $xray->id,
            'xray_type' => $xray->xray_type,
            'xray_date' => $xray->xray_date ? verta($xray->xray_date)->format('Y-m-d') : null,
            'file_url' => $xray->file_path ? url('/storage/'.$xray->file_path) : null,
            'description' => $xray->description,
            'notes' => $xray->notes,
        ];
    }

    protected function transformDentalNote(DentalNote $note): array
    {
        return [
            'id' => $note->id,
            'note_date' => $note->note_date ? verta($note->note_date)->format('Y-m-d') : null,
            'note_type' => $note->note_type,
            'content' => $note->content,
        ];
    }

    protected function transformChartEntry(DentalChart $chart): array
    {
        $implant = $chart->implant_details;

        return [
            'id' => $chart->id,
            'tooth_number' => $chart->tooth_number,
            'tooth_condition' => $chart->tooth_condition,
            'gum_health' => $chart->gum_health,
            'oral_hygiene_score' => $chart->oral_hygiene_score,
            'pocket_depth' => $chart->pocket_depth,
            'bleeding' => (bool) $chart->bleeding,
            'mobility' => $chart->mobility,
            'treatment_history' => $chart->treatment_history,
            'notes' => $chart->notes,
            'chart_date' => $chart->chart_date ? verta($chart->chart_date)->format('Y-m-d') : null,
            'implant_system_brand' => $implant['implant_system_brand'] ?? null,
            'implant_diameter' => $implant['implant_diameter'] ?? null,
            'implant_length' => $implant['implant_length'] ?? null,
            'implant_status' => $implant['implant_status'] ?? null,
            'implant_notes' => $implant['implant_notes'] ?? null,
            'edit_url' => route('dental-charts.edit', $chart->id),
            'update_url' => route('dental-charts.update', $chart->id),
            'destroy_url' => route('dental-charts.destroy', $chart->id),
        ];
    }

    protected function dentistDoctorsForForm(): Collection
    {
        $branchId = auth()->user()?->branch_id;

        return Doctor::query()
            ->where('active_status', true)
            ->where('is_dentist', true)
            ->when($branchId, fn (Builder $q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    protected function listPermissions($user): array
    {
        return [
            'edit' => $user->can('edit-dentist-registrations') || $user->can('access-dentist-registrations'),
            'delete' => $user->can('delete-dentist-registrations'),
        ];
    }

    protected function showPermissions($user): array
    {
        return [
            'edit' => $user->can('edit-dentist-registrations') || $user->can('access-dentist-registrations'),
            'delete' => $user->can('delete-dentist-registrations'),
            'manageTreatments' => $user->can('access-dentist-registrations'),
            'manageXrays' => $user->can('access-dentist-registrations'),
            'manageNotes' => $user->can('access-dentist-registrations'),
            'markStatus' => $user->can('access-dentist-registrations'),
        ];
    }

    protected function authorizeAccess($user): void
    {
        abort_unless($user->can('access-dentist-registrations'), 403);
    }

    protected function authorizeRegistrationAccess(DentistRegistration $registration, $user): void
    {
        $this->authorizeAccess($user);

        $branchId = $user->branch_id;
        if ($branchId && (int) $registration->branch_id !== (int) $branchId) {
            abort(404);
        }
    }
}
