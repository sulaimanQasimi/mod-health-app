<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if (! $this->indexExists('activity_log', 'activity_log_created_at_id_index')) {
                $table->index(['created_at', 'id'], 'activity_log_created_at_id_index');
            }

            if (! $this->indexExists('activity_log', 'activity_log_event_created_at_index')) {
                $table->index(['event', 'created_at'], 'activity_log_event_created_at_index');
            }

            if (! $this->indexExists('activity_log', 'activity_log_subject_type_created_at_index')) {
                $table->index(['subject_type', 'created_at'], 'activity_log_subject_type_created_at_index');
            }

            if (! $this->indexExists('activity_log', 'activity_log_log_name_created_at_index')) {
                $table->index(['log_name', 'created_at'], 'activity_log_log_name_created_at_index');
            }
        });

        if (
            Schema::getConnection()->getDriverName() === 'mysql'
            && ! $this->indexExists('activity_log', 'activity_log_description_fulltext')
        ) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->fullText('description', 'activity_log_description_fulltext');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if ($this->indexExists('activity_log', 'activity_log_description_fulltext')) {
                $table->dropFullText('activity_log_description_fulltext');
            }

            foreach ([
                'activity_log_created_at_id_index',
                'activity_log_event_created_at_index',
                'activity_log_subject_type_created_at_index',
                'activity_log_log_name_created_at_index',
            ] as $index) {
                if ($this->indexExists('activity_log', $index)) {
                    $table->dropIndex($index);
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
