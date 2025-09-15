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
        Schema::create('vital_sign_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vital_sign_id')->constrained()->onDelete('cascade');
            $table->time('morning_time')->nullable();
            $table->time('evening_time')->nullable();
            $table->string('day')->nullable(); // e.g., "Day 1"
            $table->date('date')->nullable();
            $table->foreignId('nurse_id')->nullable()->constrained()->onDelete('set null');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vital_sign_schedules');
    }
};
