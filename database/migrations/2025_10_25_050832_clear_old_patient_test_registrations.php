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
        // Clear existing patient test registrations that reference non-existent lab tests
        // This is necessary because the lab_tests table has been dropped
        
        // First delete patient test results
        DB::table('patient_test_results')->delete();
        
        // Then delete patient test registrations
        DB::table('patient_test_registrations')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be reversed as it clears data
        // In a production environment, you would need to restore from backup
    }
};