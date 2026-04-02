<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prosthetic_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('referral_number')->unique();
            $table->string('status')->default('drafted')->index();
            $table->date('referral_date');
            $table->string('referring_facility')->nullable();
            $table->string('referring_doctor')->nullable();
            $table->text('reason')->nullable();
            $table->text('diagnosis_summary')->nullable();
            $table->string('urgency')->default('routine');
            $table->string('requested_service_type')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignId('referral_id')->nullable()->constrained('prosthetic_referrals')->nullOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->string('case_number')->unique();
            $table->string('status')->default('new')->index();
            $table->string('side')->default('left');
            $table->string('body_region')->nullable();
            $table->string('case_category')->default('prosthetic');
            $table->string('device_type')->nullable();
            $table->text('primary_diagnosis')->nullable();
            $table->text('secondary_diagnosis')->nullable();
            $table->text('cause_of_loss_notes')->nullable();
            $table->date('injury_surgery_onset_date')->nullable();
            $table->string('amputation_level')->nullable();
            $table->string('priority')->default('normal');
            $table->string('first_time_or_replacement')->default('first_time');
            $table->text('prior_device_history')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('prosthetic_referrals', function (Blueprint $table) {
            $table->unsignedBigInteger('converted_case_id')->nullable()->after('document_path');
            $table->foreign('converted_case_id')->references('id')->on('prosthetic_cases')->nullOnDelete();
        });

        Schema::create('prosthetic_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->string('fit_outcome')->default('pending');
            $table->text('history_present_condition')->nullable();
            $table->text('surgical_history')->nullable();
            $table->text('comorbidities')->nullable();
            $table->text('medications')->nullable();
            $table->text('allergies')->nullable();
            $table->text('skin_stump_notes')->nullable();
            $table->text('functional_goals')->nullable();
            $table->text('psychosocial_notes')->nullable();
            $table->json('extra_clinical')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_measurement_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->unsignedBigInteger('locked_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_measurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosthetic_measurement_set_id');
            $table->foreign('prosthetic_measurement_set_id', 'fk_pm_set')->references('id')->on('prosthetic_measurement_sets')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('value_numeric', 12, 4)->nullable();
            $table->string('value_text')->nullable();
            $table->string('unit')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_component_catalog', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('name');
            $table->string('local_name')->nullable();
            $table->string('category')->nullable()->index();
            $table->string('subcategory')->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure')->default('unit');
            $table->decimal('standard_cost', 14, 2)->default(0);
            $table->unsignedInteger('minimum_stock')->default(0);
            $table->boolean('tracks_serial')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->string('device_timing')->default('definitive');
            $table->text('target_functionality')->nullable();
            $table->text('suspension_notes')->nullable();
            $table->string('socket_type')->nullable();
            $table->string('liner_type')->nullable();
            $table->string('foot_type')->nullable();
            $table->text('special_instructions')->nullable();
            $table->text('clinical_justification')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_prescription_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosthetic_prescription_id');
            $table->foreign('prosthetic_prescription_id', 'fk_ppl_rx')->references('id')->on('prosthetic_prescriptions')->cascadeOnDelete();
            $table->unsignedBigInteger('prosthetic_component_catalog_id');
            $table->foreign('prosthetic_component_catalog_id', 'fk_ppl_cat')->references('id')->on('prosthetic_component_catalog')->restrictOnDelete();
            $table->decimal('quantity', 12, 3)->default(1);
            $table->decimal('unit_cost_snapshot', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_estimates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->foreignId('prosthetic_prescription_id')->nullable()->constrained('prosthetic_prescriptions')->nullOnDelete();
            $table->string('currency', 8)->default('AFN');
            $table->decimal('parts_total', 14, 2)->default(0);
            $table->decimal('labor_total', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('approval_notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->unique();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->foreignId('prosthetic_prescription_id')->nullable()->constrained('prosthetic_prescriptions')->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('production_stage')->default('pending');
            $table->unsignedBigInteger('technician_user_id')->nullable();
            $table->date('planned_start_date')->nullable();
            $table->date('planned_end_date')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_stock_balances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosthetic_component_catalog_id');
            $table->foreign('prosthetic_component_catalog_id', 'fk_psb_cat')->references('id')->on('prosthetic_component_catalog')->cascadeOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->decimal('quantity', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['prosthetic_component_catalog_id', 'branch_id'], 'prosthetic_stock_cat_branch_unique');
        });

        Schema::create('prosthetic_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prosthetic_component_catalog_id');
            $table->foreign('prosthetic_component_catalog_id', 'fk_psm_cat')->references('id')->on('prosthetic_component_catalog')->restrictOnDelete();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->unsignedBigInteger('prosthetic_work_order_id')->nullable();
            $table->foreign('prosthetic_work_order_id', 'fk_psm_wo')->references('id')->on('prosthetic_work_orders')->nullOnDelete();
            $table->string('movement_type');
            $table->decimal('quantity_delta', 14, 3);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_fitting_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->foreignId('prosthetic_work_order_id')->nullable()->constrained('prosthetic_work_orders')->nullOnDelete();
            $table->date('session_date');
            $table->string('outcome')->default('pending');
            $table->unsignedTinyInteger('comfort_score')->nullable();
            $table->text('issues_identified')->nullable();
            $table->text('modifications_required')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->dateTime('delivered_at');
            $table->string('received_by_name')->nullable();
            $table->text('device_serial_notes')->nullable();
            $table->text('instructions_explained')->nullable();
            $table->date('warranty_until')->nullable();
            $table->date('follow_up_scheduled_at')->nullable();
            $table->boolean('handover_signed')->default(false);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prosthetic_case_id')->constrained('prosthetic_cases')->cascadeOnDelete();
            $table->string('follow_up_type')->default('1_month');
            $table->date('scheduled_at');
            $table->date('completed_at')->nullable();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('prosthetic_attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->string('category')->default('general');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prosthetic_attachments');
        Schema::dropIfExists('prosthetic_follow_ups');
        Schema::dropIfExists('prosthetic_deliveries');
        Schema::dropIfExists('prosthetic_fitting_sessions');
        Schema::dropIfExists('prosthetic_stock_movements');
        Schema::dropIfExists('prosthetic_stock_balances');
        Schema::dropIfExists('prosthetic_work_orders');
        Schema::dropIfExists('prosthetic_estimates');
        Schema::dropIfExists('prosthetic_prescription_lines');
        Schema::dropIfExists('prosthetic_prescriptions');
        Schema::dropIfExists('prosthetic_component_catalog');
        Schema::dropIfExists('prosthetic_measurements');
        Schema::dropIfExists('prosthetic_measurement_sets');
        Schema::dropIfExists('prosthetic_assessments');
        Schema::table('prosthetic_referrals', function (Blueprint $table) {
            $table->dropForeign(['converted_case_id']);
        });
        Schema::dropIfExists('prosthetic_cases');
        Schema::dropIfExists('prosthetic_referrals');
    }
};

