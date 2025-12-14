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
        Schema::create('dental_periodontal_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->enum('measurement_point', [
                'mesial',
                'mid_mesial',
                'mid',
                'mid_distal',
                'distal',
                'lingual',
                'palatal'
            ]);
            $table->decimal('pocket_depth', 4, 2)->comment('Pocket depth in mm (0-20)');
            $table->decimal('recession', 4, 2)->nullable()->comment('Recession in mm');
            $table->boolean('bleeding')->default(false);
            $table->boolean('plaque')->default(false);
            $table->date('measurement_date');
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['dental_chart_id', 'measurement_point', 'measurement_date'], 'dental_perio_measurements_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_periodontal_measurements');
    }
};
