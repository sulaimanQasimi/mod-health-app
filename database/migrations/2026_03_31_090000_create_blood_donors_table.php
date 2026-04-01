<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blood_donors', function (Blueprint $table) {
            $table->id();

            $table->string('donor_code')->nullable()->unique();
            $table->string('name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('national_id')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['name']);
            $table->index(['phone']);
            $table->index(['national_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blood_donors');
    }
};

