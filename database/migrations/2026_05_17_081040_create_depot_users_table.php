<?php

use App\Models\Depot;
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
        Schema::create('depot_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Depot::class, 'depot_id')->nullable()->constrained('depots')->nullOnDelete();
            $table->foreignIdFor(User::class, 'user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('role')->default('staff');
            $table->json('permissions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['depot_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_users');
    }
};
