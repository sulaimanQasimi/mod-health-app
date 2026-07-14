<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('foreign_country_referral_items', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('foreign_country_referral_id');

            $table->foreign('doctor_id', 'fcr_items_doctor_fk')
                ->references('id')
                ->on('doctors')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('foreign_country_referral_items', function (Blueprint $table) {
            $table->dropForeign('fcr_items_doctor_fk');
            $table->dropColumn('doctor_id');
        });
    }
};
