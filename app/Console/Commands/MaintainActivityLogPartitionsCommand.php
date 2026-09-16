<?php

namespace App\Console\Commands;

use App\Services\ActivityLogPartitionService;
use Illuminate\Console\Command;

class MaintainActivityLogPartitionsCommand extends Command
{
    protected $signature = 'activitylog:maintain-partitions
                            {--days= : Retention days (defaults to activitylog.clean_after_days)}
                            {--ahead=3 : How many future months to pre-create}';

    protected $description = 'Add future activity_log partitions and drop expired monthly partitions (MySQL).';

    public function handle(ActivityLogPartitionService $partitions): int
    {
        if (! $partitions->supportsPartitioning()) {
            $this->warn('activity_log partitioning is only supported on MySQL.');

            return self::SUCCESS;
        }

        if (! $partitions->isPartitioned()) {
            $this->warn('activity_log is not partitioned yet. Run migrations first.');

            return self::SUCCESS;
        }

        $days = (int) ($this->option('days') ?? config('activitylog.clean_after_days', 90));
        $ahead = max(1, (int) ($this->option('ahead') ?: config('activitylog.partition_ahead_months', 3)));

        $result = $partitions->maintain($days, $ahead);

        foreach ($result['added'] as $name) {
            $this->info("Added partition {$name}");
        }

        foreach ($result['dropped'] as $name) {
            $this->info("Dropped partition {$name}");
        }

        if ($result['added'] === [] && $result['dropped'] === []) {
            $this->comment('No partition changes needed.');
        }

        return self::SUCCESS;
    }
}
