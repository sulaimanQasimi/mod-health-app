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
        // Handle prescriptions table
        $prescriptionsForeignKey = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'prescriptions'
            AND COLUMN_NAME = 'doctor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        if (!empty($prescriptionsForeignKey)) {
            DB::statement("ALTER TABLE `prescriptions` DROP FOREIGN KEY `{$prescriptionsForeignKey[0]->CONSTRAINT_NAME}`");
        }
        
        Schema::table('prescriptions', function (Blueprint $table) {
            // Make doctor_id nullable if it's not already
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });
        
        // Set doctor_id to null for prescriptions where the doctor doesn't exist in doctors table
        DB::statement("
            UPDATE prescriptions p
            LEFT JOIN doctors d ON p.doctor_id = d.id
            SET p.doctor_id = NULL 
            WHERE p.doctor_id IS NOT NULL 
            AND d.id IS NULL
        ");
        
        Schema::table('prescriptions', function (Blueprint $table) {
            // Recreate the foreign key to point to doctors table
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');
        });
        
        // Handle outcomes table
        $outcomesForeignKey = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'outcomes'
            AND COLUMN_NAME = 'doctor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        if (!empty($outcomesForeignKey)) {
            DB::statement("ALTER TABLE `outcomes` DROP FOREIGN KEY `{$outcomesForeignKey[0]->CONSTRAINT_NAME}`");
        }
        
        Schema::table('outcomes', function (Blueprint $table) {
            // Make doctor_id nullable if it's not already (it should already be nullable)
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });
        
        // Set doctor_id to null for outcomes where the doctor doesn't exist in doctors table
        DB::statement("
            UPDATE outcomes o
            LEFT JOIN doctors d ON o.doctor_id = d.id
            SET o.doctor_id = NULL 
            WHERE o.doctor_id IS NOT NULL 
            AND d.id IS NULL
        ");
        
        Schema::table('outcomes', function (Blueprint $table) {
            // Recreate the foreign key to point to doctors table
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
        // Drop prescriptions foreign key
        try {
            Schema::table('prescriptions', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Try to find and drop by constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'prescriptions' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE prescriptions DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Set doctor_id to null for prescriptions where the doctor doesn't exist in users table
        DB::statement("
            UPDATE prescriptions p
            LEFT JOIN users u ON p.doctor_id = u.id
            SET p.doctor_id = NULL 
            WHERE p.doctor_id IS NOT NULL 
            AND u.id IS NULL
        ");
        
        Schema::table('prescriptions', function (Blueprint $table) {
            // Recreate the foreign key to point back to users table
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
        
        // Drop outcomes foreign key
        try {
            Schema::table('outcomes', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            // Try to find and drop by constraint name
            try {
                $foreignKey = DB::selectOne("
                    SELECT CONSTRAINT_NAME 
                    FROM information_schema.KEY_COLUMN_USAGE 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'outcomes' 
                    AND COLUMN_NAME = 'doctor_id'
                    AND REFERENCED_TABLE_NAME IS NOT NULL
                ");
                
                if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                    DB::statement("ALTER TABLE outcomes DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                }
            } catch (\Exception $e2) {
                // Foreign key doesn't exist, continue
            }
        }
        
        // Set doctor_id to null for outcomes where the doctor doesn't exist in users table
        DB::statement("
            UPDATE outcomes o
            LEFT JOIN users u ON o.doctor_id = u.id
            SET o.doctor_id = NULL 
            WHERE o.doctor_id IS NOT NULL 
            AND u.id IS NULL
        ");
        
        Schema::table('outcomes', function (Blueprint $table) {
            // Recreate the foreign key to point back to users table
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
