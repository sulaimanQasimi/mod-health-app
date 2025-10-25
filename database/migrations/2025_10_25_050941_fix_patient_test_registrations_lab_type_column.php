<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Check if lab_test_id column exists and drop it
            if (Schema::hasColumn('patient_test_registrations', 'lab_test_id')) {
                $table->dropForeign(['lab_test_id']);
                $table->dropColumn('lab_test_id');
            }
            
            // Check if lab_type_id column exists, if not add it
            if (!Schema::hasColumn('patient_test_registrations', 'lab_type_id')) {
                $table->foreignId('lab_type_id')->constrained('lab_types')->onDelete('cascade');
            } else {
                // If it exists, make sure it has the proper foreign key constraint
                // First check if the foreign key exists
                $foreignKeys = DB::select("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'patient_test_registrations' 
                    AND COLUMN_NAME = 'lab_type_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if (empty($foreignKeys)) {
                    $table->foreign('lab_type_id')->references('id')->on('lab_types')->onDelete('cascade');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop the lab_type_id foreign key and column
            if (Schema::hasColumn('patient_test_registrations', 'lab_type_id')) {
                $table->dropForeign(['lab_type_id']);
                $table->dropColumn('lab_type_id');
            }
            
            // Restore lab_test_id (this will fail if lab_tests table doesn't exist)
            try {
                $table->foreignId('lab_test_id')->constrained('lab_tests')->onDelete('cascade');
            } catch (\Exception $e) {
                // If lab_tests table doesn't exist, just add the column without foreign key
                $table->unsignedBigInteger('lab_test_id');
            }
        });
    }
};