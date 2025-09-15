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
        Schema::create('nurse_notes', function (Blueprint $table) {
            $table->id();
            $table->time('time_am')->nullable();
            $table->time('time_pm')->nullable();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('morphable_id');
            $table->string('morphable_type');
            $table->unsignedBigInteger('nurse_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes for better performance
            $table->index(['morphable_id', 'morphable_type']);
            $table->index('nurse_id');
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_notes');
    }
};
