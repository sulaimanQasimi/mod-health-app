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
        // Handle hospitalizations table
        $hospitalizationsForeignKey = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'hospitalizations'
            AND COLUMN_NAME = 'doctor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");
        
        if (!empty($hospitalizationsForeignKey)) {
            DB::statement("ALTER TABLE `hospitalizations` DROP FOREIGN KEY `{$hospitalizationsForeignKey[0]->CONSTRAINT_NAME}`");
        }
        
        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });
        
        Schema::table('hospitalizations', function (Blueprint $table) {
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
        Schema::table('hospitalizations', function (Blueprint $table) {
            $foreignKey = DB::select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'hospitalizations'
                AND COLUMN_NAME = 'doctor_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
                LIMIT 1
            ");
            
            if (!empty($foreignKey)) {
                DB::statement("ALTER TABLE `hospitalizations` DROP FOREIGN KEY `{$foreignKey[0]->CONSTRAINT_NAME}`");
            }
            
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
