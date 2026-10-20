<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection(config('activitylog.database_connection'))
            ->create(config('activitylog.table_name'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->string('event')->nullable();
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamps();

                $table->index('log_name');
                $table->index(['created_at', 'id'], 'activity_log_created_at_id_index');
                $table->index(['event', 'created_at'], 'activity_log_event_created_at_index');
                $table->index(['subject_type', 'created_at'], 'activity_log_subject_type_created_at_index');
                $table->index(['log_name', 'created_at'], 'activity_log_log_name_created_at_index');
            });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::connection(config('activitylog.database_connection'))
                ->table(config('activitylog.table_name'), function (Blueprint $table) {
                    $table->fullText('description', 'activity_log_description_fulltext');
                });
        }
    }

    public function down(): void
    {
        Schema::connection(config('activitylog.database_connection'))
            ->dropIfExists(config('activitylog.table_name'));
    }
};
