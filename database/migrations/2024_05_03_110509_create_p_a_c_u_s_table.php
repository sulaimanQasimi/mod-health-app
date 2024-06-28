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
        Schema::create('p_a_c_u_s', function (Blueprint $table) {
            $table->id();
            $table->text('description',2000);
            $table->enum('status', ['new', 'completed',])->default('new');
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->foreign('operation_id')->references('id')->on('anesthesias');

            $table->foreign('appointment_id')
            ->references('id')
            ->on('appointments');

            $table->foreign('hospitalization_id')
            ->references('id')
            ->on('hospitalizations');

            $table->foreign('patient_id')
            ->references('id')
            ->on('patients');

            $table->foreign('doctor_id')
            ->references('id')
            ->on('users');

            $table->foreign('branch_id')
            ->references('id')
            ->on('branches');

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
        Schema::dropIfExists('p_a_c_u_s');
    }
};
