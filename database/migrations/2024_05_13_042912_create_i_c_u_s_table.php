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
        Schema::create('i_c_u_s', function (Blueprint $table) {
            $table->id();
            $table->text('description',2000);
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('branch_id');

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

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('i_c_u_s');
    }
};
