<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Make patient_id nullable since we're using polymorphic relationships
            $table->unsignedBigInteger('patient_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Make patient_id not nullable again
            $table->unsignedBigInteger('patient_id')->nullable(false)->change();
        });
    }
};