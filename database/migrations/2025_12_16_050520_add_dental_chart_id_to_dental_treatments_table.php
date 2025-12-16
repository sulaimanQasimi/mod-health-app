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
        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->foreignId('dental_chart_id')->nullable()->after('dentist_registration_id')->constrained('dental_charts')->onDelete('cascade');
            $table->index('dental_chart_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dental_treatments', function (Blueprint $table) {
            $table->dropForeign(['dental_chart_id']);
            $table->dropIndex(['dental_chart_id']);
            $table->dropColumn('dental_chart_id');
        });
    }
};
