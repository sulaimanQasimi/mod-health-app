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
        Schema::create('diabetes_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nurse_id')->nullable()->constrained('nurses')->onDelete('set null');
            $table->foreignId('medicine_id')->nullable()->constrained('medicines')->onDelete('set null');
            $table->decimal('insulin_dose', 8, 2)->nullable();
            $table->decimal('rbs', 8, 2)->nullable()->comment('Random Blood Sugar');
            $table->decimal('fbs', 8, 2)->nullable()->comment('Fasting Blood Sugar');
            $table->string('unit', 20)->nullable()->default('mg/dl');
            $table->time('time')->nullable();
            $table->date('date')->nullable();
            $table->string('diabetes_chartable_type');
            $table->unsignedBigInteger('diabetes_chartable_id');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for better performance
            $table->index(['diabetes_chartable_type', 'diabetes_chartable_id'], 'dc_chartable_idx');
            $table->index(['date', 'time'], 'dc_date_time_idx');
            $table->index('nurse_id', 'dc_nurse_idx');
            $table->index('medicine_id', 'dc_medicine_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diabetes_charts');
    }
};