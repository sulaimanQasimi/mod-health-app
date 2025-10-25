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
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            $table->dropForeign(['testcategory_id']);
            $table->dropColumn('testcategory_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            $table->foreignId('testcategory_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('test_categories')
                  ->cascadeOnDelete();
        });
    }
};
