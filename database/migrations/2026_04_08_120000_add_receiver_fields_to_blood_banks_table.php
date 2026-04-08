<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (! Schema::hasColumn('blood_banks', 'receiver_department_id')) {
                $table->unsignedBigInteger('receiver_department_id')->nullable()->after('department_id');
                $table->foreign('receiver_department_id')->references('id')->on('departments')->nullOnDelete();
            }
            if (! Schema::hasColumn('blood_banks', 'receiver_nurse_id')) {
                $table->unsignedBigInteger('receiver_nurse_id')->nullable()->after('receiver_department_id');
                $table->foreign('receiver_nurse_id')->references('id')->on('nurses')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('blood_banks', function (Blueprint $table) {
            if (Schema::hasColumn('blood_banks', 'receiver_nurse_id')) {
                $table->dropForeign(['receiver_nurse_id']);
                $table->dropColumn('receiver_nurse_id');
            }
            if (Schema::hasColumn('blood_banks', 'receiver_department_id')) {
                $table->dropForeign(['receiver_department_id']);
                $table->dropColumn('receiver_department_id');
            }
        });
    }
};
