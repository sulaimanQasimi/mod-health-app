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
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('result',1000)->nullable();
            $table->string('result_file',500)->nullable();
            $table->integer('status')->default('0');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('under_review_id')->nullable();
            $table->unsignedBigInteger('i_c_u_id')->nullable();
            $table->unsignedBigInteger('lab_type_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('lab_type_section_id');
            $table->unsignedBigInteger('doctor_id');

            $table->foreign('branch_id')
                  ->references('id')
                  ->on('diagnoses');

            $table->foreign('hospitalization_id')
                  ->references('id')
                  ->on('hospitalizations');

            $table->foreign('under_review_id')
                  ->references('id')
                  ->on('under_reviews');

            $table->foreign('i_c_u_id')
                  ->references('id')
                  ->on('i_c_u_s');

            $table->foreign('lab_type_id')
                  ->references('id')
                  ->on('lab_types');

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients');

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('users');

            $table->foreign('appointment_id')
                  ->references('id')
                  ->on('appointments');

            $table->foreign('lab_type_section_id')
                  ->references('id')
                  ->on('lab_type_sections');

            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
