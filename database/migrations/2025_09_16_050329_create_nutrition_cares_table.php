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
        Schema::create('nutrition_cares', function (Blueprint $table) {
            $table->id();
            
            // Patient Information
            $table->string('patient_name');
            
            // Observation Fields (Boolean checkboxes)
            $table->boolean('cough')->default(false);
            $table->boolean('sound')->default(false);
            $table->boolean('fluid_swallowing_ability')->default(false);
            $table->boolean('weight')->default(false);
            $table->boolean('amount_and_type_of_nutrition')->default(false);
            $table->boolean('diarrhea')->default(false);
            $table->boolean('heart_failure_and_kidney_disease')->default(false);
            $table->boolean('remaining_materials')->default(false);
            $table->boolean('type_of_tube')->default(false);
            
            // Intervention Fields (Boolean checkboxes)
            $table->boolean('constipation')->default(false);
            $table->boolean('nutrition_is_provided')->default(false);
            $table->boolean('mouth_hygiene')->default(false);
            $table->boolean('oral_nutrition_advices')->default(false);
            $table->boolean('voice_exercise')->default(false);
            $table->boolean('swallowing_exercise')->default(false);
            $table->boolean('aspiration_prevention_proceeded')->default(false);
            
            // Full Note Section
            $table->text('nutrition_care_full_note')->nullable();
            
            // Morphable Relationship
            $table->unsignedBigInteger('morphable_id');
            $table->string('morphable_type');
            
            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['morphable_id', 'morphable_type']);
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_cares');
    }
};
