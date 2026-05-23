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
        Schema::create('nephrology_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('set null');
            $table->date('visit_date');
            $table->string('ref_no')->unique();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->text('chief_complaint')->nullable();
            $table->text('diagnosis')->nullable();
            $table->string('ckd_aki_stage')->nullable();
            $table->boolean('dialysis_required')->default(false);
            $table->enum('dialysis_type', ['HD', 'PD', 'CRRT'])->nullable();
            $table->enum('access_type', ['av_fistula', 'graft', 'catheter'])->nullable();
            $table->decimal('lab_creatinine', 10, 2)->nullable();
            $table->decimal('lab_urea', 10, 2)->nullable();
            $table->decimal('lab_potassium', 10, 2)->nullable();
            $table->decimal('lab_sodium', 10, 2)->nullable();
            $table->decimal('lab_hb', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
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
        Schema::dropIfExists('nephrology_registrations');
    }
};
