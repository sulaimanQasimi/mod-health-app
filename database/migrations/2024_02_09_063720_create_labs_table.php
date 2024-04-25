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
            $table->unsignedBigInteger('diagnose_id');
            $table->unsignedBigInteger('lab_type_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');

            $table->foreign('diagnose_id')
                  ->references('id')
                  ->on('diagnoses');

            $table->foreign('lab_type_id')
                  ->references('id')
                  ->on('lab_types');

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients');

            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('users');

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
