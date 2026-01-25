<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the prescription_stocks view first
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');

        // Add branch_id column to incomes table
        Schema::table('incomes', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('income_type');
        });

        // Drop foreign key constraint for pharmacy_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['pharmacy_id']);
        });

        // Drop index on pharmacy_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex(['pharmacy_id']);
        });

        // Remove pharmacy_id column
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('pharmacy_id');
        });

        // Add foreign key constraint for branch_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });

        // Add index on branch_id for performance
        Schema::table('incomes', function (Blueprint $table) {
            $table->index(['branch_id']);
        });

        // Recreate prescription_stocks view with branch-based income calculations
        // Note: Income is now branch-based, outcomes remain pharmacy-based
        DB::statement("
            CREATE VIEW prescription_stocks AS
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                b.id as branch_id,
                b.name as branch_name,
                NULL as pharmacy_id,
                NULL as pharmacy_name,
                COALESCE(SUM(CASE WHEN i.branch_id = b.id THEN i.amount ELSE 0 END), 0) as branch_income,
                0 as pharmacy_income,
                0 as pharmacy_outcome,
                COALESCE(SUM(CASE WHEN i.branch_id = b.id THEN i.amount ELSE 0 END), 0) as branch_stock,
                0 as pharmacy_stock,
                COALESCE(SUM(i.amount), 0) as total_income,
                COALESCE(SUM(o.amount), 0) as total_outcome,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as total_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                NOW() as last_updated,
                'Auto-calculated from income and outcome' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            CROSS JOIN branches b
            LEFT JOIN incomes i ON m.id = i.medicine_id AND i.deleted_at IS NULL
            LEFT JOIN outcomes o ON m.id = o.medicine_id AND o.deleted_at IS NULL
            GROUP BY m.id, m.name, b.id, b.name, m.created_at, m.updated_at
            HAVING COALESCE(SUM(CASE WHEN i.branch_id = b.id THEN i.amount ELSE 0 END), 0) > 0
            UNION ALL
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                NULL as branch_id,
                'General Stock' as branch_name,
                NULL as pharmacy_id,
                'General Stock' as pharmacy_name,
                0 as branch_income,
                0 as pharmacy_income,
                0 as pharmacy_outcome,
                0 as branch_stock,
                0 as pharmacy_stock,
                COALESCE(SUM(i.amount), 0) as total_income,
                COALESCE(SUM(o.amount), 0) as total_outcome,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as total_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                NOW() as last_updated,
                'Auto-calculated from income and outcome' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            LEFT JOIN incomes i ON m.id = i.medicine_id AND i.deleted_at IS NULL AND i.branch_id IS NULL
            LEFT JOIN outcomes o ON m.id = o.medicine_id AND o.deleted_at IS NULL AND o.pharmacy_id IS NULL
            GROUP BY m.id, m.name, m.created_at, m.updated_at
            HAVING COALESCE(SUM(i.amount), 0) > 0 OR COALESCE(SUM(o.amount), 0) > 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the prescription_stocks view
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');

        // Drop foreign key constraint and index for branch_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
        });

        // Remove branch_id column
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('branch_id');
        });

        // Add pharmacy_id column back
        Schema::table('incomes', function (Blueprint $table) {
            $table->unsignedBigInteger('pharmacy_id')->nullable()->after('income_type');
        });

        // Add foreign key constraint for pharmacy_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies')->onDelete('set null');
        });

        // Add index on pharmacy_id
        Schema::table('incomes', function (Blueprint $table) {
            $table->index(['pharmacy_id']);
        });

        // Recreate original prescription_stocks view
        DB::statement("
            CREATE VIEW prescription_stocks AS
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                p.id as pharmacy_id,
                p.name as pharmacy_name,
                COALESCE(SUM(CASE WHEN i.pharmacy_id = p.id THEN i.amount ELSE 0 END), 0) as pharmacy_income,
                COALESCE(SUM(CASE WHEN o.pharmacy_id = p.id THEN o.amount ELSE 0 END), 0) as pharmacy_outcome,
                COALESCE(SUM(CASE WHEN i.pharmacy_id = p.id THEN i.amount ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN o.pharmacy_id = p.id THEN o.amount ELSE 0 END), 0) as pharmacy_stock,
                COALESCE(SUM(i.amount), 0) as total_income,
                COALESCE(SUM(o.amount), 0) as total_outcome,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as total_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                NOW() as last_updated,
                'Auto-calculated from income and outcome' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            CROSS JOIN pharmacies p
            LEFT JOIN incomes i ON m.id = i.medicine_id AND i.deleted_at IS NULL
            LEFT JOIN outcomes o ON m.id = o.medicine_id AND o.deleted_at IS NULL
            GROUP BY m.id, m.name, p.id, p.name, m.created_at, m.updated_at
            UNION ALL
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                NULL as pharmacy_id,
                'General Stock' as pharmacy_name,
                0 as pharmacy_income,
                0 as pharmacy_outcome,
                0 as pharmacy_stock,
                COALESCE(SUM(i.amount), 0) as total_income,
                COALESCE(SUM(o.amount), 0) as total_outcome,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as total_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                NOW() as last_updated,
                'Auto-calculated from income and outcome' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            LEFT JOIN incomes i ON m.id = i.medicine_id AND i.deleted_at IS NULL AND i.pharmacy_id IS NULL
            LEFT JOIN outcomes o ON m.id = o.medicine_id AND o.deleted_at IS NULL AND o.pharmacy_id IS NULL
            GROUP BY m.id, m.name, m.created_at, m.updated_at
            HAVING COALESCE(SUM(i.amount), 0) > 0 OR COALESCE(SUM(o.amount), 0) > 0
        ");
    }
};
