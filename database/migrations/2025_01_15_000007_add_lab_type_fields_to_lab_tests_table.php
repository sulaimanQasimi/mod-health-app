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
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_type_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('lab_type_section_id')->nullable()->after('lab_type_id');
            $table->boolean('has_parameters')->default(true)->after('name');
            
            $table->foreign('lab_type_id')->references('id')->on('lab_types')->onDelete('set null');
            $table->foreign('lab_type_section_id')->references('id')->on('lab_type_sections')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->dropForeign(['lab_type_id']);
            $table->dropForeign(['lab_type_section_id']);
            $table->dropColumn(['lab_type_id', 'lab_type_section_id', 'has_parameters']);
        });
    }
};
