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
        Schema::table('anesthesias', function (Blueprint $table) {
            // Drop old foreign keys
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['operation_surgion_id']);
            $table->dropForeign(['operation_anesthesia_log_id']);
            $table->dropForeign(['operation_anesthesist_id']);
            $table->dropForeign(['operation_scrub_nurse_id']);
            $table->dropForeign(['operation_circulation_nurse_id']);
        });

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
                ->on('doctors')
                ->onDelete('set null');

            $table->foreign('operation_circulation_nurse_id')
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
                ->on('users')
                ->onDelete('set null');

            $table->foreign('operation_circulation_nurse_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
