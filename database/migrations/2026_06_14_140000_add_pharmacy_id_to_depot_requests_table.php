<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depot_requests', function (Blueprint $table) {
            $table->dropForeign(['requesting_depot_id']);
        });

        DB::statement('ALTER TABLE depot_requests MODIFY requesting_depot_id BIGINT UNSIGNED NULL');

        Schema::table('depot_requests', function (Blueprint $table) {
            $table->foreign('requesting_depot_id')->references('id')->on('depots')->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->nullable()->after('requesting_depot_id')->constrained('pharmacies')->nullOnDelete();
            $table->index(['pharmacy_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('depot_requests', function (Blueprint $table) {
            $table->dropForeign(['requesting_depot_id']);
            $table->dropForeign(['pharmacy_id']);
            $table->dropIndex(['pharmacy_id', 'status']);
            $table->dropColumn('pharmacy_id');
        });

        DB::statement('ALTER TABLE depot_requests MODIFY requesting_depot_id BIGINT UNSIGNED NOT NULL');

        Schema::table('depot_requests', function (Blueprint $table) {
            $table->foreign('requesting_depot_id')->references('id')->on('depots')->cascadeOnDelete();
        });
    }
};
