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
        Schema::create('under_reviews', function (Blueprint $table) {
            $table->id();
            $table->text('reason', 2000);
            $table->text('remarks',2000);
            $table->text('discharge_remark',2000)->nullable();
            $table->tinyInteger('is_discharged')->default(false);
            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds');
            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->unsignedBigInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('users');
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->foreign('operation_id')->references('id')->on('anesthesias');
            $table->unsignedBigInteger('prescription_id')->nullable();
            $table->foreign('prescription_id')->references('id')->on('prescriptions');
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->foreign('hospitalization_id')->references('id')->on('hospitalizations');
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
        Schema::dropIfExists('under_reviews');
    }
};
