<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('blood_banks', 'feature')) {
                $table->enum('feature', ['request', 'phlebotomy'])
                    ->default('request')
                    ->after('status')
                    ->index();
            }
        });

        if (Schema::hasColumn('blood_banks', 'feature')) {
            DB::table('blood_banks')->whereNull('feature')->update(['feature' => 'request']);
        }
    }

    public function down(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (Schema::hasColumn('blood_banks', 'feature')) {
                $table->dropIndex(['feature']);
                $table->dropColumn('feature');
            }
        });
    }
};
