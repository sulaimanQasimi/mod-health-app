<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * From-scratch / upgrade path:
     * - Fee lives on doctors (not departments)
     * - Patient job field removed
     * - Patient id_card remains as string/text (auto-filled as Patient-{id} in app)
     */
    public function up(): void
    {
        if (! Schema::hasColumn('doctors', 'price')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->decimal('price', 12, 2)->nullable()->default(0)->after('department_id');
            });
        }

        if (Schema::hasColumn('departments', 'price') && Schema::hasColumn('doctors', 'price')) {
            DB::statement('
                UPDATE doctors
                INNER JOIN departments ON departments.id = doctors.department_id
                SET doctors.price = COALESCE(departments.price, 0)
                WHERE doctors.price IS NULL OR doctors.price = 0
            ');
        }

        if (Schema::hasColumn('departments', 'price')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        if (Schema::hasColumn('patients', 'job')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropColumn('job');
            });
        }

        if (Schema::hasColumn('patients', 'id_card')) {
            DB::statement("UPDATE patients SET id_card = CONCAT('Patient-', id)");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('departments', 'price')) {
            Schema::table('departments', function (Blueprint $table) {
                $table->decimal('price', 12, 2)->nullable()->default(0);
            });
        }

        if (Schema::hasColumn('doctors', 'price') && Schema::hasColumn('departments', 'price')) {
            DB::statement('
                UPDATE departments
                INNER JOIN (
                    SELECT department_id, MAX(price) AS price
                    FROM doctors
                    WHERE deleted_at IS NULL
                    GROUP BY department_id
                ) doctor_prices ON doctor_prices.department_id = departments.id
                SET departments.price = COALESCE(doctor_prices.price, 0)
            ');
        }

        if (Schema::hasColumn('doctors', 'price')) {
            Schema::table('doctors', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        }

        if (! Schema::hasColumn('patients', 'job')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->string('job')->nullable()->after('job_type');
            });
        }
    }
};
