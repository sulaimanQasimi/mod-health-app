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
        Schema::create('dental_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dentist_registration_id')->constrained('dentist_registrations')->onDelete('cascade');
            $table->date('note_date');
            $table->string('note_type')->default('general');
            $table->text('content');
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
        Schema::dropIfExists('dental_notes');
    }
};
