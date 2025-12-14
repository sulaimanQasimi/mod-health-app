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
        Schema::create('dental_chart_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dental_chart_id')->constrained('dental_charts')->onDelete('cascade');
            $table->string('image_path');
            $table->enum('image_type', ['xray', 'photo', 'diagram'])->default('xray');
            $table->text('description')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['dental_chart_id', 'image_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_chart_images');
    }
};
