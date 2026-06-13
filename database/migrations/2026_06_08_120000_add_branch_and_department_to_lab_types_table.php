<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_types', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_types', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('category_id');
                $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            }

            if (! Schema::hasColumn('lab_types', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
                $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            }
        });

        $defaultBranchId = DB::table('branches')->orderBy('id')->value('id');
        if ($defaultBranchId) {
            DB::table('lab_types')
                ->whereNull('branch_id')
                ->update(['branch_id' => $defaultBranchId]);
        }

        Schema::table('lab_types', function (Blueprint $table) {
            if ($this->indexExists('lab_types', 'lab_types_name_unique')) {
                $table->dropUnique(['name']);
            }
        });

        Schema::table('lab_types', function (Blueprint $table) {
            if (! $this->indexExists('lab_types', 'lab_types_branch_id_name_unique')) {
                $table->unique(['branch_id', 'name']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_types', function (Blueprint $table) {
            if ($this->indexExists('lab_types', 'lab_types_branch_id_name_unique')) {
                $table->dropUnique(['branch_id', 'name']);
            }
        });

        Schema::table('lab_types', function (Blueprint $table) {
            if (! $this->indexExists('lab_types', 'lab_types_name_unique')) {
                $table->unique('name');
            }
        });

        Schema::table('lab_types', function (Blueprint $table) {
            if (Schema::hasColumn('lab_types', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }

            if (Schema::hasColumn('lab_types', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $connection = Schema::getConnection();
        $driver = $connection->getDriverName();

        if ($driver === 'sqlite') {
            $indexes = $connection->select("PRAGMA index_list('{$table}')");

            return collect($indexes)->contains(fn ($index) => $index->name === $indexName);
        }

        $database = $connection->getDatabaseName();

        return (bool) $connection->selectOne(
            'SELECT COUNT(*) AS aggregate FROM information_schema.statistics WHERE table_schema = ? AND table_name = ? AND index_name = ?',
            [$database, $table, $indexName],
        )?->aggregate;
    }
};
