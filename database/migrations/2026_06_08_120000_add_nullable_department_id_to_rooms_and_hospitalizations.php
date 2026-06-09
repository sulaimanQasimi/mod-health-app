<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable()->change();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
        });

        DB::table('hospitalizations as h')
            ->join('appointments as a', 'h.appointment_id', '=', 'a.id')
            ->whereNull('h.department_id')
            ->whereNotNull('a.department_id')
            ->update(['h.department_id' => DB::raw('a.department_id')]);
    }

    public function down(): void
    {
        Schema::table('hospitalizations', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->unsignedBigInteger('department_id')->nullable(false)->change();
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }
};
