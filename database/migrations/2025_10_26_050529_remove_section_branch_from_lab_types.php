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
        // Drop foreign key constraints first
        Schema::table('lab_types', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropForeign(['branch_id']);
        });

        // Drop columns from lab_types table
        Schema::table('lab_types', function (Blueprint $table) {
            $table->dropColumn(['section_id', 'branch_id']);
        });

        // Drop lab_type_section_id from lab_tests table if it exists
        if (Schema::hasColumn('lab_tests', 'lab_type_section_id')) {
            Schema::table('lab_tests', function (Blueprint $table) {
                $table->dropForeign(['lab_type_section_id']);
                $table->dropColumn('lab_type_section_id');
            });
        }

        // Drop lab_type_section_id from labs table if it exists
        if (Schema::hasColumn('labs', 'lab_type_section_id')) {
            Schema::table('labs', function (Blueprint $table) {
                $table->dropForeign(['lab_type_section_id']);
                $table->dropColumn('lab_type_section_id');
            });
        }

        // Drop the lab_type_sections table
        Schema::dropIfExists('lab_type_sections');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate lab_type_sections table
        Schema::create('lab_type_sections', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->unsignedBigInteger('section_id')->nullable();
            $table->timestamps();
        });

        // Recreate lab_types table columns
        Schema::table('lab_types', function (Blueprint $table) {
            $table->unsignedBigInteger('section_id');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('section_id')->references('id')->on('lab_type_sections');
            $table->foreign('branch_id')->references('id')->on('branches');
        });

        // Recreate lab_type_section_id in lab_tests table
        Schema::table('lab_tests', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_type_section_id')->nullable();
            $table->foreign('lab_type_section_id')->references('id')->on('lab_type_sections')->onDelete('set null');
        });

        // Recreate lab_type_section_id in labs table
        Schema::table('labs', function (Blueprint $table) {
            $table->unsignedBigInteger('lab_type_section_id')->nullable();
            $table->foreign('lab_type_section_id')->references('id')->on('lab_type_sections')->onDelete('set null');
        });
    }
};
