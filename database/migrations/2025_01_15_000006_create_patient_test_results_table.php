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
        Schema::create('patient_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->string('ref_no');
            $table->unsignedBigInteger('lab_parameter_id');
            $table->foreign('lab_parameter_id')->references('id')->on('lab_test_parameters')->onDelete('cascade');
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->string('result')->nullable();
            $table->foreignId('test_registration_id')->constrained('patient_test_registrations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_test_results');
    }
};
