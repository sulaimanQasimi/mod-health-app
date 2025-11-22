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
        // Drop existing foreign key if it exists
        try {
            Schema::table('patient_test_registrations', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, try to drop by finding the constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'patient_test_registrations' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE patient_test_registrations DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Set doctor_id to null for patient_test_registrations where the doctor doesn't exist in doctors table
        DB::statement("
            UPDATE patient_test_registrations ptr
            LEFT JOIN doctors d ON ptr.doctor_id = d.id
            SET ptr.doctor_id = NULL 
            WHERE ptr.doctor_id IS NOT NULL 
            AND d.id IS NULL
        ");
        
        // Add foreign key constraint pointing to doctors table
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        try {
            Schema::table('patient_test_registrations', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, try to drop by finding the constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'patient_test_registrations' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE patient_test_registrations DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
    }
};
