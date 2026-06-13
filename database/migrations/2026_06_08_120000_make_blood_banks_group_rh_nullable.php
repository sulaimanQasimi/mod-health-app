<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            $table->string('group')->nullable()->change();
            $table->string('rh')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            $table->string('group')->nullable(false)->change();
            $table->string('rh')->nullable(false)->change();
        });
    }
};
