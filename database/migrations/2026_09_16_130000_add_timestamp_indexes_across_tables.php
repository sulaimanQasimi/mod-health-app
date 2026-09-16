<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add single-column indexes on created_at / updated_at / deleted_at
 * for every application table that has those columns.
 *
 * Idempotent: skips tables/columns/indexes that already exist.
 */
return new class extends Migration
{
    private const TIMESTAMP_COLUMNS = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /** @var list<string> */
    private const SKIP_TABLES = [
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'sessions',
    ];

    public function up(): void
    {
        foreach ($this->tableNames() as $table) {
            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            $indexes = [];

            foreach (self::TIMESTAMP_COLUMNS as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $indexes[] = [
                    'columns' => [$column],
                    'name' => $this->indexName($table, $column),
                ];
            }

            if ($indexes === []) {
                continue;
            }

            $this->addIndexes($table, $indexes);
        }
    }

    public function down(): void
    {
        foreach ($this->tableNames() as $table) {
            if (in_array($table, self::SKIP_TABLES, true)) {
                continue;
            }

            $names = [];

            foreach (self::TIMESTAMP_COLUMNS as $column) {
                if (! Schema::hasColumn($table, $column)) {
                    continue;
                }

                $names[] = $this->indexName($table, $column);
            }

            if ($names === []) {
                continue;
            }

            $this->dropIndexes($table, $names);
        }
    }

    /**
     * @return list<string>
     */
    private function tableNames(): array
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $database = $connection->getDatabaseName();
            $rows = $connection->select(
                'SELECT TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ?',
                [$database, 'BASE TABLE']
            );

            return collect($rows)->pluck('name')->sort()->values()->all();
        }

        if ($driver === 'pgsql') {
            $rows = $connection->select(
                "SELECT tablename AS name FROM pg_tables WHERE schemaname = 'public'"
            );

            return collect($rows)->pluck('name')->sort()->values()->all();
        }

        if ($driver === 'sqlite') {
            $rows = $connection->select(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'"
            );

            return collect($rows)->pluck('name')->sort()->values()->all();
        }

        return Schema::getTableListing();
    }

    private function indexName(string $table, string $column): string
    {
        $name = "{$table}_{$column}_index";

        // MySQL max identifier length is 64.
        if (strlen($name) <= 64) {
            return $name;
        }

        return substr(hash('sha256', $name), 0, 56).'_idx';
    }

    /**
     * @param  list<array{columns: list<string>, name: string}>  $indexes
     */
    private function addIndexes(string $table, array $indexes): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
            foreach ($indexes as $index) {
                if ($this->indexExists($table, $index['name'])) {
                    continue;
                }

                // Skip when an identically keyed index already exists under another name.
                if ($this->columnAlreadyIndexed($table, $index['columns'][0])) {
                    continue;
                }

                $blueprint->index($index['columns'], $index['name']);
            }
        });
    }

    /**
     * @param  list<string>  $names
     */
    private function dropIndexes(string $table, array $names): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $names) {
            foreach ($names as $name) {
                if ($this->indexExists($table, $name)) {
                    $blueprint->dropIndex($name);
                }
            }
        });
    }

    private function columnAlreadyIndexed(string $table, string $column): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver !== 'mysql') {
            return false;
        }

        $rows = $connection->select(
            'SHOW INDEX FROM '.$connection->getTablePrefix().$table.' WHERE Column_name = ? AND Seq_in_index = 1',
            [$column]
        );

        return count($rows) > 0;
    }

    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'mysql') {
            $result = $connection->select(
                'SHOW INDEX FROM '.$connection->getTablePrefix().$table.' WHERE Key_name = ?',
                [$index]
            );

            return count($result) > 0;
        }

        if ($driver === 'pgsql') {
            $result = $connection->select(
                'SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?',
                [$table, $index]
            );

            return count($result) > 0;
        }

        if ($driver === 'sqlite') {
            $result = $connection->select("PRAGMA index_list('{$table}')");

            foreach ($result as $row) {
                if (($row->name ?? null) === $index) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }
};
