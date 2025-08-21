<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        // Create incomes table
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->integer('amount');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_contact')->nullable();
            $table->decimal('purchase_price', 10, 2)->default(0.00);
            $table->date('purchase_date')->nullable();
            $table->string('invoice_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('income_type', ['purchase', 'return', 'donation', 'transfer', 'adjustment'])->default('purchase');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['medicine_id']);
            $table->index(['batch_number']);
            $table->index(['expiry_date']);
            $table->index(['purchase_date']);
            $table->index(['income_type']);
        });

        // Create outcomes table
        Schema::create('outcomes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->integer('amount');
            $table->unsignedBigInteger('prescription_item_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->enum('outcome_type', ['prescription', 'expired', 'damaged', 'lost', 'return', 'adjustment'])->default('prescription');
            $table->string('batch_number')->nullable();
            $table->text('reason')->nullable();
            $table->date('outcome_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('prescription_item_id')->references('id')->on('prescription_items')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('set null');
            $table->foreign('doctor_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            // Indexes for better performance
            $table->index(['medicine_id']);
            $table->index(['prescription_item_id']);
            $table->index(['patient_id']);
            $table->index(['doctor_id']);
            $table->index(['outcome_date']);
            $table->index(['outcome_type']);
            $table->index(['batch_number']);
        });

        // Drop the view if it exists
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');

        // Create prescription_stocks view that calculates from income and outcome
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('outcomes');
        Schema::dropIfExists('incomes');
        DB::statement('DROP VIEW IF EXISTS prescription_stocks');
    }
};
