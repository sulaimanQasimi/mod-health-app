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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('branch_id');
            $table->text('doctor_id')->nullable();
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('patient_id');
            $table->text('result',2000)->nullable();
            $table->date('date');
            $table->string('time');


            $table->foreign('branch_id')
            ->references('id')
            ->on('branches');

            $table->foreign('appointment_id')
            ->references('id')
            ->on('appointments');

            $table->foreign('patient_id')
            ->references('id')
            ->on('patients');

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
        Schema::dropIfExists('consultations');
    }
};
