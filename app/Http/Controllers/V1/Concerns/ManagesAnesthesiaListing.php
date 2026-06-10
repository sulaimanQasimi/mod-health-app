<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Anesthesia;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesAnesthesiaListing
{
    protected function authorizeAnesthesiaMenu(): void
    {
        abort_unless(request()->user()?->can('show-anesthesias-menu'), 403);
    }

    protected function anesthesiaBranchId(): ?int
    {
        $branchId = request()->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    /**
     * @param  Builder<Anesthesia>  $query
     */
    protected function applyAnesthesiaListFilters(Builder $query, Request $request): void
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
                    })
                    ->orWhere('plan', 'like', "%{$search}%");
            });
        }

        if ($request->filled('operation_type_id')) {
            $query->where('operation_type_id', (int) $request->operation_type_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas(
                'appointment',
                fn ($q) => $q->where('department_id', (int) $request->department_id)
            );
        }

        if ($request->filled('anesthesia_type')) {
            $query->where('anesthesia_type', $request->anesthesia_type);
        }

        $this->applyAnesthesiaDateRangeFilter($query, $request, 'date');
    }

    /**
     * @param  Builder<Anesthesia>  $query
     */
    protected function applyAnesthesiaDateRangeFilter(
        Builder $query,
        Request $request,
        string $column = 'date',
        string $fromKey = 'date_from',
        string $toKey = 'date_to',
    ): void {
        if ($request->filled($fromKey)) {
            try {
                $query->whereDate($column, '>=', Verta::parse($request->input($fromKey))->datetime());
            } catch (\Throwable) {
            }
        }

        if ($request->filled($toKey)) {
            try {
                $query->whereDate($column, '<=', Verta::parse($request->input($toKey))->datetime());
            } catch (\Throwable) {
            }
        }
    }

    protected function formatAnesthesiaDate(\Illuminate\Support\Carbon|string|null $date): ?string
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

    protected function anesthesiaTypeLabel(?string $type): ?string
    {
        return match ($type) {
            'local' => localize('global.local'),
            'spinal' => localize('global.spinal'),
            'general' => localize('global.general'),
            default => $type,
        };
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformAnesthesiaListItem(Anesthesia $anesthesia, ?int $rowNumber = null): array
    {
        return [
            'id' => $anesthesia->id,
            'row_number' => $rowNumber,
            'patient_id_card' => $anesthesia->patient?->id_card,
            'patient_name' => $anesthesia->patient?->name,
            'father_name' => $anesthesia->patient?->father_name,
            'operation_type_name' => $anesthesia->operationType?->name,
            'surgion_name' => $anesthesia->surgion?->name,
            'anesthesia_type' => $anesthesia->anesthesia_type,
            'date' => $this->formatAnesthesiaDate($anesthesia->date),
            'time' => $anesthesia->time,
            'status' => $anesthesia->status,
            'urls' => [
                'show' => route('react.anesthesias.show', $anesthesia),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function anesthesiaListFilterKeys(): array
    {
        return [
            'search',
            'operation_type_id',
            'department_id',
            'anesthesia_type',
            'date_from',
            'date_to',
            'per_page',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function anesthesiaListUrls(): array
    {
        return [
            'new' => route('react.anesthesias.new'),
            'approved' => route('react.anesthesias.approved'),
            'rejected' => route('react.anesthesias.rejected'),
            'report' => route('react.anesthesias.report'),
        ];
    }

    protected function backUrlForAnesthesiaStatus(string $status): string
    {
        return match ($status) {
            'approved' => route('react.anesthesias.approved'),
            'rejected' => route('react.anesthesias.rejected'),
            default => route('react.anesthesias.new'),
        };
    }
}
