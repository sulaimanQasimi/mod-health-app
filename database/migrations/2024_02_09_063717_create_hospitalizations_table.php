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
        Schema::create('hospitalizations', function (Blueprint $table) {
            $table->id();
            $table->text('reason', 2000);
            $table->text('remarks',2000);
            $table->string('patinet_companion')->nullable();
            $table->string('companion_father_name')->nullable();
            $table->string('relation_to_patient')->nullable();
            $table->string('companion_card_type')->nullable();
            $table->text('discharge_remark',2000)->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->tinyInteger('is_discharged')->default(false);
            $table->enum('discharge_status', ['recovered', 'died', 'moved'])->nullable();
            $table->unsignedBigInteger('room_id');
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->unsignedBigInteger('branch_id');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->unsignedBigInteger('bed_id');
            $table->foreign('bed_id')->references('id')->on('beds');
            $table->text('food_type_id')->nullable();
            $table->unsignedBigInteger('appointment_id');
            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->unsignedBigInteger('doctor_id');
            $table->foreign('doctor_id')->references('id')->on('users');
            $table->unsignedBigInteger('patient_id');
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->unsignedBigInteger('under_review_id')->nullable();
            $table->foreign('under_review_id')->references('id')->on('under_reviews');
            $table->unsignedBigInteger('i_c_u_id')->nullable();
            $table->foreign('i_c_u_id')->references('id')->on('under_reviews');

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
        Schema::dropIfExists('hospitalizations');
    }
};
