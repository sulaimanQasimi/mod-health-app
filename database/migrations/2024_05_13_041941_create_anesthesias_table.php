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
        Schema::create('anesthesias', function (Blueprint $table) {
            $table->id();
            $table->text('plan',2000)->nullable();
            $table->string('date');
            $table->string('time');
            $table->string('planned_duration',192)->nullable();
            $table->string('position_on_bed',192)->nullable();
            $table->string('estimated_blood_waste',192)->nullable();
            $table->string('other_problems',192)->nullable();
            $table->enum('status', ['new', 'approved', 'rejected'])->default('new');
            $table->tinyInteger('operation_result')->default('0');
            $table->tinyInteger('is_operation_done')->default('0');
            $table->text('anesthesia_log_reply',2000)->nullable();
            $table->text('anesthesia_plan',2000)->nullable();
            $table->text('operation_remark',2000)->nullable();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('doctor_id');
            $table->text('operation_assistants_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('operation_surgion_id')->nullable();
            $table->unsignedBigInteger('operation_anesthesia_log_id')->nullable();
            $table->unsignedBigInteger('operation_anesthesist_id')->nullable();
            $table->unsignedBigInteger('operation_scrub_nurse_id')->nullable();
            $table->unsignedBigInteger('operation_circulation_nurse_id')->nullable();
            $table->unsignedBigInteger('operation_type_id')->nullable();

            $table->foreign('branch_id')
            ->references('id')
            ->on('branches');
            $table->foreign('appointment_id')
            ->references('id')
            ->on('appointments');
            $table->foreign('doctor_id')
            ->references('id')
            ->on('users');
            $table->foreign('operation_surgion_id')
            ->references('id')
            ->on('users');
            $table->foreign('operation_anesthesia_log_id')
            ->references('id')
            ->on('users');
            $table->foreign('operation_anesthesist_id')
            ->references('id')
            ->on('users');
            $table->foreign('operation_scrub_nurse_id')
            ->references('id')
            ->on('users');
            $table->foreign('operation_circulation_nurse_id')
            ->references('id')
            ->on('users');
            $table->foreign('patient_id')
            ->references('id')
            ->on('patients');
            $table->foreign('operation_type_id')
            ->references('id')
            ->on('operation_types');

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
        Schema::dropIfExists('anesthesias');
    }
};
