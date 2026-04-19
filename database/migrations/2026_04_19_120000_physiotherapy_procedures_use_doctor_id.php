<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->after('physiotherapy_type_id');
        });

        $procedures = DB::table('physiotherapy_procedures')
            ->select('id', 'appointment_id', 'physiotherapist_id')
            ->get();

        foreach ($procedures as $row) {
            $doctorId = DB::table('doctors')->where('user_id', $row->physiotherapist_id)->value('id');
            if (! $doctorId) {
                $branchId = DB::table('appointments')->where('id', $row->appointment_id)->value('branch_id');
                $doctorId = DB::table('doctors')->where('branch_id', $branchId)->orderBy('id')->value('id');
            }
            if (! $doctorId) {
                $doctorId = DB::table('doctors')->orderBy('id')->value('id');
            }
            if (! $doctorId) {
                throw new \RuntimeException(
                    'Cannot migrate physiotherapy_procedures: no doctor records exist. Seed doctors before migrating.'
                );
            }
            DB::table('physiotherapy_procedures')->where('id', $row->id)->update(['doctor_id' => $doctorId]);
        }

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->dropForeign(['physiotherapist_id']);
        });

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->dropColumn('physiotherapist_id');
        });

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
        });

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->unsignedBigInteger('physiotherapist_id')->nullable()->after('physiotherapy_type_id');
        });

        foreach (DB::table('physiotherapy_procedures')->orderBy('id')->get() as $row) {
            $userId = DB::table('doctors')->where('id', $row->doctor_id)->value('user_id')
                ?? DB::table('users')->orderBy('id')->value('id');
            if ($userId) {
                DB::table('physiotherapy_procedures')->where('id', $row->id)->update(['physiotherapist_id' => $userId]);
            }
        }

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->dropColumn('doctor_id');
        });

        Schema::table('physiotherapy_procedures', function (Blueprint $table) {
            $table->foreign('physiotherapist_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
