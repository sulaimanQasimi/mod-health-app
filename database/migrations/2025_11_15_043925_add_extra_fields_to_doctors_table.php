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
        Schema::table('doctors', function (Blueprint $table) {
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->after('name');
            $table->string('father_name')->nullable()->after('gender');
            $table->string('contact_number')->nullable()->after('father_name');
            $table->text('address')->nullable()->after('contact_number');
            $table->string('specialization')->nullable()->after('address');
            $table->string('qualification')->nullable()->after('specialization');
            $table->string('room_no')->nullable()->after('qualification');
            $table->enum('clinic_type', ['hospital', 'clinic'])->nullable()->after('room_no');
            $table->date('join_date')->nullable()->after('clinic_type');
            $table->boolean('active_status')->default(true)->after('join_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'father_name',
                'contact_number',
                'address',
                'specialization',
                'qualification',
                'room_no',
                'clinic_type',
                'join_date',
                'active_status'
            ]);
        });
    }
};
