<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ophthalmology_registrations', function (Blueprint $table) {
            $table->json('attachments')->nullable()->after('fundus_image_path');
            $table->json('diagnosis_items')->nullable()->after('diagnosis');
        });
    }

    public function down(): void
    {
        Schema::table('ophthalmology_registrations', function (Blueprint $table) {
            $table->dropColumn(['attachments', 'diagnosis_items']);
        });
    }
};
