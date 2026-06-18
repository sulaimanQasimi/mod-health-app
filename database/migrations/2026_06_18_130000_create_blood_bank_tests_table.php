<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('blood_bank_tests')) {
            return;
        }

        Schema::create('blood_bank_tests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_bank_id');
            $table->string('test_name');
            $table->enum('result', ['positive', 'negative'])->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('filled_test_by')->nullable();
            $table->timestamps();

            $table->foreign('blood_bank_id')->references('id')->on('blood_banks')->cascadeOnDelete();
            $table->foreign('filled_test_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['blood_bank_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_bank_tests');
    }
};
