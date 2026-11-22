<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
        });

        if (Schema::hasTable('purchases') && ! Schema::hasColumn('purchases', 'supplier_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->foreignId('supplier_id')->nullable()->after('depot_id')->constrained('suppliers')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('purchases') && Schema::hasColumn('purchases', 'supplier_id')) {
            Schema::table('purchases', function (Blueprint $table) {
                $table->dropConstrainedForeignId('supplier_id');
            });
        }

        Schema::dropIfExists('suppliers');
    }
};
