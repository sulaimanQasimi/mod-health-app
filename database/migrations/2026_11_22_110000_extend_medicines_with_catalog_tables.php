<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_countries', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medicine_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('medicine_gram_mls', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->decimal('gram', 12, 4)->nullable();
            $table->decimal('ml', 12, 4)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                if (! Schema::hasColumn('medicines', 'medicine_type_id')) {
                    $table->foreignId('medicine_type_id')->nullable()->after('name')->constrained('medicine_types')->nullOnDelete();
                }
                if (! Schema::hasColumn('medicines', 'medicine_country_id')) {
                    $table->foreignId('medicine_country_id')->nullable()->after('medicine_type_id')->constrained('medicine_countries')->nullOnDelete();
                }
                if (! Schema::hasColumn('medicines', 'medicine_formula_id')) {
                    $table->foreignId('medicine_formula_id')->nullable()->after('medicine_country_id')->constrained('medicine_formulas')->nullOnDelete();
                }
                if (! Schema::hasColumn('medicines', 'medicine_gram_ml_id')) {
                    $table->foreignId('medicine_gram_ml_id')->nullable()->after('medicine_formula_id')->constrained('medicine_gram_mls')->nullOnDelete();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                foreach (['medicine_gram_ml_id', 'medicine_formula_id', 'medicine_country_id', 'medicine_type_id'] as $column) {
                    if (Schema::hasColumn('medicines', $column)) {
                        $table->dropConstrainedForeignId($column);
                    }
                }
            });
        }

        Schema::dropIfExists('medicine_gram_mls');
        Schema::dropIfExists('medicine_formulas');
        Schema::dropIfExists('medicine_countries');
    }
};
