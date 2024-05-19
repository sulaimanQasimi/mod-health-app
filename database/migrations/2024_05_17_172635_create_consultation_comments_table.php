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
        Schema::create('consultation_comments', function (Blueprint $table) {
            $table->id();
            $table->text('comment',5000)->nullable();
            $table->unsignedBigInteger('consultation_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id');

            $table->foreign('consultation_id')
            ->references('id')
            ->on('consultations');
            $table->foreign('appointment_id')
            ->references('id')
            ->on('appointments');
            $table->foreign('doctor_id')
            ->references('id')
            ->on('users');
            $table->foreign('patient_id')
            ->references('id')
            ->on('patients');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_comments');
    }
};
