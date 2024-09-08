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
        Schema::create('printed_numbers', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->foreignId('patient_id')->constrained()->onDelete('cascade'); // Foreign key to patients table
            $table->unsignedInteger('number'); // Store the printed number
            $table->date('date'); // Store the date of the print
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printed_numbers');
    }
};
