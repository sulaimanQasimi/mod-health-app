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
        Schema::create('daily_icu_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('i_c_u_id');
            $table->foreign('i_c_u_id')
                  ->references('id')
                  ->on('i_c_u_s');
            $table->string('icu_day')->nullable();
            $table->string('icu_diagnose')->nullable();
            $table->string('daily_events')->nullable();
            $table->string('hr')->nullable();
            $table->string('bp')->nullable();
            $table->string('spo2')->nullable();
            $table->string('t')->nullable();
            $table->string('rr')->nullable();
            $table->string('gcs')->nullable();
            $table->string('cvs')->nullable();
            $table->string('pupils')->nullable();
            $table->string('s1s2')->nullable();
            $table->string('rs')->nullable();
            $table->string('gi')->nullable();
            $table->string('renal')->nullable();
            $table->string('musculoskeletal_system')->nullable();
            $table->string('extremities')->nullable();
            $table->text('assesment',1000)->nullable();
            $table->text('plan',2000)->nullable();
            $table->text('lab_ids')->nullable();

            $table->integer('created_by');
            $table->integer('deleted_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_icu_progress');
    }
};
