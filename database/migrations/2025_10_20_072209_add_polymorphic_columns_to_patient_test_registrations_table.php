<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Add polymorphic columns if they don't exist
            if (!Schema::hasColumn('patient_test_registrations', 'testable_type')) {
                $table->string('testable_type')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'testable_id')) {
                $table->unsignedBigInteger('testable_id')->nullable();
            }
            
            // Add other required fields if they don't exist
            if (!Schema::hasColumn('patient_test_registrations', 'doctor_id')) {
                $table->unsignedBigInteger('doctor_id')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'branch_id')) {
                $table->unsignedBigInteger('branch_id')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'priority')) {
                $table->enum('priority', ['normal', 'urgent', 'stat'])->default('normal');
            }
            if (!Schema::hasColumn('patient_test_registrations', 'notes')) {
                $table->text('notes')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'completed_by')) {
                $table->unsignedBigInteger('completed_by')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }
            if (!Schema::hasColumn('patient_test_registrations', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable();
            }
            
            // Add indexes for performance if they don't exist
            if (!$this->indexExists('patient_test_registrations', 'patient_test_registrations_testable_type_testable_id_index')) {
                $table->index(['testable_type', 'testable_id']);
            }
            if (!$this->indexExists('patient_test_registrations', 'patient_test_registrations_status_index')) {
                $table->index('status');
            }
            if (!$this->indexExists('patient_test_registrations', 'patient_test_registrations_priority_index')) {
                $table->index('priority');
            }
        });
        
        // Data migration: Convert existing records to use polymorphic relationship
        $this->migrateExistingRecords();
        
        // Keep patient_id column for backward compatibility and foreign key constraints
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patient_test_registrations', function (Blueprint $table) {
            // Drop polymorphic columns
            $table->dropColumn([
                'testable_type',
                'testable_id',
                'doctor_id',
                'branch_id',
                'priority',
                'notes',
                'completed_at',
                'completed_by',
                'created_by',
                'updated_by'
            ]);
            
            // Drop indexes
            $table->dropIndex(['testable_type', 'testable_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['priority']);
        });
    }
    
    /**
     * Check if index exists
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Migrate existing records to use polymorphic relationship
     */
    private function migrateExistingRecords(): void
    {
        // Get all existing patient test registrations
        $registrations = DB::table('patient_test_registrations')
            ->whereNotNull('patient_id')
            ->get();
            
        foreach ($registrations as $registration) {
            // Find the most recent appointment for this patient
            $appointment = DB::table('appointments')
                ->where('patient_id', $registration->patient_id)
                ->orderBy('created_at', 'desc')
                ->first();
                
            if ($appointment) {
                // Update the registration to use polymorphic relationship
                DB::table('patient_test_registrations')
                    ->where('id', $registration->id)
                    ->update([
                        'testable_type' => 'App\\Models\\Appointment',
                        'testable_id' => $appointment->id,
                        'doctor_id' => $appointment->doctor_id,
                        'branch_id' => $appointment->branch_id,
                        'created_by' => $appointment->created_by ?? 1,
                        'updated_by' => $appointment->updated_by ?? 1,
                    ]);
            } else {
                // If no appointment found, create a default one or handle differently
                // For now, we'll set a default appointment or skip
                DB::table('patient_test_registrations')
                    ->where('id', $registration->id)
                    ->update([
                        'testable_type' => 'App\\Models\\Appointment',
                        'testable_id' => null, // Will need manual review
                        'created_by' => 1,
                        'updated_by' => 1,
                    ]);
            }
        }
    }
};