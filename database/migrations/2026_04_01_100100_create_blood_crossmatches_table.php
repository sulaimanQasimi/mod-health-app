<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_crossmatches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_bank_id');
            $table->unsignedBigInteger('blood_unit_id');
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('patient_sample_id')->nullable();
            $table->enum('major_result', ['pending', 'compatible', 'incompatible', 'inconclusive'])->default('pending');
            $table->enum('minor_result', ['pending', 'compatible', 'incompatible', 'inconclusive'])->default('pending');
            $table->enum('status', ['pending', 'compatible', 'incompatible', 'overridden'])->default('pending');
            $table->boolean('auto_decision')->default(true);
            $table->string('auto_reason', 255)->nullable();
            $table->boolean('is_overridden')->default(false);
            $table->unsignedBigInteger('override_by')->nullable();
            $table->string('override_reason', 1000)->nullable();
            $table->timestamp('tested_at')->nullable();
            $table->unsignedBigInteger('tested_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('blood_bank_id')->references('id')->on('blood_banks')->cascadeOnDelete();
            $table->foreign('blood_unit_id')->references('id')->on('blood_units')->cascadeOnDelete();
            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('patient_sample_id')->references('id')->on('blood_patient_samples')->nullOnDelete();
            $table->foreign('override_by')->references('id')->on('users');
            $table->foreign('tested_by')->references('id')->on('users');

            $table->unique(['blood_bank_id', 'blood_unit_id']);
            $table->index(['blood_bank_id', 'status']);
            $table->index(['blood_unit_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_crossmatches');
    }
};
