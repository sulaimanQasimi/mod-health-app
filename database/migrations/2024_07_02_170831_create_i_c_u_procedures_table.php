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
        Schema::create('i_c_u_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->unsignedBigInteger('icu_procedure_type_id');
            $table->unsignedBigInteger('i_c_u_id');
            $table->softDeletes();
            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->foreign('icu_procedure_type_id')
                ->references('id')
                ->on('i_c_u_procedure_types');
            $table->foreign('i_c_u_id')
                ->references('id')
                ->on('i_c_u_s');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('i_c_u_procedures');
    }
};
