<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                if (! Schema::hasColumn('medicines', 'unit_id')) {
                    $table->foreignId('unit_id')->nullable()->after('name')->constrained('units')->nullOnDelete();
                }
                if (! Schema::hasColumn('medicines', 'units_per_pack')) {
                    $table->unsignedInteger('units_per_pack')->default(1)->after('unit_id');
                }
            });
        }

        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number');
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->unsignedInteger('units_per_pack')->default(1);
            $table->string('location_type', 32); // depot|pharmacy
            $table->unsignedBigInteger('location_id');
            $table->unsignedInteger('qty_on_hand')->default(0);
            $table->unsignedInteger('qty_sold')->default(0);
            $table->date('expiry_date')->nullable();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('unit_sale_price', 12, 2)->default(0);
            $table->decimal('pack_price', 12, 2)->default(0);
            $table->unsignedBigInteger('purchase_item_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['batch_number', 'location_type', 'location_id'], 'stocks_batch_location_unique');
            $table->index(['location_type', 'location_id']);
            $table->index(['medicine_id', 'batch_number']);
            $table->index('expiry_date');
        });

        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->foreignId('depot_id')->constrained('depots')->cascadeOnDelete();
            $table->string('supplier')->nullable();
            $table->date('purchase_date');
            $table->string('status', 32)->default('draft'); // draft|received|cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['depot_id', 'status']);
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('batch_number');
            $table->date('expiry_date')->nullable();
            $table->unsignedInteger('packs')->default(0);
            $table->unsignedInteger('units')->default(0);
            $table->unsignedInteger('qty_units');
            $table->unsignedInteger('units_per_pack')->default(1);
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('unit_sale_price', 12, 2)->default(0);
            $table->decimal('pack_price', 12, 2)->default(0);
            $table->foreignId('stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->timestamps();

            $table->unique('batch_number');
            $table->index(['purchase_id', 'medicine_id']);
        });

        Schema::table('stocks', function (Blueprint $table) {
            $table->foreign('purchase_item_id')
                ->references('id')
                ->on('purchase_items')
                ->nullOnDelete();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('type', 64); // purchase_receive|depot_to_pharmacy|depot_to_depot|sale|adjustment
            $table->string('batch_number');
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('from_location_type', 32)->nullable();
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->string('to_location_type', 32)->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->foreignId('from_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('to_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->unsignedInteger('qty_units');
            $table->nullableMorphs('reference');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['batch_number', 'type']);
        });

        Schema::create('depot_transfer_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('source_depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignId('pharmacy_id')->nullable()->constrained('pharmacies')->nullOnDelete();
            $table->foreignId('destination_depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->string('status', 32)->default('draft'); // draft|pending|approved|rejected|fulfilled|cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('fulfilled_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['source_depot_id', 'status']);
            $table->index(['pharmacy_id', 'status']);
        });

        Schema::create('depot_transfer_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('depot_transfer_request_id')->constrained('depot_transfer_requests')->cascadeOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('batch_number')->nullable();
            $table->unsignedInteger('packs')->default(0);
            $table->unsignedInteger('units')->default(0);
            $table->unsignedInteger('qty_units');
            $table->unsignedInteger('units_per_pack')->default(1);
            $table->foreignId('from_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->foreignId('to_stock_id')->nullable()->constrained('stocks')->nullOnDelete();
            $table->timestamps();

            $table->index('depot_transfer_request_id', 'dtri_request_id_index');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_item_id')->nullable()->constrained('prescription_items')->nullOnDelete();
            $table->foreignId('stock_id')->constrained('stocks')->restrictOnDelete();
            $table->foreignId('medicine_id')->constrained('medicines')->restrictOnDelete();
            $table->string('batch_number');
            $table->unsignedInteger('packs')->default(1);
            $table->unsignedInteger('units')->default(0);
            $table->unsignedInteger('qty_units');
            $table->unsignedInteger('units_per_pack')->default(1);
            $table->decimal('unit_sale_price', 12, 2)->default(0);
            $table->decimal('pack_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->foreignId('invoice_item_id')->nullable()->constrained('invoice_items')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['prescription_item_id', 'stock_id']);
            $table->index('batch_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('depot_transfer_request_items');
        Schema::dropIfExists('depot_transfer_requests');
        Schema::dropIfExists('stock_movements');

        Schema::table('stocks', function (Blueprint $table) {
            $table->dropForeign(['purchase_item_id']);
        });

        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('stocks');

        if (Schema::hasTable('medicines')) {
            Schema::table('medicines', function (Blueprint $table) {
                if (Schema::hasColumn('medicines', 'units_per_pack')) {
                    $table->dropColumn('units_per_pack');
                }
                if (Schema::hasColumn('medicines', 'unit_id')) {
                    $table->dropConstrainedForeignId('unit_id');
                }
            });
        }
    }
};
