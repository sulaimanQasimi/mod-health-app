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
        Schema::create('users', function (Blueprint $table) {

            $table->id();
            $table->string('name_en', 191)->nullable();
            $table->string('email', 191)->unique();
            $table->string('password', 191);
            $table->string('avatar')->nullable();
            $table->rememberToken();
            $table->string('name_dr', 191)->nullable();
            $table->string('last_name_dr', 191)->nullable();
            $table->integer('status')->default(1)->comment('1 active,0 inactive');
            $table->integer('branch_id')->nullable();
            $table->integer('user_change_password_status')->default(0)->comment('0->not changed 1->changed');
            $table->string('lang', 5)->default('dr');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
