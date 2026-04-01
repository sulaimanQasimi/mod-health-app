<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('blood_donor_id');
            $table->timestamp('phlebotomy_at')->nullable();

            // Optional: capture donor's declared/known group at time of donation (can be confirmed by testing)
            $table->string('donor_blood_group', 8)->nullable();
            $table->string('donor_rh', 8)->nullable();

            $table->string('phlebotomy_site')->nullable();
            $table->string('phlebotomist_name')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('blood_donor_id')->references('id')->on('blood_donors');

            $table->index(['branch_id', 'phlebotomy_at']);
            $table->index(['blood_donor_id', 'phlebotomy_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donations');
    }
};

