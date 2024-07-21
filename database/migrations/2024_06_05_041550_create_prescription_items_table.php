<?php

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
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_id');
            $table->unsignedBigInteger('medicine_id');
            $table->unsignedBigInteger('medicine_type_id');
            $table->unsignedBigInteger('usage_type_id');
            
            $table->string('dosage', 191);
            $table->string('frequency', 191);
            $table->string('amount', 191);
            $table->tinyInteger('is_delivered')->default('0');

            $table->foreign('medicine_id')->references('id')->on('medicines');
            $table->foreign('medicine_type_id')->references('id')->on('medicine_types');
            $table->foreign('prescription_id')->references('id')->on('prescriptions');
            $table->foreign('usage_type_id')->references('id')->on('medicine_usage_types');

            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
