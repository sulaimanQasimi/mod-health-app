<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActivityLogPartitionService
{
    public const TABLE = 'activity_log';

    public function supportsPartitioning(): bool
    {
        return Schema::getConnection()->getDriverName() === 'mysql'
            && Schema::hasTable(self::TABLE);
    }

    public function isPartitioned(): bool
    {
        if (! $this->supportsPartitioning()) {
            return false;
        }

        $row = DB::selectOne(
            'SELECT COUNT(*) AS c
             FROM information_schema.PARTITIONS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND PARTITION_NAME IS NOT NULL',
            [self::TABLE]
        );

        return (int) ($row->c ?? 0) > 0;
    }

    /**
     * @return list<string>
     */
    public function existingPartitionNames(): array
    {
        $rows = DB::select(
            'SELECT PARTITION_NAME AS name
             FROM information_schema.PARTITIONS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND PARTITION_NAME IS NOT NULL
             ORDER BY PARTITION_ORDINAL_POSITION',
            [self::TABLE]
        );

        return collect($rows)->pluck('name')->filter()->values()->all();
    }

    /**
     * Ensure monthly partitions exist from $from through $aheadMonths ahead of now,
     * always keeping a catch-all pmax partition.
     *
     * @return array{added: list<string>, dropped: list<string>}
     */
    public function maintain(int $retentionDays, int $aheadMonths = 3): array
    {
        if (! $this->supportsPartitioning()) {
            return ['added' => [], 'dropped' => []];
        }

        if (! $this->isPartitioned()) {
            return ['added' => [], 'dropped' => []];
        }

        $added = $this->ensureFuturePartitions($aheadMonths);
        $dropped = $this->dropExpiredPartitions($retentionDays);

        return ['added' => $added, 'dropped' => $dropped];
    }

    /**
     * Build PARTITION BY RANGE COLUMNS(created_at) clause fragments.
     *
     * @return list<string> e.g. ["PARTITION p202601 VALUES LESS THAN ('2026-02-01')", ...]
     */
    public function buildPartitionDefinitions(CarbonInterface $from, CarbonInterface $untilExclusive): array
    {
        $definitions = [];
        $cursor = $from->copy()->startOfMonth();
        $end = $untilExclusive->copy()->startOfMonth();

        while ($cursor->lt($end)) {
            $next = $cursor->copy()->addMonth();
            $definitions[] = sprintf(
                "PARTITION %s VALUES LESS THAN ('%s')",
                $this->partitionName($cursor),
                $next->format('Y-m-d')
            );
            $cursor = $next;
        }

        $definitions[] = 'PARTITION pmax VALUES LESS THAN (MAXVALUE)';

        return $definitions;
    }

    public function partitionName(CarbonInterface $month): string
    {
        return 'p'.$month->format('Ym');
    }

    /**
     * @return list<string>
     */
    public function ensureFuturePartitions(int $aheadMonths = 3): array
    {
        $existing = $this->existingPartitionNames();
        $added = [];

        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->addMonths($aheadMonths)->startOfMonth();

        // Also ensure current month exists if somehow missing (before pmax).
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $name = $this->partitionName($cursor);

            if (! in_array($name, $existing, true) && in_array('pmax', $existing, true)) {
                $next = $cursor->copy()->addMonth()->format('Y-m-d');
                DB::statement(sprintf(
                    'ALTER TABLE `%s` REORGANIZE PARTITION `pmax` INTO (
                        PARTITION `%s` VALUES LESS THAN (\'%s\'),
                        PARTITION `pmax` VALUES LESS THAN (MAXVALUE)
                    )',
                    self::TABLE,
                    $name,
                    $next
                ));
                $added[] = $name;
                $existing[] = $name;
            }

            $cursor->addMonth();
        }

        return $added;
    }

    /**
     * Drop monthly partitions whose upper bound is on or before the retention cutoff month start.
     *
     * @return list<string>
     */
    public function dropExpiredPartitions(int $retentionDays): array
    {
        $cutoff = Carbon::now()->subDays(max(1, $retentionDays))->startOfMonth();
        $existing = $this->existingPartitionNames();
        $dropped = [];

        foreach ($existing as $name) {
            if ($name === 'pmax' || ! preg_match('/^p(\d{6})$/', $name, $m)) {
                continue;
            }

            $month = Carbon::createFromFormat('Ym', $m[1])->startOfMonth();
            // Partition p202601 holds rows with created_at < 2026-02-01.
            // Safe to drop when the next month start is <= cutoff.
            $upperBound = $month->copy()->addMonth();

            if ($upperBound->lte($cutoff)) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` DROP PARTITION `%s`',
                    self::TABLE,
                    $name
                ));
                $dropped[] = $name;
            }
        }

        return $dropped;
    }
}
