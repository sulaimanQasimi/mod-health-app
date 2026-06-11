<?php

namespace App\Http\Controllers\V1\Concerns;

use App\Models\Anesthesia;
use Illuminate\Support\Collection;

trait FormatsOperationSectionItems
{
    /**
     * @param  Collection<int, Anesthesia>  $items
     * @return list<array<string, mixed>>
     */
    protected function formatOperationSectionItems(Collection $items): array
    {
        return $items
            ->map(fn (Anesthesia $item) => [
                'id' => $item->id,
                'operation_type' => $item->operationType?->name,
                'patient_name' => $item->patient?->name,
                'date' => $item->date ? verta($item->date)->format('Y-m-d') : null,
                'is_operation_approved' => (bool) $item->is_operation_approved,
                'is_operation_done' => (bool) $item->is_operation_done,
                'is_reserved' => (bool) $item->is_reserved,
                'urls' => [
                    'show' => route('react.operations.show', $item),
                ],
            ])
            ->values()
            ->all();
    }
}
