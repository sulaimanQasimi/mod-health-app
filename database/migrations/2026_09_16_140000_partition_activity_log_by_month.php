<?php

use App\Services\ActivityLogPartitionService;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Convert activity_log to monthly RANGE COLUMNS(created_at) partitions (MySQL only).
 *
 * Requires composite primary key (id, created_at) per MySQL partitioning rules.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('activity_log')) {
            return;
        }

        $service = app(ActivityLogPartitionService::class);

        if ($service->isPartitioned()) {
            return;
        }

        // Partition key cannot be NULL.
        DB::statement('UPDATE `activity_log` SET `created_at` = COALESCE(`created_at`, `updated_at`, NOW()) WHERE `created_at` IS NULL');
        DB::statement('ALTER TABLE `activity_log` MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP');

        // AUTO_INCREMENT column must remain a key; composite PK satisfies that.
        DB::statement('ALTER TABLE `activity_log` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `created_at`)');

        $from = $this->resolveStartMonth();
        $until = Carbon::now()->addMonths(3)->startOfMonth();

        if ($until->lte($from)) {
            $until = $from->copy()->addMonths(3);
        }

        $definitions = $service->buildPartitionDefinitions($from, $until);

        DB::statement(sprintf(
            'ALTER TABLE `activity_log` PARTITION BY RANGE COLUMNS(`created_at`) (%s)',
            implode(",\n", $definitions)
        ));
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('activity_log')) {
            return;
        }

        $service = app(ActivityLogPartitionService::class);

        if (! $service->isPartitioned()) {
            return;
        }

        DB::statement('ALTER TABLE `activity_log` REMOVE PARTITIONING');
        DB::statement('ALTER TABLE `activity_log` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)');
        DB::statement('ALTER TABLE `activity_log` MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL');
    }

    private function resolveStartMonth(): Carbon
    {
        $min = DB::table('activity_log')->min('created_at');

        if ($min) {
            return Carbon::parse($min)->startOfMonth();
        }

        return Carbon::now()->subMonths(3)->startOfMonth();
    }
};
