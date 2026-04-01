<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('requesting_branch_id');
            $table->unsignedBigInteger('supplying_branch_id');
            $table->string('blood_group', 8);
            $table->string('rh', 8);
            $table->enum('component_type', ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'])->default('Fresh');
            $table->unsignedInteger('quantity');
            $table->enum('status', ['pending', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->unsignedBigInteger('fulfilled_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('requesting_branch_id')->references('id')->on('branches');
            $table->foreign('supplying_branch_id')->references('id')->on('branches');
            $table->foreign('fulfilled_by')->references('id')->on('users');

            $table->index(['requesting_branch_id', 'status']);
            $table->index(['supplying_branch_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_branch_transfers');
    }
};
