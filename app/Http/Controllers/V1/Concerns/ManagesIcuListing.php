<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\ICU;
use App\Services\IcuReferralService;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesIcuListing
{
    protected function authorizeIcuMenu(): void
    {
        abort_unless(request()->user()?->can('show-icu-menu'), 403);
    }

    protected function icuBranchId(): ?int
    {
        $branchId = request()->user()?->branch_id;

        return $branchId ? (int) $branchId : null;
    }

    /**
     * @param  Builder<ICU>  $query
     */
    protected function applyIcuPatientFilters(Builder $query, Request $request): void
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
     * @param  Builder<ICU>  $query
     */
    protected function applyIcuDischargeFilter(Builder $query, Request $request): void
    {
        $dischargeFilter = $request->input('discharge_filter', 'in_icu');

        if ($dischargeFilter === 'in_icu') {
            $query->where(function ($q) {
                $q->where('is_discharged', 0)->orWhereNull('is_discharged');
            });
        } elseif ($dischargeFilter === 'discharged') {
            $query->where('is_discharged', 1);
        } elseif ($dischargeFilter === 'recovered') {
            $query->where('is_discharged', 1)->where('discharge_status', 'recovered');
        } elseif ($dischargeFilter === 'died') {
            $query->where('is_discharged', 1)->where('discharge_status', 'died');
        } elseif ($dischargeFilter === 'moved') {
            $query->where('is_discharged', 1)->where('discharge_status', 'moved');
        }
    }

    /**
     * @param  Builder<ICU>  $query
     */
    protected function applyIcuDateRangeFilter(
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

    protected function formatIcuDate(\Illuminate\Support\Carbon|string|null $date): ?string
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
    protected function transformIcuListItem(ICU $icu, ?int $rowNumber = null): array
    {
        $placement = IcuReferralService::placementHospitalization($icu);

        return [
            'id' => $icu->id,
            'row_number' => $rowNumber,
            'patient_id_card' => $icu->patient?->id_card,
            'patient_name' => $icu->patient?->name,
            'father_name' => $icu->patient?->father_name,
            'room_name' => $placement?->room?->name,
            'bed_number' => $placement?->bed?->number,
            'description' => $icu->description,
            'status' => $icu->status,
            'is_discharged' => (bool) $icu->is_discharged,
            'discharge_status' => $icu->discharge_status,
            'created_at' => $this->formatIcuDate($icu->created_at),
            'urls' => [
                'show' => route('icus.show', $icu),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function icuListFilterKeys(bool $includeDischarge = false): array
    {
        $keys = ['search', 'patient_name', 'card_number', 'father_name', 'per_page'];

        if ($includeDischarge) {
            $keys[] = 'discharge_filter';
        }

        return $keys;
    }

    /**
     * @return array<string, string>
     */
    protected function icuListUrls(): array
    {
        return [
            'new' => route('icus.new'),
            'approved' => route('icus.approved'),
            'rejected' => route('icus.rejected'),
            'report' => route('icus.report'),
        ];
    }
}
