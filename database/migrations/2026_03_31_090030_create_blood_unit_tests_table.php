<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_unit_tests', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('blood_unit_id');

            // Grouping confirmation (recording result as requested)
            $table->string('abo_result', 8)->nullable(); // A,B,AB,O
            $table->string('rh_result', 8)->nullable(); // +,-

            // Coombs tests
            $table->enum('dct_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');
            $table->enum('ict_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');

            // Infection screening
            $table->enum('hbs_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');
            $table->enum('hcv_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');
            $table->enum('hiv_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');
            $table->enum('vdrl_result', ['pending', 'negative', 'positive', 'inconclusive'])->default('pending');

            $table->enum('overall_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->timestamp('tested_at')->nullable();
            $table->unsignedBigInteger('tested_by')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();

            $table->foreign('blood_unit_id')->references('id')->on('blood_units')->cascadeOnDelete();
            $table->foreign('tested_by')->references('id')->on('users');

            $table->unique('blood_unit_id');
            $table->index(['overall_status', 'tested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_unit_tests');
    }
};

