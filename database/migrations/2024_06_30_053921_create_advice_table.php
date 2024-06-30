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
        Schema::create('advice', function (Blueprint $table) {
            $table->id();
            $table->text('description',1000);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('appointment_id');
            $table->unsignedBigInteger('i_c_u_id')->nullable();
            $table->unsignedBigInteger('hospitalization_id')->nullable();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');

            $table->foreign('doctor_id')
                ->references('id')
                ->on('users');

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments');

            $table->foreign('i_c_u_id')
                ->references('id')
                ->on('i_c_u_s');

            $table->foreign('hospitalization_id')
                ->references('id')
                ->on('hospitalizations');

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
        Schema::dropIfExists('advice');
    }
};
