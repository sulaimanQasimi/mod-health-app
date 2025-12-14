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
        Schema::create('dental_chart_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->string('measurement_type');
            $table->decimal('measurement_value', 10, 2)->nullable();
            $table->string('measurement_unit', 20)->nullable();
            $table->date('measurement_date');
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['dental_chart_id', 'measurement_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_chart_measurements');
    }
};
