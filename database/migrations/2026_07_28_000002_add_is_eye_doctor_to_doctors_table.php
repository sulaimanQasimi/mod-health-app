<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->boolean('is_eye_doctor')->default(false)->after('is_nephrologist');
            $table->index(['branch_id', 'active_status', 'is_eye_doctor'], 'doctors_eye_doctor_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('doctors_eye_doctor_lookup_index');
            $table->dropColumn('is_eye_doctor');
        });
    }
};
