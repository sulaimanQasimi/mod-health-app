<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['bed_id']);
        });

        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable()->change();
            $table->unsignedBigInteger('bed_id')->nullable()->change();
            $table->foreign('room_id')->references('id')->on('rooms')->nullOnDelete();
            $table->foreign('bed_id')->references('id')->on('beds')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->dropForeign(['room_id']);
            $table->dropForeign(['bed_id']);
        });

        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->unsignedBigInteger('room_id')->nullable(false)->change();
            $table->unsignedBigInteger('bed_id')->nullable(false)->change();
            $table->foreign('room_id')->references('id')->on('rooms');
            $table->foreign('bed_id')->references('id')->on('beds');
        });
    }
};
