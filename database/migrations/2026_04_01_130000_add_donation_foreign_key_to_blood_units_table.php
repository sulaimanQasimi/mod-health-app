<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blood_units') || ! Schema::hasTable('blood_donations') || ! Schema::hasColumn('blood_units', 'donation_id')) {
            return;
        }

        $database = DB::getDatabaseName();
        $constraintExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'blood_units')
            ->where('COLUMN_NAME', 'donation_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if ($constraintExists) {
            return;
        }

        Schema::table('blood_units', function (Blueprint $table) {
            $table->foreign('donation_id')->references('id')->on('blood_donations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_units') || ! Schema::hasColumn('blood_units', 'donation_id')) {
            return;
        }

        $database = DB::getDatabaseName();
        $constraintExists = DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', 'blood_units')
            ->where('COLUMN_NAME', 'donation_id')
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();

        if (! $constraintExists) {
            return;
        }

        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropForeign(['donation_id']);
        });
    }
};
