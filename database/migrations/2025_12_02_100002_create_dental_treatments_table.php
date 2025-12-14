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
        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_registration_id')->constrained('dentist_registrations')->onDelete('cascade');
            $table->string('treatment_type');
            $table->string('tooth_number')->nullable();
            $table->text('treatment_description');
            $table->date('treatment_date');
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->decimal('cost', 10, 2)->nullable();
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
        Schema::dropIfExists('dental_treatments');
    }
};
