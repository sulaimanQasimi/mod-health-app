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
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name_en', 255)->nullable();
            $table->string('name_dr', 255)->nullable();
            $table->string('name_pa', 255)->nullable();
            $table->string('zone', 50)->nullable();
            $table->string('zname_dr', 255)->nullable();
            $table->string('zname_pa', 255)->nullable();
            $table->string('zname_en', 255)->nullable();
            $table->string('latitude', 20)->comment('latitude of provinces');
            $table->string('longitude', 20)->comment('longitude of provinces');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
