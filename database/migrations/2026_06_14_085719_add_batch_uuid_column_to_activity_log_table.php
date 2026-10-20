<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kept for package parity; batch_uuid is already created in the base migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        $table = config('activitylog.table_name');

        if (! Schema::connection(config('activitylog.database_connection'))->hasTable($table)) {
            return;
        }

        if (! Schema::connection(config('activitylog.database_connection'))->hasColumn($table, 'batch_uuid')) {
            Schema::connection(config('activitylog.database_connection'))->table($table, function (Blueprint $blueprint) {
                $blueprint->uuid('batch_uuid')->nullable()->after('properties');
            });
        }
    }

    public function down(): void
    {
        //
    }
};
