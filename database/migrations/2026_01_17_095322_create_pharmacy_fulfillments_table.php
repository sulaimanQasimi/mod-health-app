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
        Schema::create('pharmacy_fulfillments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medicine_id');
            $table->string('unit_type')->nullable();
            $table->string('amount', 191);
            $table->string('form_no', 191);
            $table->date('date');
            $table->string('form')->nullable(); // File path for PDF
            $table->unsignedBigInteger('pharmacy_id');
            $table->unsignedBigInteger('user_id');
            
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            
            $table->foreign('medicine_id')->references('id')->on('medicines');
            $table->foreign('pharmacy_id')->references('id')->on('pharmacies');
            $table->foreign('user_id')->references('id')->on('users');
            
            $table->index('medicine_id');
            $table->index('pharmacy_id');
            $table->index('user_id');
            $table->index('date');
            
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pharmacy_fulfillments');
    }
};
