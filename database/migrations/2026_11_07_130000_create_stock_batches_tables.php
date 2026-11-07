<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('medicine_id')->nullable();
            $table->unsignedBigInteger('tool_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->string('batch_code');
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->foreignId('source_batch_id')->nullable()->constrained('stock_batches')->nullOnDelete();
            $table->unsignedBigInteger('depot_transaction_id')->nullable();
            $table->unsignedBigInteger('pharmacy_fulfillment_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['owner_type', 'owner_id'], 'stock_batches_owner_index');
            $table->index(['owner_type', 'owner_id', 'medicine_id'], 'stock_batches_owner_medicine_index');
            $table->index(['owner_type', 'owner_id', 'tool_id'], 'stock_batches_owner_tool_index');
            $table->index(['batch_code']);
            $table->foreign('medicine_id')->references('id')->on('medicines')->nullOnDelete();
            $table->foreign('tool_id')->references('id')->on('tools')->nullOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->nullOnDelete();
            $table->foreign('depot_transaction_id')->references('id')->on('depot_transactions')->nullOnDelete();
            $table->foreign('pharmacy_fulfillment_id')->references('id')->on('pharmacy_fulfillments')->nullOnDelete();
        });

        Schema::create('prescription_item_batch_allocations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_item_id');
            $table->unsignedBigInteger('stock_batch_id');
            $table->unsignedInteger('quantity');
            $table->decimal('unit_sale_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedBigInteger('invoice_item_id')->nullable();
            $table->timestamps();

            $table->foreign('prescription_item_id', 'rx_item_batch_alloc_item_fk')
                ->references('id')->on('prescription_items')->cascadeOnDelete();
            $table->foreign('stock_batch_id', 'rx_item_batch_alloc_batch_fk')
                ->references('id')->on('stock_batches')->restrictOnDelete();
            $table->foreign('invoice_item_id', 'rx_item_batch_alloc_invoice_item_fk')
                ->references('id')->on('invoice_items')->nullOnDelete();
            $table->index(['prescription_item_id'], 'rx_item_batch_alloc_item_index');
        });

        Schema::table('depot_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('depot_transactions', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->default(0)->after('quantity');
            }
            if (! Schema::hasColumn('depot_transactions', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->default(0)->after('purchase_price');
            }
            if (! Schema::hasColumn('depot_transactions', 'stock_batch_id')) {
                $table->unsignedBigInteger('stock_batch_id')->nullable()->after('batch_number');
                $table->foreign('stock_batch_id')->references('id')->on('stock_batches')->nullOnDelete();
            }
        });

        Schema::table('depot_request_items', function (Blueprint $table) {
            if (! Schema::hasColumn('depot_request_items', 'stock_batch_id')) {
                $table->unsignedBigInteger('stock_batch_id')->nullable()->after('batch_number');
                $table->foreign('stock_batch_id')->references('id')->on('stock_batches')->nullOnDelete();
            }
            if (! Schema::hasColumn('depot_request_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('stock_batch_id');
            }
            if (! Schema::hasColumn('depot_request_items', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->nullable()->after('expiry_date');
            }
            if (! Schema::hasColumn('depot_request_items', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->nullable()->after('purchase_price');
            }
        });

        Schema::table('pharmacy_fulfillments', function (Blueprint $table) {
            if (! Schema::hasColumn('pharmacy_fulfillments', 'batch_code')) {
                $table->string('batch_code')->nullable()->after('amount');
            }
            if (! Schema::hasColumn('pharmacy_fulfillments', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('batch_code');
            }
            if (! Schema::hasColumn('pharmacy_fulfillments', 'purchase_price')) {
                $table->decimal('purchase_price', 12, 2)->default(0)->after('expiry_date');
            }
            if (! Schema::hasColumn('pharmacy_fulfillments', 'sale_price')) {
                $table->decimal('sale_price', 12, 2)->default(0)->after('purchase_price');
            }
            if (! Schema::hasColumn('pharmacy_fulfillments', 'stock_batch_id')) {
                $table->unsignedBigInteger('stock_batch_id')->nullable()->after('sale_price');
                $table->foreign('stock_batch_id')->references('id')->on('stock_batches')->nullOnDelete();
            }
        });

        // Best-effort backfill: one pharmacy batch per historical fulfillment (no source depot link).
        if (Schema::hasTable('pharmacy_fulfillments')) {
            $rows = DB::table('pharmacy_fulfillments')
                ->whereNull('deleted_at')
                ->whereNotNull('pharmacy_id')
                ->whereNotNull('medicine_id')
                ->orderBy('id')
                ->get(['id', 'pharmacy_id', 'medicine_id', 'amount', 'date']);

            foreach ($rows as $row) {
                $qty = max(0, (int) $row->amount);
                if ($qty <= 0) {
                    continue;
                }

                $batchId = DB::table('stock_batches')->insertGetId([
                    'owner_type' => 'App\\Models\\Pharmacy',
                    'owner_id' => $row->pharmacy_id,
                    'medicine_id' => $row->medicine_id,
                    'tool_id' => null,
                    'unit_id' => null,
                    'batch_code' => 'LEGACY-'.$row->id,
                    'expiry_date' => null,
                    'qty_on_hand' => $qty,
                    'purchase_price' => 0,
                    'sale_price' => 0,
                    'source_batch_id' => null,
                    'depot_transaction_id' => null,
                    'pharmacy_fulfillment_id' => $row->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('pharmacy_fulfillments')
                    ->where('id', $row->id)
                    ->update([
                        'batch_code' => 'LEGACY-'.$row->id,
                        'stock_batch_id' => $batchId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('pharmacy_fulfillments', function (Blueprint $table) {
            if (Schema::hasColumn('pharmacy_fulfillments', 'stock_batch_id')) {
                $table->dropForeign(['stock_batch_id']);
                $table->dropColumn('stock_batch_id');
            }
            foreach (['sale_price', 'purchase_price', 'expiry_date', 'batch_code'] as $column) {
                if (Schema::hasColumn('pharmacy_fulfillments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('depot_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('depot_request_items', 'stock_batch_id')) {
                $table->dropForeign(['stock_batch_id']);
                $table->dropColumn('stock_batch_id');
            }
            foreach (['sale_price', 'purchase_price', 'expiry_date'] as $column) {
                if (Schema::hasColumn('depot_request_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('depot_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('depot_transactions', 'stock_batch_id')) {
                $table->dropForeign(['stock_batch_id']);
                $table->dropColumn('stock_batch_id');
            }
            foreach (['sale_price', 'purchase_price'] as $column) {
                if (Schema::hasColumn('depot_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('prescription_item_batch_allocations');
        Schema::dropIfExists('stock_batches');
    }
};
