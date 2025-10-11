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
        Schema::table('lab_types', function (Blueprint $table) {
            $table->unsignedBigInteger('department_section_id')->nullable()->after('section_id');
            $table->foreign('department_section_id')->references('id')->on('sections');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_types', function (Blueprint $table) {
            $table->dropForeign(['department_section_id']);
            $table->dropColumn('department_section_id');
        });
    }
};
