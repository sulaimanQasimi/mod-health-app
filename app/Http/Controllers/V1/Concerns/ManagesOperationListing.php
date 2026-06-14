<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Anesthesia;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesOperationListing
{
    protected function authorizeOperationsMenu(): void
    {
        abort_unless(request()->user()?->can('show-operations-menu'), 403);
    }

    protected function operationBranchId(): ?int
    {
        $branchId = request()->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    /**
     * @param  Builder<Anesthesia>  $query
     */
    protected function applyOperationListFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('id_card', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%");
                })
                    ->orWhereHas('operationType', function ($opQuery) use ($search) {
                        $opQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('surgion', function ($surgionQuery) use ($search) {
                        $surgionQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('branch_id') && ! $this->operationBranchId()) {
            $query->where('branch_id', (int) $request->branch_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('operationType', function ($q) use ($request) {
                $q->where('department_id', (int) $request->department_id);
            });
        }

        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', (int) $request->operation_type_id);
        }

        if ($request->filled('surgeon_id')) {
            $query->where('operation_surgion_id', (int) $request->surgeon_id);
        }

        if ($request->filled('date_from')) {
            try {
                $query->whereDate('date', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
            }
        }

        if ($request->filled('date_to')) {
            try {
                $query->whereDate('date', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
            }
        }

        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSort = ['date', 'created_at', 'time'];

        if (! in_array($sortBy, $allowedSort, true)) {
            $sortBy = 'date';
        }

        $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
    }

    /**
     * @return Builder<Anesthesia>
     */
    protected function operationListQuery(string $variant): Builder
    {
        $query = Anesthesia::query()
            ->when($this->operationBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId));

        return match ($variant) {
            'new' => $query
                ->where('status', 'approved')
                ->where('is_referred_to_operation', true)
                ->where('is_operation_approved', 0)
                ->where('is_reserved', 0),
            'approved' => $query
                ->where('status', 'approved')
                ->where('is_operation_approved', 1)
                ->where('is_operation_done', 0)
                ->where('is_reserved', 0),
            'reserved' => $query->reserved(),
            'completed' => $query->where('is_operation_done', 1),
            default => $query,
        };
    }

    /**
     * @return list<string>
     */
    protected function operationEagerLoads(string $variant): array
    {
        $base = ['patient:id,name,father_name,id_card', 'operationType:id,name'];

        return match ($variant) {
            'approved', 'completed' => [...$base, 'scrub_nurse:id,first_name,last_name', 'circulation_nurse:id,first_name,last_name'],
            default => $base,
        };
    }

    protected function formatOperationDate(\Illuminate\Support\Carbon|string|null $date): ?string
    {
        if (! $date) {
            return null;
        }

        if (! $date instanceof \Illuminate\Support\Carbon) {
            try {
                $date = \Illuminate\Support\Carbon::parse($date);
            } catch (\Throwable) {
                return null;
            }
        }

        try {
            return verta($date)->format('Y/n/j');
        } catch (\Throwable) {
            return $date->format('Y-m-d');
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformOperationListItem(Anesthesia $operation, string $variant, ?int $rowNumber = null): array
    {
        $item = [
            'id' => $operation->id,
            'row_number' => $rowNumber,
            'patient_id' => $operation->patient_id,
            'patient_id_card' => $operation->patient?->id_card,
            'patient_name' => $operation->patient?->name,
            'father_name' => $operation->patient?->father_name,
            'operation_type_name' => $operation->operationType?->name,
            'date' => $this->formatOperationDate($operation->date),
            'time' => $operation->time,
            'is_operation_approved' => (bool) $operation->is_operation_approved,
            'is_operation_done' => (bool) $operation->is_operation_done,
            'is_reserved' => (bool) $operation->is_reserved,
            'reserve_reason' => $operation->reserve_reason,
            'status' => $operation->status,
            'urls' => [
                'show' => route('react.operations.show', $operation),
            ],
        ];

        if (in_array($variant, ['approved', 'completed'], true)) {
            $item['scrub_nurse_name'] = $operation->scrub_nurse?->full_name;
            $item['circulation_nurse_name'] = $operation->circulation_nurse?->full_name;
        }

        return $item;
    }

    /**
     * @return list<string>
     */
    protected function operationListFilterKeys(): array
    {
        return [
            'search',
            'branch_id',
            'department_id',
            'operation_type_id',
            'surgeon_id',
            'date_from',
            'date_to',
            'sort_by',
            'sort_order',
            'per_page',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function operationListUrls(): array
    {
        return [
            'new' => route('react.operations.new'),
            'approved' => route('react.operations.approved'),
            'reserved' => route('react.operations.reserved'),
            'completed' => route('react.operations.completed'),
            'report' => route('react.operations.report'),
        ];
    }

    protected function backUrlForOperation(Anesthesia $operation): string
    {
        if ($operation->is_operation_done) {
            return route('react.operations.completed');
        }

        if ($operation->is_reserved) {
            return route('react.operations.reserved');
        }

        if ($operation->is_operation_approved) {
            return route('react.operations.approved');
        }

        return route('react.operations.new');
    }
}
