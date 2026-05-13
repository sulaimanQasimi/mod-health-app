<?php

use App\Models\Depot;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\Pharmacy;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('depot_transactions');
        Schema::create('depot_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignIdFor(Depot::class, 'depot_id')->constrained('depots');
            $table->foreignIdFor(User::class, 'user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(Pharmacy::class, 'pharmacy_id')->nullable()->constrained('pharmacies')->nullOnDelete();

            // Basic medicine and batch info
            $table->foreignIdFor(MedicineType::class, 'medicine_type_id')->nullable()->constrained('medicine_types')->nullOnDelete();
            $table->foreignIdFor(Medicine::class, 'medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->foreignIdFor(Tool::class, 'tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->foreignIdFor(Unit::class, 'unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('batch_number')->nullable();

            // Transaction and relation info
            $table->nullableMorphs('transactionable');
            $table->enum('transaction_type', ['in', 'out', 'transfer'])->default('in');
            $table->enum('type', ['depot_to_depot', 'depot_to_pharmacy', 'stock_in', 'stock_out', 'adjustment'])->default('stock_in');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            $table->unsignedInteger('quantity');

            // Depot involvement
            $table->foreignIdFor(Depot::class, 'from_depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignIdFor(Depot::class, 'to_depot_id')->nullable()->constrained('depots')->nullOnDelete();

            // Dates
            $table->date('transaction_date');
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Other fields
            $table->text('notes')->nullable();

            // User tracking and timestamps
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('depot_id');
            $table->index('from_depot_id');
            $table->index('to_depot_id');
            $table->index('pharmacy_id');
            $table->index('medicine_id');
            $table->index('tool_id');
            $table->index('type');
            $table->index('status');
            $table->index('created_by');
            $table->index('transaction_date');
            $table->index(['from_depot_id', 'medicine_id', 'status'], 'depot_tx_source_item_status_idx');
            $table->index(['to_depot_id', 'medicine_id', 'status'], 'depot_tx_dest_item_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_transactions');
    }
};
