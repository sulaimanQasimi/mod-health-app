<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_items', function (Blueprint $table) {
            $table->id();
            $table->string('result')->nullable();
            $table->string('result_file')->nullable();
            $table->string('status')->nullable();
            $table->tinyInteger('is_delivered')->default('0');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('lab_id');
            $table->unsignedBigInteger('lab_type_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('under_review_id')->nullable();
            $table->timestamps();

            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->foreign('lab_id')->references('id')->on('labs');
            $table->foreign('lab_type_id')->references('id')->on('lab_types');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('doctor_id')->references('id')->on('users');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('hospitalization_id')->references('id')->on('hospitalizations');
            $table->foreign('under_review_id')->references('id')->on('under_reviews');
            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicines');
    }
};
