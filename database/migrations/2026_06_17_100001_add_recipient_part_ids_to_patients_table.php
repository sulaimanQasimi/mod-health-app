<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->foreignId('recipient_part_id')
                ->nullable()
                ->after('referred_by')
                ->constrained('recipient_parts')
                ->nullOnDelete();
            $table->foreignId('referral_recipient_part_id')
                ->nullable()
                ->after('referral_recipient')
                ->constrained('recipient_parts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_part_id');
            $table->dropConstrainedForeignId('referral_recipient_part_id');
        });
    }
};
