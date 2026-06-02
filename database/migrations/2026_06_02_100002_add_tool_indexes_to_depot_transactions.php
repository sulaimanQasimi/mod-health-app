<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('depot_transactions', function (Blueprint $table) {
            $table->index(['from_depot_id', 'tool_id', 'status'], 'depot_tx_source_tool_status_idx');
            $table->index(['to_depot_id', 'tool_id', 'status'], 'depot_tx_dest_tool_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('depot_transactions', function (Blueprint $table) {
            $table->dropIndex('depot_tx_source_tool_status_idx');
            $table->dropIndex('depot_tx_dest_tool_status_idx');
        });
    }
};
