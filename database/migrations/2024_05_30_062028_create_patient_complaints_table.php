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
        Schema::create('patient_complaints', function (Blueprint $table) {
            $table->id();
            $table->text('description',1000);
            $table->unsignedBigInteger('hospitalization_id');

            $table->foreign('hospitalization_id')
                  ->references('id')
                  ->on('hospitalizations');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_complaints');
    }
};
