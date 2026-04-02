<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blood_donors') || Schema::hasColumn('blood_donors', 'military_department')) {
            return;
        }

        Schema::table('blood_donors', function (Blueprint $table) {
            $table->string('military_department', 255)->nullable()->after('receiver');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_donors') || ! Schema::hasColumn('blood_donors', 'military_department')) {
            return;
        }

        Schema::table('blood_donors', function (Blueprint $table) {
            $table->dropColumn('military_department');
        });
    }
};
