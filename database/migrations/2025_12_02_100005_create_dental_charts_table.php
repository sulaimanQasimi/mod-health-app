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
        Schema::create('dental_charts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_registration_id')->constrained('dentist_registrations')->onDelete('cascade');
            $table->tinyInteger('tooth_number')->comment('Tooth number 1-32 (FDI notation)');
            $table->enum('tooth_condition', [
                'healthy', 
                'cavity', 
                'filling', 
                'crown', 
                'bridge', 
                'extraction', 
                'missing', 
                'impacted',
                'root_canal',
                'implant',
                'decay',
                'fractured'
            ])->default('healthy');
            $table->enum('gum_health', [
                'healthy',
                'gingivitis',
                'periodontitis',
                'recession',
                'bleeding'
            ])->nullable();
            $table->decimal('oral_hygiene_score', 3, 1)->nullable()->comment('Score 0-10');
            $table->decimal('pocket_depth', 4, 2)->nullable()->comment('Pocket depth in mm');
            $table->boolean('bleeding')->default(false);
            $table->enum('mobility', ['none', 'grade1', 'grade2', 'grade3'])->nullable();
            $table->text('treatment_history')->nullable();
            $table->json('measurements')->nullable()->comment('Additional measurements');
            $table->date('chart_date');
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['dentist_registration_id', 'tooth_number']);
            $table->index('chart_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_charts');
    }
};
