<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\LabTest;
use App\Models\LabTestParameter;
use App\Models\PatientTestRegistration;
use App\Models\UnderReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabTestRegistrationAjaxController extends Controller
{

    /**
     * Get all lab types
     */
    public function getAllLabTypes()
    {
        try {
            $labTypes = \App\Models\LabType::with(['section', 'category', 'directLabTestParameters'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $labTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab type parameters by lab type ID
     */
    public function getLabTypeParameters($labTypeId)
    {
        try {
            $parameters = LabTestParameter::where('lab_type_id', $labTypeId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $parameters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab type parameters',
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
                'lab_type_ids' => 'required|string', // JSON string of lab type IDs
                'priority' => 'required|in:normal,urgent,stat',
                'notes' => 'nullable|string|max:1000',
                'detailed_notes' => 'nullable|string',
                'metadata' => 'nullable|string', // JSON string
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

            // Parse lab type IDs from JSON string
            $labTypeIds = json_decode($request->lab_type_ids, true);
            if (!is_array($labTypeIds) || empty($labTypeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid lab type IDs provided'
                ], 422);
            }

            // Validate that all lab type IDs exist
            $validLabTypes = \App\Models\LabType::whereIn('id', $labTypeIds)->pluck('id')->toArray();
            if (count($validLabTypes) !== count($labTypeIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'One or more lab type IDs are invalid'
                ], 422);
            }

            DB::beginTransaction();

            try {
                // Get entity details for polymorphic relationship
                $entity = $this->getEntity($request->entity_type, $request->entity_id);
                if (!$entity) {
                    throw new \Exception('Entity not found');
                }

                // Get patient_id from the related entity
                $patientId = $this->getPatientIdFromEntity($entity);

                // Generate new category_id for this batch to group all selected tests together
                $maxCategoryId = PatientTestRegistration::max('category_id') ?? 0;
                $newCategoryId = $maxCategoryId + 1;

                $createdRegistrations = [];
                $refNumbers = [];

                // Create registrations for each selected lab type
                foreach ($labTypeIds as $labTypeId) {
                    // Lock ref_numbers row and increment ref_no for each test
                    $ref = DB::table('ref_numbers')->lockForUpdate()->first();
                    $newRefNo = $ref->last_ref_no + 1;
                    DB::table('ref_numbers')->update(['last_ref_no' => $newRefNo]);
                    $refNumbers[] = $newRefNo;

                    // Parse metadata if provided
                    $metadata = null;
                    if ($request->has('metadata') && !empty($request->metadata)) {
                        $metadata = json_decode($request->metadata, true);
                    }

                    // Get lab type for creating test results
                    $labType = \App\Models\LabType::find($labTypeId);
                    
                    // Create test registration with polymorphic relationship
                    $registration = PatientTestRegistration::create([
                        'patient_id'        => $patientId,
                        'testable_type'     => $this->getEntityClass($request->entity_type),
                        'testable_id'       => $request->entity_id,
                        'registration_date' => now(),
                        'ref_no'            => $newRefNo,
                        'lab_type_id'       => $labTypeId,
                        'category_id'       => $newCategoryId, // Use the generated category_id to group all tests
                        'status'            => 'pending',
                        'doctor_id'         => $request->doctor_id,
                        'branch_id'         => $request->branch_id,
                        'priority'          => $request->priority,
                        'notes'             => $request->notes,
                        'detailed_notes'    => $request->detailed_notes,
                        'metadata'          => $metadata,
                    ]);

                    $createdRegistrations[] = $registration;

                    // Create test results - handle both parametered and non-parametered lab types
                    
                    if ($labType->directLabTestParameters && $labType->directLabTestParameters->count() > 0) {
                        // Create results for each parameter
                        foreach ($labType->directLabTestParameters as $parameter) {
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
                    } else {
                        // Create single result entry for non-parametered lab type
                        \App\Models\PatientTestResult::create([
                            'patient_id'          => $patientId,
                            'ref_no'              => $newRefNo,
                            'lab_parameter_id'    => null,
                            'unit'                => null,
                            'normal_range'        => null,
                            'result'              => null,
                            'text_result'         => null,
                            'test_registration_id'=> $registration->id,
                        ]);
                    }
                }

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => localize('global.lab_test_registrations_created_successfully'),
                    'data' => [
                        'registration_ids' => array_column($createdRegistrations, 'id'),
                        'ref_numbers' => $refNumbers,
                        'category_id' => $newCategoryId,
                        'registrations_count' => count($createdRegistrations)
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
                'labType.section',
                'labType.category',
                'labType.directLabTestParameters',
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
                'labType.section',
                'labType.category',
                'labType.directLabTestParameters',
                'doctor',
                'branch',
                'results.parameter'
            ])->findOrFail($registrationId);

            // Ensure labType exists and has directLabTestParameters
            if (!$registration->labType) {
                $registration->labType = (object) [
                    'id' => null,
                    'name' => '—',
                    'section' => null,
                    'category' => null,
                    'direct_lab_test_parameters' => []
                ];
            } else {
                // Ensure directLabTestParameters is an array
                if (!$registration->labType->directLabTestParameters) {
                    $registration->labType->direct_lab_test_parameters = [];
                }
            }

            // Transform results to parameters format for display (only if results exist)
            $parameters = collect();
            if ($registration->results && $registration->results->count() > 0) {
                $parameters = $registration->results->map(function($result) {
                    // Check if parameter exists before accessing its properties
                    if ($result->parameter) {
                        return [
                            'id' => $result->parameter->id,
                            'parameter_name' => $result->parameter->parameter_name,
                            'unit' => $result->unit,
                            'normal_range' => $result->normal_range,
                            'critical_low' => $result->parameter->critical_low,
                            'critical_high' => $result->parameter->critical_high,
                            'panic_low' => $result->parameter->panic_low,
                            'panic_high' => $result->parameter->panic_high,
                            'critical_comment' => $result->parameter->critical_comment,
                            'panic_comment' => $result->parameter->panic_comment,
                            'delta_check_enabled' => $result->parameter->delta_check_enabled,
                            'delta_check_threshold' => $result->parameter->delta_check_threshold,
                            'requires_verification' => $result->parameter->requires_verification,
                            'verification_level' => $result->parameter->verification_level,
                            'result' => $result->result
                        ];
                    }
                    return null;
                })->filter(); // Remove null entries
            }

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
