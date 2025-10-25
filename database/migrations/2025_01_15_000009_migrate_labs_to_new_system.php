<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\LabTest;
use App\Models\PatientTestRegistration;
use App\Models\PatientTestResult;
use App\Models\LabType;
use App\Models\LabTypeSection;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get all unique lab_types from labs table
        $labTypes = DB::table('labs')
            ->join('lab_types', 'labs.lab_type_id', '=', 'lab_types.id')
            ->join('lab_type_sections', 'labs.lab_type_section_id', '=', 'lab_type_sections.id')
            ->select('lab_types.id as lab_type_id', 'lab_types.name as lab_type_name', 'lab_type_sections.id as section_id')
            ->distinct()
            ->get();

        foreach ($labTypes as $labType) {
            // Create LabTest for each unique lab_type
            $labTest = LabTest::create([
                'category_id' => 1, // Default category, adjust as needed
                'name' => $labType->lab_type_name,
                'lab_type_id' => $labType->lab_type_id,
                'lab_type_section_id' => $labType->section_id,
                'has_parameters' => false, // Old system didn't have parameters
            ]);

            // Get all labs for this lab_type
            $labs = DB::table('labs')
                ->where('lab_type_id', $labType->lab_type_id)
                ->get();

            foreach ($labs as $lab) {
                // Create PatientTestRegistration
                $registration = PatientTestRegistration::create([
                    'patient_id' => $lab->patient_id,
                    'testable_type' => 'App\Models\Appointment', // Default to appointment
                    'testable_id' => $lab->appointment_id,
                    'registration_date' => $lab->created_at,
                    'ref_no' => $lab->id, // Use lab ID as ref_no for migration
                    'lab_test_id' => $labTest->id,
                    'status' => $lab->status ? 'completed' : 'pending',
                    'doctor_id' => $lab->doctor_id,
                    'branch_id' => $lab->branch_id,
                    'priority' => 'normal',
                    'notes' => null,
                    'category_id' => 1,
                ]);

                // Create PatientTestResult with text_result
                PatientTestResult::create([
                    'patient_id' => $lab->patient_id,
                    'ref_no' => $lab->id,
                    'lab_parameter_id' => null, // No parameters for migrated tests
                    'unit' => null,
                    'normal_range' => null,
                    'result' => null,
                    'text_result' => $lab->result, // Store old result as text_result
                    'test_registration_id' => $registration->id,
                ]);
            }
        }

        // After successful migration, we can optionally delete old data
        // Uncomment the following lines after verifying migration success:
        // DB::table('lab_items')->truncate();
        // DB::table('labs')->truncate();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible as it involves data transformation
        // Manual intervention would be required to restore old data
        throw new Exception('This migration cannot be reversed automatically. Manual data restoration required.');
    }
};
