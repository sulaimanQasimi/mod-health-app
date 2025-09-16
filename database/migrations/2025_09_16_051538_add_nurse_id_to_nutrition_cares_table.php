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
        Schema::table('nutrition_cares', function (Blueprint $table) {
            $table->unsignedBigInteger('nurse_id')->nullable()->after('patient_name');
            $table->index('nurse_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrition_cares', function (Blueprint $table) {
            $table->dropIndex(['nurse_id']);
            $table->dropColumn('nurse_id');
        });
    }
};
