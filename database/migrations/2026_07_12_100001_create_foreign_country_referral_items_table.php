<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('foreign_country_referral_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('foreign_country_referral_id');
            $table->text('diagnosis')->nullable();
            $table->text('doctor_comment')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('expire_date')->nullable();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('foreign_country_referral_id', 'fcr_items_referral_fk')
                ->references('id')
                ->on('foreign_country_referrals')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('foreign_country_referral_items');
    }
};
