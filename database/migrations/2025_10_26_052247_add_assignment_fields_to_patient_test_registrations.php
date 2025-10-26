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
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('assigned_section_id')->nullable()->constrained('sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropForeign(['assigned_section_id']);
            $table->dropColumn(['assigned_to', 'assigned_at', 'assigned_section_id']);
        });
    }
};
