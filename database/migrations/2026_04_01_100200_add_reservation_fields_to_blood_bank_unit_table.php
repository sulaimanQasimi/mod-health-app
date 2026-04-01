<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blood_bank_unit')) {
            return;
        }

        Schema::table('blood_bank_unit', function (Blueprint $table) {
            if (! Schema::hasColumn('blood_bank_unit', 'reserved_at')) {
                $table->timestamp('reserved_at')->nullable()->after('blood_unit_id');
            }
            if (! Schema::hasColumn('blood_bank_unit', 'reserved_by')) {
                $table->unsignedBigInteger('reserved_by')->nullable()->after('reserved_at');
            }
            if (! Schema::hasColumn('blood_bank_unit', 'crossmatch_id')) {
                $table->unsignedBigInteger('crossmatch_id')->nullable()->after('reserved_by');
            }
        });

        Schema::table('blood_bank_unit', function (Blueprint $table) {
            if (Schema::hasColumn('blood_bank_unit', 'reserved_by')) {
                $table->foreign('reserved_by')->references('id')->on('users');
            }
            if (Schema::hasColumn('blood_bank_unit', 'crossmatch_id')) {
                $table->foreign('crossmatch_id')->references('id')->on('blood_crossmatches')->nullOnDelete();
            }
            $table->index(['blood_bank_id', 'reserved_at'], 'blood_bank_unit_reserved_idx');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_bank_unit')) {
            return;
        }

        Schema::table('blood_bank_unit', function (Blueprint $table) {
            if (Schema::hasColumn('blood_bank_unit', 'crossmatch_id')) {
                $table->dropForeign(['crossmatch_id']);
            }
            if (Schema::hasColumn('blood_bank_unit', 'reserved_by')) {
                $table->dropForeign(['reserved_by']);
            }
            $table->dropIndex('blood_bank_unit_reserved_idx');
            if (Schema::hasColumn('blood_bank_unit', 'crossmatch_id')) {
                $table->dropColumn('crossmatch_id');
            }
            if (Schema::hasColumn('blood_bank_unit', 'reserved_by')) {
                $table->dropColumn('reserved_by');
            }
            if (Schema::hasColumn('blood_bank_unit', 'reserved_at')) {
                $table->dropColumn('reserved_at');
            }
        });
    }
};
