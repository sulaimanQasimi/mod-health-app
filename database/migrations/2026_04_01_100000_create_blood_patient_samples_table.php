<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_patient_samples', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_bank_id');
            $table->unsignedBigInteger('patient_id');
            $table->string('sample_id', 100)->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->unsignedBigInteger('collected_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('blood_bank_id')->references('id')->on('blood_banks')->cascadeOnDelete();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('collected_by')->references('id')->on('users');

            $table->index(['blood_bank_id', 'created_at']);
            $table->index(['patient_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_patient_samples');
    }
};
