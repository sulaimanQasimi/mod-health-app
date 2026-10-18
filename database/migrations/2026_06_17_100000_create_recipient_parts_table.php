<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipient_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipient_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code', 50);
            $table->timestamps();

            $table->unique(['recipient_id', 'code']);
            $table->unique(['recipient_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipient_parts');
    }
};
