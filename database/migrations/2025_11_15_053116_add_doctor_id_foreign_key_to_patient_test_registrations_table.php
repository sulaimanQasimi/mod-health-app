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
            // Drop existing foreign key if it exists (Laravel will handle if it doesn't exist)
            // We use a raw query to check and drop if needed
            $foreignKey = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'patient_test_registrations'
                AND COLUMN_NAME = 'doctor_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            
            if (!empty($foreignKey)) {
                $table->dropForeign([$foreignKey[0]->CONSTRAINT_NAME]);
            }
            
            // Add foreign key constraint pointing to doctors table
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
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop the foreign key constraint
            $foreignKey = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'patient_test_registrations'
                AND COLUMN_NAME = 'doctor_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            
            if (!empty($foreignKey)) {
                $table->dropForeign([$foreignKey[0]->CONSTRAINT_NAME]);
            }
        });
    }
};
