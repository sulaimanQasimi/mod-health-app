<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blood_units') || ! Schema::hasColumn('blood_units', 'collected_at')) {
            return;
        }

        DB::statement('ALTER TABLE `blood_units` MODIFY `collected_at` DATETIME NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_units') || ! Schema::hasColumn('blood_units', 'collected_at')) {
            return;
        }

        DB::statement('ALTER TABLE `blood_units` MODIFY `collected_at` DATE NULL');
    }
};
