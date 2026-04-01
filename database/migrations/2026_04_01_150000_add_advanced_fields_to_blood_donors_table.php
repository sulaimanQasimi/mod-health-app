<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('blood_donors')) {
            return;
        }

        Schema::table('blood_donors', function (Blueprint $table) {
            if (! Schema::hasColumn('blood_donors', 'age')) {
                $table->unsignedTinyInteger('age')->nullable()->after('father_name');
            }
            if (! Schema::hasColumn('blood_donors', 'blood_pressure')) {
                $table->string('blood_pressure', 50)->nullable()->after('gender');
            }
            if (! Schema::hasColumn('blood_donors', 'comorbidities')) {
                $table->text('comorbidities')->nullable()->after('blood_pressure');
            }
            if (! Schema::hasColumn('blood_donors', 'donor_type')) {
                $table->enum('donor_type', ['civilian', 'military'])->nullable()->after('comorbidities');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('blood_donors')) {
            return;
        }

        Schema::table('blood_donors', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('blood_donors', 'donor_type')) {
                $columns[] = 'donor_type';
            }
            if (Schema::hasColumn('blood_donors', 'comorbidities')) {
                $columns[] = 'comorbidities';
            }
            if (Schema::hasColumn('blood_donors', 'blood_pressure')) {
                $columns[] = 'blood_pressure';
            }
            if (Schema::hasColumn('blood_donors', 'age')) {
                $columns[] = 'age';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
