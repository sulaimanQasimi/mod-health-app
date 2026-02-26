<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'both' to users.clinic_type enum so users can see clinic + hospital everywhere.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN clinic_type ENUM('hospital', 'clinic', 'both') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optionally migrate existing 'both' users to null or 'hospital' before reverting
        DB::table('users')->where('clinic_type', 'both')->update(['clinic_type' => null]);
        DB::statement("ALTER TABLE users MODIFY COLUMN clinic_type ENUM('hospital', 'clinic') NULL");
    }
};
