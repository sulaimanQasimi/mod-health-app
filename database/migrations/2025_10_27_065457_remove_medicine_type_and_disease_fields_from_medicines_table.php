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
        Schema::table('medicines', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['medicine_type_id']);
            // Drop the columns
            $table->dropColumn(['medicine_type_id', 'disease_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medicines', function (Blueprint $table) {
            // Re-add the columns
            $table->unsignedBigInteger('medicine_type_id')->after('name');
            $table->text('disease_id')->nullable()->after('medicine_type_id');
            // Re-add foreign key constraint
            $table->foreign('medicine_type_id')
                ->references('id')
                ->on('medicine_types');
        });
    }
};
