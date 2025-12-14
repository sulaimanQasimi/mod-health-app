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
        Schema::create('dental_xrays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_registration_id')->constrained('dentist_registrations')->onDelete('cascade');
            $table->string('xray_type');
            $table->date('xray_date');
            $table->string('file_path')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_xrays');
    }
};
