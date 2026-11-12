<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('blood_banks', 'feature')) {
            return;
        }

        DB::statement("ALTER TABLE blood_banks MODIFY COLUMN feature ENUM('request', 'phlebotomy', 'blood_typing') NOT NULL DEFAULT 'request'");
    }

    public function down(): void
    {
        if (! Schema::hasColumn('blood_banks', 'feature')) {
            return;
        }

        DB::table('blood_banks')
            ->where('feature', 'blood_typing')
            ->update(['feature' => 'request']);

        DB::statement("ALTER TABLE blood_banks MODIFY COLUMN feature ENUM('request', 'phlebotomy') NOT NULL DEFAULT 'request'");
    }
};
