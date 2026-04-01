<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_samples', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('blood_donation_id');
            $table->string('sample_code')->nullable()->unique();
            $table->enum('status', ['collected', 'sent_to_lab', 'testing', 'completed', 'rejected'])->default('collected');
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('blood_donation_id')->references('id')->on('blood_donations')->cascadeOnDelete();
            $table->index(['blood_donation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_samples');
    }
};

