<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->foreignId('disease_category_id')
                ->nullable()
                ->after('name')
                ->constrained('disease_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('diseases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('disease_category_id');
        });
    }
};
