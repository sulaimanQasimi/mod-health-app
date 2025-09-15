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
        Schema::create('medication_administration_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medication_administration_record_id')->constrained('medication_administration_records', 'id', 'mar_times_record_fk')->onDelete('cascade');
            $table->time('time')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Add indexes for better performance
            $table->index('medication_administration_record_id', 'mar_time_record_index');
            $table->index('time', 'mar_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administration_times');
    }
};
