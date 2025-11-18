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
        // Drop old foreign keys if they exist (using try-catch to handle missing keys)
        $columns = ['doctor_id', 'operation_surgion_id', 'operation_anesthesia_log_id', 'operation_anesthesist_id', 'operation_scrub_nurse_id', 'operation_circulation_nurse_id'];
        
        foreach ($columns as $column) {
            try {
                Schema::table('anesthesias', function (Blueprint $table) use ($column) {
                    $table->dropForeign([$column]);
                });
            } catch (\Exception $e) {
                // Foreign key doesn't exist, try to drop by finding the constraint name
                try {
                    $foreignKey = DB::selectOne("
                        SELECT CONSTRAINT_NAME 
                        FROM information_schema.KEY_COLUMN_USAGE 
                        WHERE TABLE_SCHEMA = DATABASE() 
                        AND TABLE_NAME = 'anesthesias' 
                        AND COLUMN_NAME = ?
                        AND REFERENCED_TABLE_NAME IS NOT NULL
                    ", [$column]);
                    
                    if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                        DB::statement("ALTER TABLE anesthesias DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
                    }
                } catch (\Exception $e2) {
                    // Foreign key doesn't exist, continue
                }
            }
        }

        // Add new foreign keys pointing to doctors table
        Schema::table('anesthesias', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade');

            $table->foreign('operation_surgion_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('operation_anesthesia_log_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('operation_anesthesist_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('operation_scrub_nurse_id')
                ->references('id')
                ->on('nurses')
                ->onDelete('set null');

            $table->foreign('operation_circulation_nurse_id')
                ->references('id')
                ->on('nurses')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anesthesias', function (Blueprint $table) {
            // Drop new foreign keys
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['operation_surgion_id']);
            $table->dropForeign(['operation_anesthesia_log_id']);
            $table->dropForeign(['operation_anesthesist_id']);
            $table->dropForeign(['operation_scrub_nurse_id']);
            $table->dropForeign(['operation_circulation_nurse_id']);
        });

        // Restore old foreign keys pointing to users table
        Schema::table('anesthesias', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('operation_surgion_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('operation_anesthesia_log_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('operation_anesthesist_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('operation_scrub_nurse_id')
                ->references('id')
                ->on('nurses')
                ->onDelete('set null');

            $table->foreign('operation_circulation_nurse_id')
                ->references('id')
                ->on('nurses')
                ->onDelete('set null');
        });
    }
};
