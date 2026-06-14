<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Doctor;
use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyType;
use App\Models\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait ManagesPhysiotherapyProcedureListing
{
    private const LIST_FILTER_KEYS = [
        'search',
        'status',
        'physiotherapy_type_id',
        'doctor_id',
        'start_date',
        'end_date',
        'sort_by',
        'sort_order',
        'per_page',
    ];

    protected function listFilters(Request $request): array
    {
        return $request->only(self::LIST_FILTER_KEYS);
    }

    protected function baseProcedureQuery(): Builder
    {
        return PhysiotherapyProcedure::query()->with([
            'appointment.patient:id,name,last_name,father_name,id_card,phone',
            'physiotherapyType:id,name',
            'doctor:id,name,user_id',
            'reviews:id,physiotherapy_procedure_id',
        ])->when(auth()->user()?->branch_id, function (Builder $query) {
            $query->whereHas('appointment', function (Builder $appointmentQuery) {
                $appointmentQuery->where('branch_id', auth()->user()->branch_id);
            });
        });
    }

    protected function applyProcedureFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('appointment.patient', function (Builder $patientQuery) use ($search) {
                $patientQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('id_card', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['physiotherapy_type_id'])) {
            $query->where('physiotherapy_type_id', $filters['physiotherapy_type_id']);
        }

        if (! empty($filters['doctor_id'])) {
            $query->where('doctor_id', $filters['doctor_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('start_date', Verta::parse($filters['start_date'])->datetime());
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('end_date', '<=', Verta::parse($filters['end_date'])->datetime());
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $allowedSort = ['created_at', 'start_date', 'status', 'counter', 'days_count'];
        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'created_at';
        }

        return $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
    }

    protected function paginateProcedures(Builder $query, array $filters): array
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = in_array($perPage, [10, 15, 25, 50], true) ? $perPage : 15;

        $paginator = $query->paginate($perPage)->withQueryString();

        return [
            'data' => collect($paginator->items())->map(fn (PhysiotherapyProcedure $procedure) => $this->transformListItem($procedure))->values()->all(),
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

    protected function procedureStats(Builder $query): array
    {
        $statsQuery = clone $query;
        $rows = $statsQuery->get(['id', 'status']);

        return [
            'total' => $rows->count(),
            'pending' => $rows->where('status', 'pending')->count(),
            'in_progress' => $rows->where('status', 'in_progress')->count(),
            'completed' => $rows->where('status', 'completed')->count(),
            'cancelled' => $rows->where('status', 'cancelled')->count(),
        ];
    }

    protected function transformListItem(PhysiotherapyProcedure $procedure): array
    {
        $percentage = $procedure->days_count > 0
            ? ($procedure->counter / max(1, $procedure->days_count)) * 100
            : 0;

        $patient = $procedure->appointment?->patient;

        return [
            'id' => $procedure->id,
            'appointment_id' => $procedure->appointment_id,
            'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            'patient_id_card' => $patient?->id_card,
            'patient_father_name' => $patient?->father_name,
            'patient_phone' => $patient?->phone,
            'physiotherapy_type' => $procedure->physiotherapyType?->name,
            'physiotherapist' => $procedure->doctor?->name,
            'type' => $procedure->type,
            'duration' => $procedure->duration,
            'counter' => $procedure->counter,
            'days_count' => $procedure->days_count,
            'progress_counter' => $procedure->counter,
            'progress_total' => $procedure->days_count,
            'progress_percentage' => round($percentage, 1),
            'status' => $procedure->status,
            'start_date' => $procedure->start_date ? verta($procedure->start_date)->format('Y-m-d') : null,
            'end_date' => $procedure->end_date ? verta($procedure->end_date)->format('Y-m-d') : null,
            'reviews_count' => $procedure->reviews->count(),
        ];
    }

    protected function transformDetail(PhysiotherapyProcedure $procedure): array
    {
        $item = $this->transformListItem($procedure);
        $patient = $procedure->appointment?->patient;

        return array_merge($item, [
            'physiotherapy_type_id' => $procedure->physiotherapy_type_id,
            'doctor_id' => $procedure->doctor_id,
            'description' => $procedure->description,
            'notes' => $procedure->notes,
            'created_by_name' => $procedure->createdBy
                ? trim("{$procedure->createdBy->name} {$procedure->createdBy->last_name}")
                : null,
            'updated_by_name' => $procedure->updatedBy
                ? trim("{$procedure->updatedBy->name} {$procedure->updatedBy->last_name}")
                : null,
            'created_at' => $procedure->created_at
                ? verta($procedure->created_at)->format('Y-m-d H:i')
                : null,
            'updated_at' => $procedure->updated_at
                ? verta($procedure->updated_at)->format('Y-m-d H:i')
                : null,
            'appointment' => $procedure->appointment ? [
                'id' => $procedure->appointment->id,
                'date' => $procedure->appointment->date
                    ? verta($procedure->appointment->date)->format('Y-m-d')
                    : null,
                'patient_name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')) ?: null,
            ] : null,
            'reviews' => $procedure->reviews->map(fn ($review) => $this->transformReview($review))->values()->all(),
        ]);
    }

    protected function transformReview($review): array
    {
        return [
            'id' => $review->id,
            'description' => $review->description,
            'status' => $review->status,
            'days_count' => $review->days_count,
            'created_by_name' => $review->createdBy
                ? trim("{$review->createdBy->name} {$review->createdBy->last_name}")
                : null,
            'updated_by_name' => $review->updatedBy
                ? trim("{$review->updatedBy->name} {$review->updatedBy->last_name}")
                : null,
            'created_at' => $review->created_at
                ? verta($review->created_at)->format('Y-m-d H:i')
                : null,
            'updated_at' => $review->updated_at
                ? verta($review->updated_at)->format('Y-m-d H:i')
                : null,
        ];
    }

    protected function filterOptions(): array
    {
        return [
            'physiotherapy_types' => PhysiotherapyType::query()
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (PhysiotherapyType $type) => ['id' => $type->id, 'name' => $type->name])
                ->values()
                ->all(),
            'physiotherapists' => $this->physiotherapistDoctorsForFilters()
                ->map(fn (Doctor $doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
                ->values()
                ->all(),
        ];
    }

    protected function listPermissions(User $user, string $mode): array
    {
        $isOwn = $mode === 'own';

        return [
            'view' => $isOwn ? $user->can('show-own-physiotherapy-procedures') : $user->can('show-physiotherapy-procedures'),
            'create' => $user->can('create-physiotherapy-procedures'),
            'edit' => $user->can('edit-physiotherapy-procedures'),
            'delete' => $user->can('delete-physiotherapy-procedures'),
            'updateProgress' => $user->can('edit-physiotherapy-procedures') || $user->can('show-own-physiotherapy-procedures'),
            'viewMyProcedures' => $user->can('show-own-physiotherapy-procedures'),
            'viewAllProcedures' => $user->can('show-physiotherapy-procedures'),
            'viewReports' => $user->can('show-physiotherapy-reports'),
        ];
    }

    protected function showPermissions(User $user, PhysiotherapyProcedure $procedure): array
    {
        $isAssigned = (int) ($procedure->doctor?->user_id ?? 0) === (int) $user->id;

        return [
            'edit' => $user->can('edit-physiotherapy-procedures'),
            'delete' => $user->can('delete-physiotherapy-procedures'),
            'updateProgress' => $user->can('edit-physiotherapy-procedures')
                || ($user->can('show-own-physiotherapy-procedures') && $isAssigned),
            'addReview' => $user->can('edit-physiotherapy-procedures')
                || ($user->can('show-own-physiotherapy-procedures') && $isAssigned),
            'editReview' => $user->can('edit-physiotherapy-procedures'),
            'deleteReview' => $user->can('delete-physiotherapy-procedures'),
        ];
    }

    protected function authorizeProcedureAccess(User $user, PhysiotherapyProcedure $procedure): void
    {
        if ($user->can('show-physiotherapy-procedures')) {
            $this->authorize('view', $procedure);

            return;
        }

        if ($user->can('show-own-physiotherapy-procedures')) {
            abort_unless((int) ($procedure->doctor?->user_id ?? 0) === (int) $user->id, 403);
            abort_unless(
                ! $user->branch_id
                    || (int) ($procedure->appointment?->branch_id ?? 0) === (int) $user->branch_id,
                404
            );

            return;
        }

        abort(403);
    }

    protected function canUpdateProgress(User $user, PhysiotherapyProcedure $procedure): bool
    {
        if ($user->can('edit-physiotherapy-procedures')) {
            return true;
        }

        return $user->can('show-own-physiotherapy-procedures')
            && (int) ($procedure->doctor?->user_id ?? 0) === (int) $user->id;
    }

    /**
     * @return Collection<int, Doctor>
     */
    protected function physiotherapistDoctorsForFilters(): Collection
    {
        $roleQuery = Doctor::query()
            ->where('active_status', true)
            ->whereHas('user.roles', function ($role) {
                $role->where('name', 'physiotherapist');
            });

        if (auth()->user()?->branch_id) {
            $roleQuery->where('branch_id', auth()->user()->branch_id);
        }

        $doctors = $roleQuery->orderBy('name')->get();
        if ($doctors->isNotEmpty()) {
            return $doctors;
        }

        $fallback = Doctor::query()->where('active_status', true);
        if (auth()->user()?->branch_id) {
            $fallback->where('branch_id', auth()->user()->branch_id);
        }

        return $fallback->orderBy('name')->get();
    }
}
