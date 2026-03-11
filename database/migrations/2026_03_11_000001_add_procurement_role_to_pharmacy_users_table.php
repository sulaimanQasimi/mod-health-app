<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL: expand ENUM to include "procurement"
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `pharmacy_users` MODIFY `role` ENUM('manager','staff','procurement','viewer') NOT NULL DEFAULT 'staff'");
        }
    }

    public function down(): void
    {
        // MySQL: revert ENUM (drops procurement values)
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `pharmacy_users` MODIFY `role` ENUM('manager','staff','viewer') NOT NULL DEFAULT 'staff'");
        }
    }
};

