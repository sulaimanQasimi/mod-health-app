<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\LabTest;
use App\Models\LabTestParameter;
use App\Models\PatientTestRegistration;
use App\Models\TestCategory;
use App\Models\UnderReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabTestRegistrationAjaxController extends Controller
{
    /**
     * Get test categories for dropdown
     */
    public function getTestCategories()
    {
        try {
            $categories = TestCategory::all();
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch test categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get tests by category ID
     */
    public function getTestsByCategory($categoryId)
    {
        try {
            $tests = LabTest::where('category_id', $categoryId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $tests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch tests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get test parameters by test ID
     */
    public function getTestParameters($testId)
    {
        try {
            $parameters = LabTestParameter::where('test_id', $testId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $parameters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch test parameters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new lab test registration via Ajax
     */
    public function storeTestRegistration(Request $request, $type = 'appointment', $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'test_category_id' => 'required|exists:test_categories,id',
                'lab_test_id' => 'required|exists:lab_tests,id',
                'priority' => 'required|in:normal,urgent,stat',
                'notes' => 'nullable|string|max:1000',
                'doctor_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'entity_id' => 'required',
                'entity_type' => 'required|in:appointment,icu,hospitalization,under_review',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Get entity details for polymorphic relationship
                $entity = $this->getEntity($request->entity_type, $request->entity_id);
                if (!$entity) {
                    throw new \Exception('Entity not found');
                }

                // Lock ref_numbers row and increment ref_no
                $ref = DB::table('ref_numbers')->lockForUpdate()->first();
                $newRefNo = $ref->last_ref_no + 1;
                DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);

                // Get patient_id from the related entity
                $patientId = $this->getPatientIdFromEntity($entity);

                // Create test registration with polymorphic relationship
                $registration = PatientTestRegistration::create([
                    'patient_id'        => $patientId,
                    'testable_type'     => $this->getEntityClass($request->entity_type),
                    'testable_id'       => $request->entity_id,
                    'registration_date' => now(),
                    'ref_no'            => $newRefNo,
                    'lab_test_id'       => $request->lab_test_id,
                    'status'            => 'pending',
                    'doctor_id'         => $request->doctor_id,
                    'branch_id'         => $request->branch_id,
                    'priority'          => $request->priority,
                    'notes'             => $request->notes,
                ]);

                // Create test results for each parameter
                $parameters = LabTestParameter::where('test_id', $request->lab_test_id)->get();
                foreach ($parameters as $parameter) {
                    \App\Models\PatientTestResult::create([
                        'patient_id'          => $patientId,
                        'ref_no'              => $newRefNo,
                        'lab_parameter_id'    => $parameter->id,
                        'unit'                => $parameter->unit ?? null,
                        'normal_range'        => $parameter->normal_range ?? null,
                        'result'              => null,
                        'test_registration_id'=> $registration->id,
                    ]);
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Lab test registration created successfully',
                    'data' => [
                        'registration_id' => $registration->id,
                        'ref_no' => $newRefNo
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lab test registration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab test registrations for any entity type (unified method)
     */
    public function loadList(int $id, string $type = "appointment")
    {
        try {
            $registrations = PatientTestRegistration::query();
            
            switch($type) {
                case 'appointment':
                    $registrations->where('testable_type', 'App\\Models\\Appointment')
                                 ->where('testable_id', $id);
                    break;
                case 'icu':
                    $registrations->where('testable_type', 'App\\Models\\ICU')
                                 ->where('testable_id', $id);
                    break;
                case 'hospitalization':
                    $registrations->where('testable_type', 'App\\Models\\Hospitalization')
                                 ->where('testable_id', $id);
                    break;
                case 'under_review':
                    $registrations->where('testable_type', 'App\\Models\\UnderReview')
                                 ->where('testable_id', $id);
                    break;
                default:
                    $registrations->where('testable_type', 'App\\Models\\Appointment')
                                 ->where('testable_id', $id);
            }
            
            $registrations = $registrations->with([
                'testable.patient', 
                'labTest', 
                'doctor', 
                'branch'
            ])
            ->latest()
            ->get();
                
            return response()->json([
                'success' => true,
                'data' => $registrations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab test registrations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get registration parameters for a specific registration
     */
    public function getRegistrationParameters($registrationId)
    {
        try {
            $registration = PatientTestRegistration::with([
                'testable.patient',
                'labTest',
                'doctor',
                'branch',
                'results.parameter'
            ])->findOrFail($registrationId);

            // Transform results to parameters format for display
            $parameters = $registration->results->map(function($result) {
                return [
                    'id' => $result->parameter->id,
                    'parameter_name' => $result->parameter->parameter_name,
                    'unit' => $result->unit,
                    'normal_range' => $result->normal_range,
                    'result' => $result->result
                ];
            });

            $registration->parameters = $parameters;

            return response()->json([
                'success' => true,
                'data' => $registration
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registration parameters',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get entity by type and ID
     */
    private function getEntity($type, $id)
    {
        switch($type) {
            case 'appointment':
                return Appointment::find($id);
            case 'icu':
                return ICU::find($id);
            case 'hospitalization':
                return Hospitalization::find($id);
            case 'under_review':
                return UnderReview::find($id);
            default:
                return null;
        }
    }

    /**
     * Get entity class name for polymorphic relationship
     */
    private function getEntityClass($type)
    {
        switch($type) {
            case 'appointment':
                return 'App\\Models\\Appointment';
            case 'icu':
                return 'App\\Models\\ICU';
            case 'hospitalization':
                return 'App\\Models\\Hospitalization';
            case 'under_review':
                return 'App\\Models\\UnderReview';
            default:
                return 'App\\Models\\Appointment';
        }
    }

    /**
     * Get patient ID from entity
     */
    private function getPatientIdFromEntity($entity)
    {
        if (!$entity) {
            return null;
        }

        // All entities should have a patient_id field
        return $entity->patient_id ?? null;
    }
}
