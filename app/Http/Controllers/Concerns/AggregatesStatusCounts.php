<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait AggregatesStatusCounts
{
    /**
     * Count rows by status in one SQL GROUP BY instead of hydrating all rows.
     *
     * @param  list<string>  $statuses
     * @return array<string, int>
     */
    protected function statusCounts(Builder $query, array $statuses = [
        'pending',
        'in_progress',
        'completed',
        'cancelled',
    ]): array
    {
        $rows = (clone $query)
            ->reorder()
            ->toBase()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = ['total' => (int) $rows->sum()];

        foreach ($statuses as $status) {
            $stats[$status] = (int) ($rows[$status] ?? 0);
        }

        return $stats;
    }
}
