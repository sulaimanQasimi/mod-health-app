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
            // Check if lab_test_id column exists and drop it if it does
            if (Schema::hasColumn('patient_test_registrations', 'lab_test_id')) {
                $table->dropForeign(['lab_test_id']);
                $table->dropColumn('lab_test_id');
            }
            
            // Add the new lab_type_id column only if it doesn't exist
            if (!Schema::hasColumn('patient_test_registrations', 'lab_type_id')) {
                $table->foreignId('lab_type_id')->constrained('lab_types')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop the lab_type_id column if it exists
            if (Schema::hasColumn('patient_test_registrations', 'lab_type_id')) {
                $table->dropForeign(['lab_type_id']);
                $table->dropColumn('lab_type_id');
            }
            
            // Restore the original lab_test_id column if it doesn't exist
            if (!Schema::hasColumn('patient_test_registrations', 'lab_test_id')) {
                $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            }
        });
    }
};
