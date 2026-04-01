<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds donation_id when blood_units was created from an older migration
     * that did not include this column (editing the original migration does not re-run it).
     */
    public function up(): void
    {
        if (! Schema::hasTable('blood_units') || Schema::hasColumn('blood_units', 'donation_id')) {
            return;
        }

        Schema::table('blood_units', function (Blueprint $table) {
            $table->unsignedBigInteger('donation_id')->nullable()->after('branch_id');
        });

        Schema::table('blood_units', function (Blueprint $table) {
            $table->foreign('donation_id')->references('id')->on('blood_donations')->nullOnDelete();
            $table->index(['donation_id']);
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_units') || ! Schema::hasColumn('blood_units', 'donation_id')) {
            return;
        }

        Schema::table('blood_units', function (Blueprint $table) {
            $table->dropForeign(['donation_id']);
            $table->dropColumn('donation_id');
        });
    }
};
