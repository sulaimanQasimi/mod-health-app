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
        // Drop old foreign key if it exists (using try-catch to handle missing keys)
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, try to drop by finding the constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'appointments' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE appointments DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }

        // Re-add the foreign key constraint pointing to doctors table
        Schema::table('appointments', function (Blueprint $table) {
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
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, try to drop by finding the constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'appointments' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE appointments DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Re-add the foreign key constraint pointing back to users table
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
