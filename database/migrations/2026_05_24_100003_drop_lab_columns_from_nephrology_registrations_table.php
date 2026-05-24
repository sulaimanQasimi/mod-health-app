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
        Schema::table('nephrology_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'lab_creatinine',
                'lab_urea',
                'lab_potassium',
                'lab_sodium',
                'lab_hb',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nephrology_registrations', function (Blueprint $table) {
            $table->decimal('lab_creatinine', 10, 2)->nullable()->after('access_type');
            $table->decimal('lab_urea', 10, 2)->nullable()->after('lab_creatinine');
            $table->decimal('lab_potassium', 10, 2)->nullable()->after('lab_urea');
            $table->decimal('lab_sodium', 10, 2)->nullable()->after('lab_potassium');
            $table->decimal('lab_hb', 10, 2)->nullable()->after('lab_sodium');
        });
    }
};
