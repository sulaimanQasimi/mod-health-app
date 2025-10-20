<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ref_numbers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('last_ref_no')->default(1001000);
            $table->timestamps();
        });

        // Seed initial value
        DB::table('ref_numbers')->insert(['last_ref_no' => 1001000]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ref_numbers');
    }
};
