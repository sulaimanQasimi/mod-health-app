<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('blood_banks', 'hemoglobin')) {
                $table->decimal('hemoglobin', 8, 2)->nullable()->after('quantity');
            }
            if (! Schema::hasColumn('blood_banks', 'hematocrit')) {
                $table->decimal('hematocrit', 8, 2)->nullable()->after('hemoglobin');
            }
            if (! Schema::hasColumn('blood_banks', 'factor')) {
                $table->string('factor')->nullable()->after('hematocrit');
            }
        });
    }

    public function down(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (Schema::hasColumn('blood_banks', 'factor')) {
                $table->dropColumn('factor');
            }
            if (Schema::hasColumn('blood_banks', 'hematocrit')) {
                $table->dropColumn('hematocrit');
            }
            if (Schema::hasColumn('blood_banks', 'hemoglobin')) {
                $table->dropColumn('hemoglobin');
            }
        });
    }
};
