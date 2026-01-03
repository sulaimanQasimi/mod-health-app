<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum column to include 'completion'
        DB::statement("ALTER TABLE `incomes` MODIFY COLUMN `income_type` ENUM('purchase', 'return', 'donation', 'transfer', 'adjustment', 'completion') DEFAULT 'purchase'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original enum values
        DB::statement("ALTER TABLE `incomes` MODIFY COLUMN `income_type` ENUM('purchase', 'return', 'donation', 'transfer', 'adjustment') DEFAULT 'purchase'");
    }
};

