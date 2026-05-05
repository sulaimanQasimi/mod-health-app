<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Map i_c_u_s.doctor_id to doctors.id (from legacy user id or existing doctor id) and point FK to doctors.
     */
    public function up(): void
    {
        $iCusForeignKey = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'i_c_u_s'
            AND COLUMN_NAME = 'doctor_id'
            AND REFERENCED_TABLE_NAME IS NOT NULL
            LIMIT 1
        ");

        if (! empty($iCusForeignKey)) {
            DB::statement("ALTER TABLE `i_c_u_s` DROP FOREIGN KEY `{$iCusForeignKey[0]->CONSTRAINT_NAME}`");
        }

        Schema::table('i_c_u_s', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });

        // Normalize to doctor primary key: prefer direct id match, else map via doctors.user_id = legacy user id
        DB::statement('
            UPDATE i_c_u_s i
            INNER JOIN (
                SELECT
                    i2.id,
                    COALESCE(d_by_id.id, d_by_user.id) AS resolved_doctor_id
                FROM i_c_u_s i2
                LEFT JOIN doctors d_by_id ON d_by_id.id = i2.doctor_id
                LEFT JOIN doctors d_by_user ON d_by_user.user_id = i2.doctor_id
            ) x ON x.id = i.id
            SET i.doctor_id = x.resolved_doctor_id
        ');

        // Clear values that are not valid doctor ids
        DB::statement('
            UPDATE i_c_u_s i
            LEFT JOIN doctors d ON d.id = i.doctor_id
            SET i.doctor_id = NULL
            WHERE i.doctor_id IS NOT NULL
            AND d.id IS NULL
        ');

        Schema::table('i_c_u_s', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('i_c_u_s', function (Blueprint $table) {
                $table->dropForeign(['doctor_id']);
            });
        } catch (\Exception $e) {
            $foreignKey = DB::selectOne("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'i_c_u_s'
                AND COLUMN_NAME = 'doctor_id'
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ");

            if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                DB::statement("ALTER TABLE i_c_u_s DROP FOREIGN KEY `{$foreignKey->CONSTRAINT_NAME}`");
            }
        }

        // Map back to user id for legacy FK
        DB::statement('
            UPDATE i_c_u_s i
            INNER JOIN doctors d ON d.id = i.doctor_id
            SET i.doctor_id = d.user_id
        ');

        Schema::table('i_c_u_s', function (Blueprint $table) {
            $table->unsignedBigInteger('doctor_id')->nullable()->change();
        });

        // Clear invalid for users
        DB::statement('
            UPDATE i_c_u_s i
            LEFT JOIN users u ON u.id = i.doctor_id
            SET i.doctor_id = NULL
            WHERE i.doctor_id IS NOT NULL
            AND u.id IS NULL
        ');

        Schema::table('i_c_u_s', function (Blueprint $table) {
            $table->foreign('doctor_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
