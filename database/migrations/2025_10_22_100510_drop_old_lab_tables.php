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
        Schema::table('lab_items', function (Blueprint $table) {
            $table->dropForeign(['lab_id']);
            $table->dropForeign(['lab_type_id']);
        });

        Schema::table('labs', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['lab_type_section_id']);
        });

        // Drop the tables
        Schema::dropIfExists('lab_items');
        Schema::dropIfExists('labs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate labs table
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('lab_type_section_id');
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('lab_type_section_id')->references('id')->on('lab_type_sections')->onDelete('cascade');
        });

        // Recreate lab_items table
        Schema::create('lab_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lab_id');
            $table->unsignedBigInteger('lab_type_id');
            $table->boolean('status')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('lab_id')->references('id')->on('labs')->onDelete('cascade');
            $table->foreign('lab_type_id')->references('id')->on('lab_types')->onDelete('cascade');
        });
    }
};
