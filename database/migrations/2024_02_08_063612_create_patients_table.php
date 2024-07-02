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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('father_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('age')->nullable();
            $table->tinyInteger('gender')->default('0')->nullable();
            $table->string('nid')->nullable();
            $table->enum('job_type', ['civilian', 'militant', 'retired'])->default('militant');
            $table->string('job')->nullable();
            $table->string('rank')->nullable();
            $table->string('image')->nullable();
            $table->string('referral_name')->nullable();
            $table->string('referral_last_name')->nullable();
            $table->string('referral_father_name')->nullable();
            $table->string('referral_nid')->nullable();
            $table->string('referral_id_card')->nullable();
            $table->string('referral_phone')->nullable();
            $table->integer('referral_recipient')->nullable();            
            $table->tinyInteger('type')->default('0')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('relation_id')->nullable();
            $table->unsignedBigInteger('province_id');
            $table->unsignedBigInteger('id_card')->nullable();
            $table->tinyInteger('job_category')->default('0')->nullable();
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('referred_by');
            $table->foreign('province_id')->references('id')->on('provinces');
            $table->foreign('district_id')->references('id')->on('districts');
            $table->foreign('relation_id')->references('id')->on('relations');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->softDeletes();
            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
