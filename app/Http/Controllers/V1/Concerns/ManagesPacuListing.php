<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\PACU;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesPacuListing
{
    protected function authorizePacuMenu(): void
    {
        abort_unless(request()->user()?->can('show-pacu-menu'), 403);
    }

    protected function pacuBranchId(): ?int
    {
        $branchId = request()->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    /**
     * @param  Builder<PACU>  $query
     */
    protected function applyPacuPatientFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where('name', 'like', '%'.$term.'%')
                    ->orWhere('father_name', 'like', '%'.$term.'%')
                    ->orWhere('id_card', 'like', '%'.$term.'%')
                    ->orWhere('last_name', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('patient_name')) {
            $name = $request->patient_name;
            $query->whereHas('patient', function ($q) use ($name) {
                $q->where('name', 'like', '%'.$name.'%')
                    ->orWhere('last_name', 'like', '%'.$name.'%');
            });
        }

        if ($request->filled('card_number')) {
            $card = $request->card_number;
            $query->whereHas('patient', function ($q) use ($card) {
                $q->where('id_card', 'like', '%'.$card.'%');
            });
        }

        if ($request->filled('father_name')) {
            $fatherName = $request->father_name;
            $query->whereHas('patient', function ($q) use ($fatherName) {
                $q->where('father_name', 'like', '%'.$fatherName.'%');
            });
        }
    }

    /**
     * @param  Builder<PACU>  $query
     */
    protected function applyPacuDateRangeFilter(
        Builder $query,
        Request $request,
        string $column = 'created_at',
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

    protected function formatPacuDate(\Illuminate\Support\Carbon|string|null $date): ?string
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
    protected function transformPacuListItem(PACU $pacu, ?int $rowNumber = null): array
    {
        return [
            'id' => $pacu->id,
            'row_number' => $rowNumber,
            'patient_id_card' => $pacu->patient?->id_card,
            'patient_name' => $pacu->patient?->name,
            'father_name' => $pacu->patient?->father_name,
            'description' => $pacu->description,
            'status' => $pacu->status,
            'created_at' => $this->formatPacuDate($pacu->created_at),
            'urls' => [
                'show' => route('react.pacus.show', $pacu),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function pacuListFilterKeys(): array
    {
        return ['search', 'patient_name', 'card_number', 'father_name', 'per_page'];
    }

    /**
     * @return array<string, string>
     */
    protected function pacuListUrls(): array
    {
        return [
            'new' => route('react.pacus.index'),
            'completed' => route('react.pacus.completed'),
            'report' => route('react.pacus.report'),
        ];
    }

    protected function backUrlForPacuStatus(string $status): string
    {
        return $status === 'completed'
            ? route('react.pacus.completed')
            : route('react.pacus.index');
    }
}
