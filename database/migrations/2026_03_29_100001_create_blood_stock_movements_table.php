<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_unit_id');
            $table->enum('movement_type', ['received', 'issued', 'adjusted', 'discarded', 'transferred']);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('blood_unit_id')->references('id')->on('blood_units')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users');

            $table->index(['blood_unit_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_stock_movements');
    }
};
