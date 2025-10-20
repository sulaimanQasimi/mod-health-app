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
        Schema::create('lab_test_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('testcategory_id')
                  ->constrained('test_categories')
                  ->cascadeOnDelete();
            $table->foreignId('test_id')
                  ->constrained('lab_tests')
                  ->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('unit')->nullable();
            $table->string('normal_range')->nullable();
            $table->decimal('result', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lab_test_parameters');
    }
};
