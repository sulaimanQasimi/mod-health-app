<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name_dr', 200);
            $table->string('name_en', 200)->nullable();
            $table->string('name_pa', 200)->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['0', '1'])->comment('0 -> internal, 1 -> external');
            $table->integer('category')->comment('6 main category of ministries');
            $table->integer('parent_id')->nullable();
            $table->integer('sector_id')->nullable();
            $table->integer('order_or_document')->default(0)->comment('0 -> available for orders, 1 -> available for documents');
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by');
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recipients');
    }
};
