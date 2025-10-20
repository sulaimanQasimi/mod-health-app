<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            // Ensure foreign keys are the correct type
            if (!Schema::hasColumn('lab_test_parameters', 'testcategory_id')) {
                $table->unsignedBigInteger('testcategory_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'test_id')) {
                $table->unsignedBigInteger('test_id')->nullable()->after('testcategory_id');
            }

            if (!Schema::hasColumn('lab_test_parameters', 'parameter_name')) {
                $table->string('parameter_name')->after('test_id');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'unit')) {
                $table->string('unit')->nullable()->after('parameter_name');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'normal_range')) {
                $table->string('normal_range')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'critical_low')) {
                $table->string('critical_low')->nullable()->after('normal_range');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'critical_high')) {
                $table->string('critical_high')->nullable()->after('critical_low');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'panic_low')) {
                $table->string('panic_low')->nullable()->after('critical_high');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'panic_high')) {
                $table->string('panic_high')->nullable()->after('panic_low');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'delta_check_enabled')) {
                $table->boolean('delta_check_enabled')->default(false)->after('panic_high');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'delta_check_threshold')) {
                $table->string('delta_check_threshold')->nullable()->after('delta_check_enabled');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'critical_comment')) {
                $table->text('critical_comment')->nullable()->after('delta_check_threshold');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'panic_comment')) {
                $table->text('panic_comment')->nullable()->after('critical_comment');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'requires_verification')) {
                $table->boolean('requires_verification')->default(false)->after('panic_comment');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'verification_level')) {
                $table->string('verification_level')->nullable()->after('requires_verification');
            }
            if (!Schema::hasColumn('lab_test_parameters', 'result')) {
                $table->string('result')->nullable()->after('verification_level');
            }
            // created_at/updated_at columns typically already exist via timestamps()
            if (!Schema::hasColumn('lab_test_parameters', 'created_at') && !Schema::hasColumn('lab_test_parameters', 'updated_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('lab_test_parameters', function (Blueprint $table) {
            // We won't drop FK columns, only the newly added data columns to be safe
            if (Schema::hasColumn('lab_test_parameters', 'critical_low')) {
                $table->dropColumn('critical_low');
            }
            if (Schema::hasColumn('lab_test_parameters', 'critical_high')) {
                $table->dropColumn('critical_high');
            }
            if (Schema::hasColumn('lab_test_parameters', 'panic_low')) {
                $table->dropColumn('panic_low');
            }
            if (Schema::hasColumn('lab_test_parameters', 'panic_high')) {
                $table->dropColumn('panic_high');
            }
            if (Schema::hasColumn('lab_test_parameters', 'delta_check_enabled')) {
                $table->dropColumn('delta_check_enabled');
            }
            if (Schema::hasColumn('lab_test_parameters', 'delta_check_threshold')) {
                $table->dropColumn('delta_check_threshold');
            }
            if (Schema::hasColumn('lab_test_parameters', 'critical_comment')) {
                $table->dropColumn('critical_comment');
            }
            if (Schema::hasColumn('lab_test_parameters', 'panic_comment')) {
                $table->dropColumn('panic_comment');
            }
            if (Schema::hasColumn('lab_test_parameters', 'requires_verification')) {
                $table->dropColumn('requires_verification');
            }
            if (Schema::hasColumn('lab_test_parameters', 'verification_level')) {
                $table->dropColumn('verification_level');
            }
            if (Schema::hasColumn('lab_test_parameters', 'result')) {
                $table->dropColumn('result');
            }
        });
    }
};
