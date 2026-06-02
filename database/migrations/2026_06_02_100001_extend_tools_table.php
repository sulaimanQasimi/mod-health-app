<?php

use App\Models\Unit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->string('name')->default('Tool')->after('id');
            $table->string('code')->nullable()->after('name');
            $table->foreignIdFor(Unit::class, 'unit_id')->nullable()->after('code')->constrained('units')->nullOnDelete();
            $table->text('description')->nullable()->after('unit_id');
            $table->boolean('is_active')->default(true)->after('description');
            $table->softDeletes();
        });

        foreach (\DB::table('tools')->whereNull('code')->pluck('id') as $toolId) {
            \DB::table('tools')->where('id', $toolId)->update([
                'code' => 'TOOL-' . str_pad((string) $toolId, 4, '0', STR_PAD_LEFT),
            ]);
        }

        Schema::table('tools', function (Blueprint $table) {
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('tools', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn(['name', 'code', 'description', 'is_active']);
        });
    }
};
