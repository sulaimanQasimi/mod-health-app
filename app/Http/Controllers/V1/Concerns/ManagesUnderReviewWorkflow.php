<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\UnderReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait ManagesUnderReviewWorkflow
{
    /**
     * @return Builder<UnderReview>
     */
    protected function underReviewBaseQuery(): Builder
    {
        return UnderReview::query()
            ->where('branch_id', $this->branchId())
            ->visibleForAuthUserDepartment()
            ->with([
                'patient:id,name,father_name,id_card',
                'room:id,name',
                'bed:id,number',
                'department:id,name',
                'processedBy:id,name,last_name',
            ]);
    }

    /**
     * @param  Builder<UnderReview>  $query
     */
    protected function applyUnderReviewListFilters(Builder $query, Request $request): void
    {
        if ($request->filled('patient_name')) {
            $search = $request->patient_name;
            $query->whereHas('patient', fn ($p) => $p->where('name', 'like', '%'.$search.'%'));
        }

        if ($request->filled('id_card')) {
            $search = $request->id_card;
            $query->whereHas('patient', fn ($p) => $p->where('id_card', 'like', '%'.$search.'%'));
        }

        if ($request->filled('father_name')) {
            $search = $request->father_name;
            $query->whereHas('patient', fn ($p) => $p->where('father_name', 'like', '%'.$search.'%'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $recordId = $this->parseNumericFilter($request->input('record_id'));
        if ($recordId !== null) {
            $query->where('id', $recordId);
        }

        $patientId = $this->parseNumericFilter($request->input('patient_id'));
        if ($patientId !== null) {
            $query->where('patient_id', $patientId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($w) use ($search) {
                $w->whereHas('patient', function ($p) use ($search) {
                    $p->where('name', 'like', '%'.$search.'%')
                        ->orWhere('father_name', 'like', '%'.$search.'%')
                        ->orWhere('id_card', 'like', '%'.$search.'%');
                });
            });
        }
    }

    protected function parseNumericFilter(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function transformUnderReviewListItem(UnderReview $item, $user, bool $includeAccept = false): array
    {
        $processor = $item->processedBy;
        $processedBy = $processor
            ? trim($processor->name.' '.$processor->last_name)
            : null;

        $data = [
            'id' => $item->id,
            'patient_id' => $item->patient_id,
            'patient_id_card' => $item->patient?->id_card,
            'patient_name' => $item->patient?->name,
            'father_name' => $item->patient?->father_name,
            'department_name' => $item->department?->name,
            'room_name' => $item->room?->name,
            'bed_number' => $item->bed?->number,
            'admission_date' => $this->formatDate($item->created_at),
            'is_accepted' => (bool) $item->processed_by,
            'processed_by' => $processedBy,
            'is_discharged' => (bool) $item->is_discharged,
            'urls' => [
                'show' => route('under-reviews.show', $item),
            ],
        ];

        if ($includeAccept) {
            $data['permissions'] = [
                'accept' => $user->can('accept', $item),
            ];
        }

        return $data;
    }

    /**
     * @return array<string, string>
     */
    protected function underReviewWorkflowUrls(): array
    {
        return [
            'index' => route('under-reviews.index'),
            'pending' => route('under-reviews.pending'),
            'myCases' => route('under-reviews.my-cases'),
            'discharged' => route('under-reviews.discharged'),
            'show' => url('/under-reviews'),
            'accept' => url('/under-reviews'),
        ];
    }
}
