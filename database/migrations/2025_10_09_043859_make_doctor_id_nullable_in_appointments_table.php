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
        Schema::table('appointments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['doctor_id']);
            
            // Make doctor_id nullable
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['doctor_id']);
            
            // Make doctor_id not nullable again
            $table->unsignedBigInteger('doctor_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users');
        });
    }
};
