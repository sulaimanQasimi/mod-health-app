<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depot_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('depot_transactions', 'transaction_number')) {
                $table->string('transaction_number')->nullable()->after('id');
            }
            if (!Schema::hasColumn('depot_transactions', 'pharmacy_id')) {
                $table->foreignId('pharmacy_id')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('depot_transactions', 'type')) {
                $table->enum('type', ['depot_to_depot', 'depot_to_pharmacy', 'stock_in', 'stock_out', 'adjustment'])
                    ->default('stock_in')
                    ->after('transaction_type');
            }
            if (!Schema::hasColumn('depot_transactions', 'status')) {
                $table->enum('status', ['pending', 'completed', 'cancelled'])
                    ->default('completed')
                    ->after('type');
            }
        });

        DB::table('depot_transactions')
            ->whereNull('transaction_number')
            ->orderBy('id')
            ->select(['id'])
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('depot_transactions')
                        ->where('id', $row->id)
                        ->update(['transaction_number' => 'DTR-LEGACY-' . str_pad((string) $row->id, 8, '0', STR_PAD_LEFT)]);
                }
            });

        $this->addIndexIfMissing('depot_transactions', ['transaction_number'], 'depot_transactions_transaction_number_unique', true);
        $this->addIndexIfMissing('depot_transactions', ['depot_id'], 'depot_transactions_depot_id_index');
        $this->addIndexIfMissing('depot_transactions', ['from_depot_id'], 'depot_transactions_from_depot_id_index');
        $this->addIndexIfMissing('depot_transactions', ['to_depot_id'], 'depot_transactions_to_depot_id_index');
        $this->addIndexIfMissing('depot_transactions', ['pharmacy_id'], 'depot_transactions_pharmacy_id_index');
        $this->addIndexIfMissing('depot_transactions', ['medicine_id'], 'depot_transactions_medicine_id_index');
        $this->addIndexIfMissing('depot_transactions', ['tool_id'], 'depot_transactions_tool_id_index');
        $this->addIndexIfMissing('depot_transactions', ['type'], 'depot_transactions_type_index');
        $this->addIndexIfMissing('depot_transactions', ['status'], 'depot_transactions_status_index');
        $this->addIndexIfMissing('depot_transactions', ['created_by'], 'depot_transactions_created_by_index');
        $this->addIndexIfMissing('depot_transactions', ['transaction_date'], 'depot_transactions_transaction_date_index');
        $this->addIndexIfMissing('depot_transactions', ['from_depot_id', 'medicine_id', 'status'], 'depot_tx_source_item_status_idx');
        $this->addIndexIfMissing('depot_transactions', ['to_depot_id', 'medicine_id', 'status'], 'depot_tx_dest_item_status_idx');

        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_depot_id_foreign', 'depot_id', 'depots', false);
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_user_id_foreign', 'user_id', 'users');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_pharmacy_id_foreign', 'pharmacy_id', 'pharmacies');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_medicine_type_id_foreign', 'medicine_type_id', 'medicine_types');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_medicine_id_foreign', 'medicine_id', 'medicines');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_tool_id_foreign', 'tool_id', 'tools');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_unit_id_foreign', 'unit_id', 'units');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_from_depot_id_foreign', 'from_depot_id', 'depots');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_to_depot_id_foreign', 'to_depot_id', 'depots');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_created_by_foreign', 'created_by', 'users');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_updated_by_foreign', 'updated_by', 'users');
        $this->addForeignIfMissing('depot_transactions', 'depot_transactions_deleted_by_foreign', 'deleted_by', 'users');
    }

    public function down(): void
    {
        // Kept intentionally conservative because fresh installs now create these
        // columns in the base depot transaction migration.
    }

    private function addIndexIfMissing(string $table, array $columns, string $name, bool $unique = false): void
    {
        if ($this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($columns, $name, $unique) {
            $unique ? $table->unique($columns, $name) : $table->index($columns, $name);
        });
    }

    private function addForeignIfMissing(string $table, string $name, string $column, string $referencesTable, bool $nullOnDelete = true): void
    {
        if (!Schema::hasColumn($table, $column) || $this->foreignKeyExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($name, $column, $referencesTable, $nullOnDelete) {
            $foreign = $table->foreign($column, $name)->references('id')->on($referencesTable);

            if ($nullOnDelete) {
                $foreign->nullOnDelete();
            }
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $name)
            ->exists();
    }

    private function foreignKeyExists(string $table, string $name): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $name)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
