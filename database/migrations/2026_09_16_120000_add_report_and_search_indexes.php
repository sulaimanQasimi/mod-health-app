<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Secondary indexes for report / listing date filters that still caused full scans.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexes('patient_test_registrations', [
            ['columns' => ['registration_date'], 'name' => 'ptr_registration_date_index'],
            ['columns' => ['lab_type_id', 'registration_date'], 'name' => 'ptr_lab_type_registration_date_index'],
            ['columns' => ['branch_id', 'registration_date'], 'name' => 'ptr_branch_registration_date_index'],
            ['columns' => ['patient_id'], 'name' => 'ptr_patient_id_index'],
        ]);

        $this->addIndexes('physiotherapy_procedures', [
            ['columns' => ['start_date'], 'name' => 'physio_start_date_index'],
            ['columns' => ['end_date'], 'name' => 'physio_end_date_index'],
            ['columns' => ['appointment_id', 'start_date'], 'name' => 'physio_appointment_start_date_index'],
        ]);

        $this->addIndexes('appointments', [
            ['columns' => ['created_at'], 'name' => 'appointments_created_at_index'],
            ['columns' => ['branch_id', 'created_at'], 'name' => 'appointments_branch_created_at_index'],
        ]);

        $this->addIndexes('patients', [
            ['columns' => ['name'], 'name' => 'patients_name_index'],
            ['columns' => ['branch_id', 'name'], 'name' => 'patients_branch_name_index'],
        ]);

        // id_card is TEXT — MySQL requires an explicit prefix length.
        $this->addPrefixIndex('patients', 'id_card', 'patients_id_card_index', 191);

        $this->addIndexes('printed_numbers', [
            ['columns' => ['number'], 'name' => 'printed_numbers_number_index'],
            ['columns' => ['patient_id', 'number'], 'name' => 'printed_numbers_patient_number_index'],
        ]);
    }

    public function down(): void
    {
        $this->dropIndexes('patient_test_registrations', [
            'ptr_registration_date_index',
            'ptr_lab_type_registration_date_index',
            'ptr_branch_registration_date_index',
            'ptr_patient_id_index',
        ]);

        $this->dropIndexes('physiotherapy_procedures', [
            'physio_start_date_index',
            'physio_end_date_index',
            'physio_appointment_start_date_index',
        ]);

        $this->dropIndexes('appointments', [
            'appointments_created_at_index',
            'appointments_branch_created_at_index',
        ]);

        $this->dropIndexes('patients', [
            'patients_name_index',
            'patients_id_card_index',
            'patients_branch_name_index',
        ]);

        $this->dropIndexes('printed_numbers', [
            'printed_numbers_number_index',
            'printed_numbers_patient_number_index',
        ]);
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

                $missingColumn = collect($index['columns'])
                    ->first(fn (string $column) => ! Schema::hasColumn($table, $column));

                if ($missingColumn !== null) {
                    continue;
                }

                $blueprint->index($index['columns'], $index['name']);
            }
        });
    }

    private function addPrefixIndex(string $table, string $column, string $indexName, int $length): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        if ($this->indexExists($table, $indexName)) {
            return;
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
                $blueprint->index([$column], $indexName);
            });

            return;
        }

        DB::statement(sprintf(
            'ALTER TABLE `%s` ADD INDEX `%s` (`%s`(%d))',
            $table,
            $indexName,
            $column,
            $length
        ));
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
