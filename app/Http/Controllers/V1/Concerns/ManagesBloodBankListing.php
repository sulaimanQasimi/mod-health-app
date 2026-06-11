<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\BloodBank;
use App\Models\Department;
use App\Support\PersianDateParser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesBloodBankListing
{
    protected function authorizeBloodBankMenu(): void
    {
        abort_unless(request()->user()?->can('show-blood-bank-menu'), 403);
    }

    protected function bloodBankBranchId(): ?int
    {
        $branchId = request()->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    /**
     * @return list<string>
     */
    protected function bloodComponentTypes(): array
    {
        return ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'];
    }

    /**
     * @param  Builder<BloodBank>  $query
     */
    protected function applyBloodRequestListFilters(Builder $query, Request $request): void
    {
        $search = $request->input('q', $request->input('search'));
        if ($search) {
            $term = $search;
            $query->whereHas('patient', function ($p) use ($term) {
                $p->where('name', 'like', '%'.$term.'%')
                    ->orWhere('id_card', 'like', '%'.$term.'%')
                    ->orWhere('father_name', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->department_id);
        }

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('rh')) {
            $query->where('rh', $request->rh);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $this->applyPersianDateFromFilter($query, 'created_at', $request->input('from'));
        $this->applyPersianDateToFilter($query, 'created_at', $request->input('to'));

        $query->orderByDesc('created_at');
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformBloodRequestListItem(BloodBank $bloodBank, ?int $rowNumber = null): array
    {
        return [
            'id' => $bloodBank->id,
            'row_number' => $rowNumber,
            'patient_id_card' => $bloodBank->patient?->id_card,
            'patient_name' => $bloodBank->patient?->name,
            'father_name' => $bloodBank->patient?->father_name,
            'department_name' => $bloodBank->department?->name,
            'group' => $bloodBank->group,
            'rh' => $bloodBank->rh,
            'type' => $bloodBank->type,
            'quantity' => $bloodBank->quantity,
            'status' => $bloodBank->status,
            'created_at' => $bloodBank->created_at ? verta($bloodBank->created_at)->format('Y-m-d') : null,
            'urls' => [
                'show' => route('react.blood-banks.show', $bloodBank),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function bloodRequestListFilterKeys(): array
    {
        return ['q', 'department_id', 'group', 'rh', 'type', 'from', 'to', 'per_page'];
    }

    /**
     * @return array<string, string>
     */
    protected function bloodBankListUrls(): array
    {
        return [
            'dashboard' => route('react.blood-banks.dashboard'),
            'new' => route('react.blood-banks.new'),
            'approved' => route('react.blood-banks.approved'),
            'rejected' => route('react.blood-banks.rejected'),
            'delivered' => route('react.blood-banks.delivered'),
            'inventory' => route('react.blood-banks.inventory'),
            'movements' => route('react.blood-banks.movements'),
            'branchTransfers' => route('react.blood-banks.branch-transfers.index'),
            'report' => route('react.blood-banks.report'),
        ];
    }

    /**
     * @return array{departments: list<array{id: int, name: string}>, bloodGroups: list<string>, bloodComponentTypes: list<string>}
     */
    protected function bloodRequestFilterOptions(): array
    {
        $branchId = $this->bloodBankBranchId();

        return [
            'departments' => Department::query()
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Department $d) => ['id' => $d->id, 'name' => $d->name])
                ->values()
                ->all(),
            'bloodGroups' => ['A', 'B', 'AB', 'O'],
            'bloodComponentTypes' => $this->bloodComponentTypes(),
        ];
    }

    protected function backUrlForBloodRequest(BloodBank $bloodBank): string
    {
        return match ($bloodBank->status) {
            'approved' => route('react.blood-banks.approved'),
            'rejected' => route('react.blood-banks.rejected'),
            'delivered' => route('react.blood-banks.delivered'),
            default => route('react.blood-banks.new'),
        };
    }

    protected function ensureBloodRequestBranch(BloodBank $bloodBank): void
    {
        if ((int) $bloodBank->branch_id !== (int) request()->user()?->branch_id) {
            abort(404);
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function applyPersianDateFromFilter(Builder $query, string $column, ?string $from): void
    {
        if ($from === null || $from === '') {
            return;
        }

        $parsed = PersianDateParser::queryDate($from);
        if ($parsed !== null) {
            $query->whereDate($column, '>=', $parsed);
        }
    }

    /**
     * @param  Builder<\Illuminate\Database\Eloquent\Model>  $query
     */
    protected function applyPersianDateToFilter(Builder $query, string $column, ?string $to): void
    {
        if ($to === null || $to === '') {
            return;
        }

        $parsed = PersianDateParser::queryDate($to);
        if ($parsed !== null) {
            $query->whereDate($column, '<=', $parsed);
        }
    }
}
