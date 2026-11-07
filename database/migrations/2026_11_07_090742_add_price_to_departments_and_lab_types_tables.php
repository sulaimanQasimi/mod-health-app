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
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->default(0);
            }
        });

        Schema::table('lab_types', function (Blueprint $table) {
            if (! Schema::hasColumn('lab_types', 'price')) {
                $table->decimal('price', 12, 2)->nullable()->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (Schema::hasColumn('departments', 'price')) {
                $table->dropColumn('price');
            }
        });

        Schema::table('lab_types', function (Blueprint $table) {
            if (Schema::hasColumn('lab_types', 'price')) {
                $table->dropColumn('price');
            }
        });
    }
};
