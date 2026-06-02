<?php

use App\Models\Depot;
use App\Models\DepotTransaction;
use App\Models\Medicine;
use App\Models\Tool;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignIdFor(Depot::class, 'requesting_depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignIdFor(Depot::class, 'source_depot_id')->constrained('depots')->cascadeOnDelete();
            $table->foreignIdFor(Medicine::class, 'medicine_id')->nullable()->constrained('medicines')->nullOnDelete();
            $table->foreignIdFor(Tool::class, 'tool_id')->nullable()->constrained('tools')->nullOnDelete();
            $table->foreignIdFor(Unit::class, 'unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected', 'fulfilled', 'cancelled'])->default('draft');
            $table->foreignIdFor(User::class, 'requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignIdFor(User::class, 'fulfilled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignIdFor(DepotTransaction::class, 'depot_transaction_id')->nullable()->constrained('depot_transactions')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['requesting_depot_id', 'status']);
            $table->index(['source_depot_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_requests');
    }
};
