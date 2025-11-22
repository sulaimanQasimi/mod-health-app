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
        // Handle advice table
        $adviceForeignKey = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'advice'
            AND COLUMN_NAME = 'doctor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        if (!empty($adviceForeignKey)) {
            DB::statement("ALTER TABLE `advice` DROP FOREIGN KEY `{$adviceForeignKey[0]->CONSTRAINT_NAME}`");
        }
        
        Schema::table('advice', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });
        
        // Set doctor_id to null for advice where the doctor doesn't exist in doctors table
        DB::statement("
            UPDATE advice a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            SET a.doctor_id = NULL 
            WHERE a.doctor_id IS NOT NULL 
            AND d.id IS NULL
        ");
        
        Schema::table('advice', function (Blueprint $table) {
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
            Schema::table('advice', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Foreign key doesn't exist, try to drop by finding the constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'advice' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE advice DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Set doctor_id to null for advice where the doctor doesn't exist in users table
        DB::statement("
            UPDATE advice a
            LEFT JOIN users u ON a.doctor_id = u.id
            SET a.doctor_id = NULL 
            WHERE a.doctor_id IS NOT NULL 
            AND u.id IS NULL
        ");
        
        Schema::table('advice', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
