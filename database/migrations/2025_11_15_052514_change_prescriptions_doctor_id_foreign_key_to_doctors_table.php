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
        Schema::table('prescriptions', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['doctor_id']);
            
            // Recreate the foreign key to point back to users table
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
        
        Schema::table('outcomes', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['doctor_id']);
            
            // Recreate the foreign key to point back to users table
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
