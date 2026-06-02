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
        Schema::table('hemodialysis_sessions', function (Blueprint $table) {
            $table->string('blood_type', 8)->nullable()->after('dialyzer_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hemodialysis_sessions', function (Blueprint $table) {
            $table->dropColumn('blood_type');
        });
    }
};
