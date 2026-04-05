<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persisted blood check details (ABO/Rh, component, quantity, clinical links, optional lab typing).
     * Optional blood_bank_id links one row to an existing blood request when applicable.
     */
    public function up(): void
    {
        if (Schema::hasTable('blood_checks')) {
            return;
        }

        Schema::create('blood_checks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('blood_bank_id')->nullable()->unique();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('anesthesia_id')->nullable();
            $table->unsignedBigInteger('i_c_u_id')->nullable();
            $table->unsignedBigInteger('under_review_id')->nullable();

            /** ABO group (same meaning as blood_banks.group: A, B, AB, O). */
            $table->string('abo_group', 8);
            /** Rh factor (+ / -). */
            $table->string('rh', 8);
            /** Component / product type (matches blood_banks.type). */
            $table->enum('component_type', ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'])->default('Fresh');
            $table->unsignedInteger('quantity')->default(0);
            /** Workflow status aligned with blood request lifecycle. */
            $table->enum('status', ['new', 'approved', 'rejected', 'delivered'])->default('new');

            $table->text('reject_reason')->nullable();
            $table->text('notes')->nullable();

            /** Lab-confirmed patient typing (optional; may differ from requested abo_group/rh until verified). */
            $table->string('patient_typed_group', 8)->nullable();
            $table->string('patient_typed_rh', 8)->nullable();

            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('blood_bank_id')->references('id')->on('blood_banks')->nullOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
            $table->foreign('appointment_id')->references('id')->on('appointments')->nullOnDelete();
            $table->foreign('patient_id')->references('id')->on('patients')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('operation_id')->references('id')->on('operations')->nullOnDelete();
            $table->foreign('hospitalization_id')->references('id')->on('hospitalizations')->nullOnDelete();
            $table->foreign('anesthesia_id')->references('id')->on('anesthesias')->nullOnDelete();
            $table->foreign('i_c_u_id')->references('id')->on('i_c_u_s')->nullOnDelete();
            $table->foreign('under_review_id')->references('id')->on('under_reviews')->nullOnDelete();
            $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['patient_id', 'created_at']);
            $table->index(['appointment_id', 'created_at']);
            $table->index(['branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_checks');
    }
};
