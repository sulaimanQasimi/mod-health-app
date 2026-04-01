<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('donation_id')->nullable();
            $table->string('blood_group', 8);
            $table->string('rh', 8);
            $table->enum('component_type', ['Fresh', 'RBC', 'PRBC', 'Platelets', 'Plasma', 'Whole Blood'])->default('Fresh');
            $table->string('bag_number')->unique();
            $table->unsignedInteger('volume_ml')->nullable();
            $table->date('collected_at')->nullable();
            $table->dateTime('expires_at');
            $table->enum('status', ['available', 'reserved', 'issued', 'discarded', 'quarantine'])->default('available');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('donation_id')->references('id')->on('blood_donations')->nullOnDelete();

            $table->index(['branch_id', 'status', 'expires_at']);
            $table->index(['branch_id', 'blood_group', 'rh', 'component_type', 'status']);
            $table->index(['donation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_units');
    }
};
