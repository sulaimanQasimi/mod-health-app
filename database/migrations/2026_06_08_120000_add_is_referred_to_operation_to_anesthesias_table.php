<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anesthesias', function (Blueprint $table) {
            $table->boolean('is_referred_to_operation')->default(false)->after('is_operation_approved');
        });

        DB::table('anesthesias')
            ->where('status', 'approved')
            ->update(['is_referred_to_operation' => true]);
    }

    public function down(): void
    {
        Schema::table('anesthesias', function (Blueprint $table) {
            $table->dropColumn('is_referred_to_operation');
        });
    }
};
