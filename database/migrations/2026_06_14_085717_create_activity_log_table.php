<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();

            $table->string('log_name', 125)->nullable();
            $table->text('description');

            $table->string('subject_type')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('event', 50)->nullable();

            $table->string('causer_type')->nullable();
            $table->unsignedBigInteger('causer_id')->nullable();

            $table->json('attribute_changes')->nullable();
            $table->json('properties')->nullable();

            $table->timestamps();

            // Polymorphic lookups (performedOn / causedBy).
            $table->index(['subject_type', 'subject_id'], 'subject');
            $table->index(['causer_type', 'causer_id'], 'causer');

            // Default admin list: latest('created_at') with stable keyset pagination.
            $table->index(['created_at', 'id'], 'activity_log_created_at_id_index');

            // Filter by event or subject type while sorting newest first.
            $table->index(['event', 'created_at'], 'activity_log_event_created_at_index');
            $table->index(['subject_type', 'created_at'], 'activity_log_subject_type_created_at_index');

            // Scoped log streams (Spatie log_name).
            $table->index(['log_name', 'created_at'], 'activity_log_log_name_created_at_index');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->fullText('description', 'activity_log_description_fulltext');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
