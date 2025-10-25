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
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_type_id')->nullable()->after('test_id');
            $table->foreign('lab_type_id')->references('id')->on('lab_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            $table->dropForeign(['lab_type_id']);
            $table->dropColumn('lab_type_id');
        });
    }
};