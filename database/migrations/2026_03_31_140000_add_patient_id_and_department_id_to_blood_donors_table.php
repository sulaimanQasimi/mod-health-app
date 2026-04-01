<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_donors', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('notes');
            $table->unsignedBigInteger('department_id')->nullable()->after('patient_id');
        });

        Schema::table('blood_donors', function (Blueprint $table) {
            $table->foreign('patient_id')->references('id')->on('patients')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->index(['patient_id']);
            $table->index(['department_id']);
        });
    }

    public function down(): void
    {
        Schema::table('blood_donors', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['department_id']);
        });

        Schema::table('blood_donors', function (Blueprint $table) {
            $table->dropColumn(['patient_id', 'department_id']);
        });
    }
};
