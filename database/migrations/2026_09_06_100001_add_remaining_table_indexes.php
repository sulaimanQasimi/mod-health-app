<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Finish leftover indexes that Schema::hasTable() skipped (legacy/corrupt
 * tables still present in MySQL) plus wirechat_groups.conversation_id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexes('wirechat_groups', [
            ['columns' => ['conversation_id'], 'name' => 'wirechat_groups_conversation_id_index'],
        ]);

        $this->addIndexes('nurses', [
            ['columns' => ['phone'], 'name' => 'nurses_phone_index'],
            ['columns' => ['email'], 'name' => 'nurses_email_index'],
        ]);

        $this->addIndexes('pharmacies', [
            ['columns' => ['phone'], 'name' => 'pharmacies_phone_index'],
        ]);

        // Legacy tables that exist in MySQL but may fail Doctrine hasTable checks.
        $this->addIndexesRaw('outcome', [
            ['columns' => ['pharmacy_id'], 'name' => 'outcome_pharmacy_id_index'],
            ['columns' => ['medicine_id'], 'name' => 'outcome_medicine_id_index'],
        ]);

        $this->addIndexesRaw('prescription_stocks', [
            ['columns' => ['medicine_id'], 'name' => 'prescription_stocks_medicine_id_index'],
            ['columns' => ['branch_id'], 'name' => 'prescription_stocks_branch_id_index'],
        ]);
    }

    public function down(): void
    {
        foreach ([
            'wirechat_groups' => ['wirechat_groups_conversation_id_index'],
            'nurses' => ['nurses_phone_index', 'nurses_email_index'],
            'pharmacies' => ['pharmacies_phone_index'],
            'outcome' => ['outcome_pharmacy_id_index', 'outcome_medicine_id_index'],
            'prescription_stocks' => [
                'prescription_stocks_medicine_id_index',
                'prescription_stocks_branch_id_index',
            ],
        ] as $table => $indexes) {
            if (! $this->tableExists($table)) {
                continue;
            }

            foreach ($indexes as $index) {
                if ($this->indexExists($table, $index)) {
                    DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
                }
            }
        }
    }

    /**
     * @param  array<int, array{columns: array<int, string>, name: string}>  $indexes
     */
    private function addIndexes(string $table, array $indexes): void
    {
        if (! $this->tableExists($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($table, $indexes) {
            foreach ($indexes as $index) {
                foreach ($index['columns'] as $column) {
                    if (! $this->columnExists($table, $column) || ! $this->columnIsIndexable($table, $column)) {
                        continue 2;
                    }
                }

                if ($this->indexExists($table, $index['name'])) {
                    continue;
                }

                $blueprint->index($index['columns'], $index['name']);
            }
        });
    }

    /**
     * @param  array<int, array{columns: array<int, string>, name: string}>  $indexes
     */
    private function addIndexesRaw(string $table, array $indexes): void
    {
        if (! $this->tableExists($table)) {
            return;
        }

        foreach ($indexes as $index) {
            foreach ($index['columns'] as $column) {
                if (! $this->columnExists($table, $column) || ! $this->columnIsIndexable($table, $column)) {
                    continue 2;
                }
            }

            if ($this->indexExists($table, $index['name'])) {
                continue;
            }

            $cols = implode('`, `', $index['columns']);
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$index['name']}` (`{$cols}`)");
        }
    }

    private function tableExists(string $table): bool
    {
        if (! $this->isBaseTable($table)) {
            return false;
        }

        if (Schema::hasTable($table)) {
            return true;
        }

        $tables = collect(DB::select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->all();

        return in_array($table, $tables, true);
    }

    private function isBaseTable(string $table): bool
    {
        try {
            $result = DB::select(
                'SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
                [$table]
            );

            return isset($result[0]) && strtoupper((string) $result[0]->TABLE_TYPE) === 'BASE TABLE';
        } catch (Throwable) {
            return Schema::hasTable($table);
        }
    }

    private function columnExists(string $table, string $column): bool
    {
        try {
            $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);

            return count($result) > 0;
        } catch (Throwable) {
            return false;
        }
    }

    private function columnIsIndexable(string $table, string $column): bool
    {
        $result = DB::select("SHOW COLUMNS FROM `{$table}` WHERE Field = ?", [$column]);
        if ($result === []) {
            return false;
        }

        $type = strtolower((string) ($result[0]->Type ?? ''));

        if (str_contains($type, 'text') || str_contains($type, 'blob') || str_contains($type, 'varbinary')) {
            return false;
        }

        // Empty / zero-length types cannot be indexed.
        if (preg_match('/\((\d+)\)/', $type, $m) && (int) $m[1] === 0) {
            return false;
        }

        return true;
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $result = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

            return count($result) > 0;
        } catch (Throwable) {
            return false;
        }
    }
};
