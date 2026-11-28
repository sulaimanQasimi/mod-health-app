<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndex('pharmacy_fulfillments', ['pharmacy_id', 'date', 'deleted_at'], 'pf_pharmacy_date_deleted_index');
        $this->addIndex('pharmacy_fulfillments', ['pharmacy_id', 'medicine_id', 'deleted_at'], 'pf_pharmacy_medicine_deleted_index');
        $this->addIndex('prescription_items', ['prescription_id', 'deleted_at'], 'pi_prescription_deleted_index');
        $this->addIndex('prescription_items', ['medicine_id', 'deleted_at'], 'pi_medicine_deleted_index');
    }

    public function down(): void
    {
        $this->dropIndex('pharmacy_fulfillments', 'pf_pharmacy_date_deleted_index');
        $this->dropIndex('pharmacy_fulfillments', 'pf_pharmacy_medicine_deleted_index');
        $this->dropIndex('prescription_items', 'pi_prescription_deleted_index');
        $this->dropIndex('prescription_items', 'pi_medicine_deleted_index');
    }

    private function addIndex(string $table, array $columns, string $name): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $name) {
            $blueprint->index($columns, $name);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (!Schema::hasTable($table) || !$this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($name) {
            $blueprint->dropIndex($name);
        });
    }

    private function indexExists(string $table, string $name): bool
    {
        $database = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT COUNT(1) AS cnt FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $name]
        );

        return ((int) ($row->cnt ?? 0)) > 0;
    }
};
