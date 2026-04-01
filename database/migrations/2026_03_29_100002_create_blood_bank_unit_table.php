<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_bank_unit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blood_bank_id');
            $table->unsignedBigInteger('blood_unit_id');
            $table->timestamp('issued_at')->nullable();
            $table->unsignedBigInteger('issued_by')->nullable();
            $table->timestamps();

            $table->foreign('blood_bank_id')->references('id')->on('blood_banks')->cascadeOnDelete();
            $table->foreign('blood_unit_id')->references('id')->on('blood_units')->cascadeOnDelete();
            $table->foreign('issued_by')->references('id')->on('users');

            $table->unique('blood_unit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_bank_unit');
    }
};
