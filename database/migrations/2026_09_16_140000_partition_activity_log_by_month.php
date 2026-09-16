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
 * MySQL does not allow FULLTEXT indexes on partitioned tables, so the description
 * fulltext index is dropped before partitioning.
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

        // Partitioned InnoDB tables cannot keep FULLTEXT indexes.
        if ($this->indexExists('activity_log_description_fulltext')) {
            DB::statement('ALTER TABLE `activity_log` DROP INDEX `activity_log_description_fulltext`');
        }

        // Partition key cannot be NULL. TIMESTAMP is not valid for RANGE/TO_DAYS;
        // DATETIME is required for monthly partitioning.
        DB::statement('UPDATE `activity_log` SET `created_at` = COALESCE(`created_at`, `updated_at`, NOW()) WHERE `created_at` IS NULL');
        DB::statement('ALTER TABLE `activity_log` MODIFY `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');

        // AUTO_INCREMENT column must remain a key; composite PK satisfies that.
        if (! $this->hasCompositePrimaryKey()) {
            DB::statement('ALTER TABLE `activity_log` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`, `created_at`)');
        }

        $from = $this->resolveStartMonth();
        $until = Carbon::now()->addMonths(3)->startOfMonth();

        if ($until->lte($from)) {
            $until = $from->copy()->addMonths(3);
        }

        $definitions = $service->buildPartitionDefinitions($from, $until);

        DB::statement(sprintf(
            'ALTER TABLE `activity_log` PARTITION BY RANGE (TO_DAYS(`created_at`)) (%s)',
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

        if ($service->isPartitioned()) {
            DB::statement('ALTER TABLE `activity_log` REMOVE PARTITIONING');
        }

        if ($this->hasCompositePrimaryKey()) {
            DB::statement('ALTER TABLE `activity_log` DROP PRIMARY KEY, ADD PRIMARY KEY (`id`)');
        }

        DB::statement('ALTER TABLE `activity_log` MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL');

        if (! $this->indexExists('activity_log_description_fulltext')) {
            DB::statement('ALTER TABLE `activity_log` ADD FULLTEXT `activity_log_description_fulltext` (`description`)');
        }
    }

    private function resolveStartMonth(): Carbon
    {
        $min = DB::table('activity_log')->min('created_at');

        if ($min) {
            return Carbon::parse($min)->startOfMonth();
        }

        return Carbon::now()->subMonths(3)->startOfMonth();
    }

    private function hasCompositePrimaryKey(): bool
    {
        $rows = DB::select(
            'SHOW INDEX FROM `activity_log` WHERE Key_name = ?',
            ['PRIMARY']
        );

        $columns = collect($rows)
            ->sortBy('Seq_in_index')
            ->pluck('Column_name')
            ->values()
            ->all();

        return $columns === ['id', 'created_at'];
    }

    private function indexExists(string $index): bool
    {
        $result = DB::select(
            'SHOW INDEX FROM `activity_log` WHERE Key_name = ?',
            [$index]
        );

        return count($result) > 0;
    }
};
