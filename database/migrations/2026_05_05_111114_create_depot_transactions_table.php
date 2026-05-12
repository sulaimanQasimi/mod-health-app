<?php

use App\Models\Depot;
use App\Models\Medicine;
use App\Models\MedicineType;
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
        Schema::create('depot_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Depot::class, 'depot_id');
            $table->foreignIdFor(User::class, 'user_id')->nullable();

            // Basic medicine and batch info
            $table->foreignIdFor(MedicineType::class, 'medicine_type_id')->nullable();
            $table->foreignIdFor(Medicine::class, 'medicine_id')->nullable();
            $table->foreignIdFor(Tool::class, 'tool_id')->nullable();
            $table->string('batch_number')->nullable();

            // Transaction and relation info
            $table->nullableMorphs('transactionable');
            $table->enum('transaction_type', ['in', 'out','transfer'])->default('in');
            $table->integer('quantity')->default(0);

            // Depot involvement
            $table->foreignIdFor(Depot::class, 'from_depot_id')->nullable();
            $table->foreignIdFor(Depot::class, 'to_depot_id')->nullable();

            // Dates
            $table->date('transaction_date');
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Other fields
            $table->text('notes')->nullable();

            // User tracking and timestamps
            $table->foreignIdFor(User::class, 'created_by')->nullable();
            $table->foreignIdFor(User::class, 'updated_by')->nullable();
            $table->foreignIdFor(User::class, 'deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
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
