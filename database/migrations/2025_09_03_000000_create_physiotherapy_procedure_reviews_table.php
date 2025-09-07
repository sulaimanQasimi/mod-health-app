<?php

use App\Models\PhysiotherapyProcedure;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('physiotherapy_procedure_reviews')) {
            Schema::create('physiotherapy_procedure_reviews', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('physiotherapy_procedure_id');
                $table->text('description')->nullable();
                $table->integer('days_count')->default(0);
                $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
                $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
                $table->softDeletes();

                // Use a shorter, explicit foreign key constraint name to avoid MySQL's 64-character limit
                $table->foreign('physiotherapy_procedure_id', 'pp_review_procedure_id_fk')
                    ->references('id')
                    ->on('physiotherapy_procedures')
                    ->onDelete('cascade');

            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physiotherapy_procedure_reviews');
    }
};
