<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Converts morning_time and evening_time from time to string (text) to allow flexible time input.
     * Note: doctrine/dbal may be required for column modification: composer require doctrine/dbal
     */
    public function up(): void
    {
        Schema::table('vital_sign_schedules', function (Blueprint $table) {
            $table->string('morning_time', 255)->nullable()->change();
            $table->string('evening_time', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vital_sign_schedules', function (Blueprint $table) {
            $table->time('morning_time')->nullable()->change();
            $table->time('evening_time')->nullable()->change();
        });
    }
};
