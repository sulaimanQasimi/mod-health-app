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
        Schema::create('hemodialysis_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->unique();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('nephrology_registration_id')->nullable()->constrained('nephrology_registrations')->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->text('diagnosis')->nullable();
            $table->string('dialysis_schedule')->nullable();
            $table->date('session_date');
            $table->time('session_time')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->enum('vascular_access_type', ['av_fistula', 'graft', 'catheter'])->nullable();
            $table->string('pre_blood_pressure', 50)->nullable();
            $table->decimal('pre_weight', 8, 2)->nullable();
            $table->unsignedSmallInteger('pre_pulse')->nullable();
            $table->decimal('pre_temperature', 4, 1)->nullable();
            $table->string('post_blood_pressure', 50)->nullable();
            $table->decimal('post_weight', 8, 2)->nullable();
            $table->unsignedSmallInteger('post_pulse')->nullable();
            $table->decimal('post_temperature', 4, 1)->nullable();
            $table->decimal('fluid_removed_ml', 10, 2)->nullable();
            $table->string('dialyzer_type')->nullable();
            $table->text('complications_notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hemodialysis_sessions');
    }
};
