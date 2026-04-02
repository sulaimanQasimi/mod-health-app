<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'prosthetic_attachments',
            'prosthetic_follow_ups',
            'prosthetic_deliveries',
            'prosthetic_fitting_sessions',
            'prosthetic_stock_movements',
            'prosthetic_stock_balances',
            'prosthetic_work_orders',
            'prosthetic_estimates',
            'prosthetic_prescription_lines',
            'prosthetic_prescriptions',
            'prosthetic_component_catalog',
            'prosthetic_measurements',
            'prosthetic_measurement_sets',
            'prosthetic_assessments',
            'prosthetic_cases',
            'prosthetic_referrals',
        ] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        //
    }
};
