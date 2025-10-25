<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Add long text column for detailed notes or descriptions (if not exists)
            if (!Schema::hasColumn('patient_test_registrations', 'detailed_notes')) {
                $table->longText('detailed_notes')->nullable()->after('notes');
            }
            
            // Add JSON column for storing structured data (metadata, configurations, etc.) (if not exists)
            if (!Schema::hasColumn('patient_test_registrations', 'metadata')) {
                $table->json('metadata')->nullable()->after('detailed_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn(['detailed_notes', 'metadata']);
        });
    }
};