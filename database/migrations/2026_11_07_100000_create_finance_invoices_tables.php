<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('patient_id');
            $table->nullableMorphs('invoiceable'); // Appointment, Hospitalization, ICU, …
            $table->unsignedBigInteger('branch_id');
            $table->string('status', 32)->default('draft'); // draft|issued|paid|cancelled
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_id')->references('id')->on('patients');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->index(['branch_id', 'status']);
            $table->index(['status', 'invoiceable_type', 'invoiceable_id'], 'invoices_status_invoiceable_index');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->string('item_type', 64); // appointment_fee|lab_test|custom|…
            $table->string('description');
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('amount', 12, 2)->default(0);
            $table->nullableMorphs('billable'); // Appointment, PatientTestRegistration, …
            $table->unsignedBigInteger('lab_type_id')->nullable(); // optional catalog reference
            $table->string('payment_status', 16)->default('unpaid'); // unpaid|paid
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('lab_type_id')->references('id')->on('lab_types')->nullOnDelete();
            $table->index(['invoice_id', 'payment_status']);
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 12, 2);
            $table->string('method', 32)->default('cash'); // cash|card|other
            $table->text('notes')->nullable();
            $table->timestamp('paid_at');
            $table->unsignedBigInteger('paid_by')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('paid_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('payment_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('invoice_id');
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });

        Schema::table('patient_test_registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('patient_test_registrations', 'payment_status')) {
                // Existing rows stay processable; new registrations set unpaid in code.
                $table->string('payment_status', 16)->default('paid')->after('status');
                $table->index('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            if (Schema::hasColumn('patient_test_registrations', 'payment_status')) {
                $table->dropIndex(['payment_status']);
                $table->dropColumn('payment_status');
            }
        });

        Schema::dropIfExists('payment_receipts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
    }
};
