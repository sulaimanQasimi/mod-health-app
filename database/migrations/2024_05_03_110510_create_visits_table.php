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
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->text('description', 5000);
            $table->unsignedBigInteger('patient_id');
            $table->unsignedBigInteger('hospitalization_id')->nullable();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('i_c_u_id')->nullable();

            $table->foreign('patient_id')
                  ->references('id')
                  ->on('patients');
            $table->foreign('hospitalization_id')
                  ->references('id')
                  ->on('hospitalizations');
            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('users');
            $table->foreign('i_c_u_id')
                  ->references('id')
                  ->on('i_c_u_s');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
