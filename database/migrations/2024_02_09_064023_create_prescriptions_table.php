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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->text('description', 5000);
            $table->string('dosage', 191);
            $table->string('frequency', 191);
            $table->string('amount', 191);

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');

            $table->foreign('branch_id')
                  ->references('id')
                  ->on('diagnoses');

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients');

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('users');

            $table->foreign('appointment_id')
                  ->references('id')
                  ->on('appointments');

            $table->softDeletes();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
