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
            // Drop the existing foreign key constraint and column
            $table->dropForeign(['lab_test_id']);
            $table->dropColumn('lab_test_id');
            
            // Add the new lab_type_id column
            $table->foreignId('lab_type_id')->constrained('lab_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop the new foreign key constraint and column
            $table->dropForeign(['lab_type_id']);
            $table->dropColumn('lab_type_id');
            
            // Restore the original lab_test_id column
            $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
        });
    }
};
