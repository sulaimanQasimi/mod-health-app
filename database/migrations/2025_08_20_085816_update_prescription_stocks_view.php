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
        // Drop the existing view
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');

        // Create an enhanced prescription_stocks view
        DB::statement("
            CREATE VIEW prescription_stocks AS
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                mt.name as medicine_type_name,
                COALESCE(SUM(CASE WHEN i.deleted_at IS NULL THEN i.amount ELSE 0 END), 0) as total_income,
                COALESCE(SUM(CASE WHEN o.deleted_at IS NULL THEN o.amount ELSE 0 END), 0) as total_outcome,
                (COALESCE(SUM(CASE WHEN i.deleted_at IS NULL THEN i.amount ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN o.deleted_at IS NULL THEN o.amount ELSE 0 END), 0)) as current_stock,
                (COALESCE(SUM(CASE WHEN i.deleted_at IS NULL THEN i.amount ELSE 0 END), 0) - 
                 COALESCE(SUM(CASE WHEN o.deleted_at IS NULL THEN o.amount ELSE 0 END), 0)) as available_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                COALESCE(SUM(CASE WHEN i.deleted_at IS NULL AND i.expiry_date >= CURDATE() THEN i.amount ELSE 0 END), 0) as valid_stock,
                COALESCE(SUM(CASE WHEN i.deleted_at IS NULL AND i.expiry_date < CURDATE() THEN i.amount ELSE 0 END), 0) as expired_stock,
                COALESCE(SUM(CASE WHEN i.deleted_at IS NULL AND i.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN i.amount ELSE 0 END), 0) as expiring_soon_stock,
                COUNT(DISTINCT CASE WHEN i.deleted_at IS NULL THEN i.batch_number END) as total_batches,
                COUNT(DISTINCT CASE WHEN i.deleted_at IS NULL AND i.expiry_date >= CURDATE() THEN i.batch_number END) as valid_batches,
                COUNT(DISTINCT CASE WHEN i.deleted_at IS NULL AND i.expiry_date < CURDATE() THEN i.batch_number END) as expired_batches,
                MIN(CASE WHEN i.deleted_at IS NULL AND i.expiry_date >= CURDATE() THEN i.expiry_date END) as earliest_expiry,
                MAX(CASE WHEN i.deleted_at IS NULL THEN i.purchase_date END) as last_purchase_date,
                MAX(CASE WHEN o.deleted_at IS NULL THEN o.outcome_date END) as last_outcome_date,
                NOW() as last_updated,
                'Auto-calculated from income and outcome records' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            LEFT JOIN medicine_types mt ON m.medicine_type_id = mt.id
            LEFT JOIN incomes i ON m.id = i.medicine_id
            LEFT JOIN outcomes o ON m.id = o.medicine_id
            GROUP BY m.id, m.name, mt.name, m.created_at, m.updated_at
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the enhanced view
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');

        // Recreate the original simple view
        DB::statement("
            CREATE VIEW prescription_stocks AS
            SELECT 
                m.id as medicine_id,
                m.name as medicine_name,
                COALESCE(SUM(i.amount), 0) as total_income,
                COALESCE(SUM(o.amount), 0) as total_outcome,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as current_stock,
                (COALESCE(SUM(i.amount), 0) - COALESCE(SUM(o.amount), 0)) as available_stock,
                0 as reserved_stock,
                10 as minimum_stock,
                1000 as maximum_stock,
                NOW() as last_updated,
                'Auto-calculated from income and outcome' as notes,
                m.created_at,
                m.updated_at
            FROM medicines m
            LEFT JOIN incomes i ON m.id = i.medicine_id AND i.deleted_at IS NULL
            LEFT JOIN outcomes o ON m.id = o.medicine_id AND o.deleted_at IS NULL
            GROUP BY m.id, m.name, m.created_at, m.updated_at
        ");
    }
};
