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
        Schema::create('i_c_u_s', function (Blueprint $table) {
            $table->id();
            $table->text('description',2000);
            $table->enum('status', ['new', 'approved', 'rejected'])->default('new');
            $table->text('icu_enterance_note',2000)->nullable();
            $table->text('icu_reject_reason',2000)->nullable();
            $table->tinyInteger('is_discharged')->default(false);
            $table->enum('discharge_status', ['recovered', 'died', 'moved'])->nullable();
            $table->text('discharge_remark',2000)->nullable();
            $table->timestamp('discharged_at')->nullable();
            $table->text('cause_of_death',2000)->nullable();
            $table->string('death_date',2000)->nullable();
            $table->string('death_time',2000)->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('operation_id')->nullable();
            $table->unsignedBigInteger('move_department_id')->nullable();
            $table->foreign('operation_id')->references('id')->on('anesthesias');

            $table->foreign('appointment_id')
            ->references('id')
            ->on('appointments');

            $table->foreign('hospitalization_id')
            ->references('id')
            ->on('hospitalizations');

            $table->foreign('patient_id')
            ->references('id')
            ->on('patients');

            $table->foreign('doctor_id')
            ->references('id')
            ->on('users');

            $table->foreign('branch_id')
            ->references('id')
            ->on('branches');

            $table->foreign('move_department_id')
            ->references('id')
            ->on('departments');

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
        Schema::dropIfExists('i_c_u_s');
    }
};
