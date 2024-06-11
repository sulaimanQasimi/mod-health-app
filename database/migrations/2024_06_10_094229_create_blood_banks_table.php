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
        Schema::create('blood_banks', function (Blueprint $table) {
            $table->id();
            $table->string('group');
            $table->string('rh');
            $table->string('quantity');
            $table->enum('status', ['new', 'approved','rejected','delivered'])->default('new');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->unsignedBigInteger('i_c_u_id')->nullable();
            $table->unsignedBigInteger('under_review_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('anesthesia_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('appointment_id')->references('id')->on('appointments');
            $table->foreign('operation_id')->references('id')->on('anesthesias');
            $table->foreign('i_c_u_id')->references('id')->on('i_c_u_s');
            $table->foreign('under_review_id')->references('id')->on('under_reviews');
            $table->foreign('hospitalization_id')->references('id')->on('hospitalizations');
            $table->foreign('anesthesia_id')->references('id')->on('anesthesias');
            $table->foreign('patient_id')->references('id')->on('users');
            $table->foreign('department_id')->references('id')->on('departments');
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
        Schema::dropIfExists('blood_banks');
    }
};
