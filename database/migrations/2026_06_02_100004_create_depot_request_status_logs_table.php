<?php

use App\Models\DepotRequest;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('depot_request_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DepotRequest::class, 'depot_request_id')->constrained('depot_requests')->cascadeOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->foreignIdFor(User::class, 'user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('depot_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('depot_request_status_logs');
    }
};
