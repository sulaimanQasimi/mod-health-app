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
        Schema::create('medication_administration_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medicine_id')->constrained('medicines', 'id', 'mar_medicine_fk')->onDelete('cascade');
            $table->date('order_date')->nullable();
            $table->date('date_signature')->nullable();
            $table->foreignId('nurse_id')->nullable()->constrained('nurses', 'id', 'mar_nurse_fk')->onDelete('set null');
            $table->unsignedBigInteger('morphable_id');
            $table->string('morphable_type');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Add indexes for better performance
            $table->index(['morphable_id', 'morphable_type'], 'mar_morphable_index');
            $table->index('medicine_id', 'mar_medicine_index');
            $table->index('nurse_id', 'mar_nurse_index');
            $table->index('order_date', 'mar_order_date_index');
            $table->index('date_signature', 'mar_signature_date_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_administration_records');
    }
};
