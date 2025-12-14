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
        Schema::create('dental_examinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_registration_id')->constrained('dentist_registrations')->onDelete('cascade');
            $table->date('examination_date');
            $table->text('chief_complaint')->nullable();
            $table->text('clinical_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_examinations');
    }
};
