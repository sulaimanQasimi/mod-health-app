<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Jobs\SendNewLabNotification;
use App\Models\Appointment;
use App\Models\Hospitalization;
use App\Models\ICU;
use App\Models\Lab;
use App\Models\LabItem;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\UnderReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LabAjaxController extends Controller
{
    /**
     * Get lab type sections for dropdown
     */
    public function getLabTypeSections()
    {
        try {
            $sections = LabTypeSection::with('relatedSection')->get();
            
            return response()->json([
                'success' => true,
                'data' => $sections
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab type sections',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab types by section ID
     */
    public function getLabTypesBySection($sectionId)
    {
        try {
            $labTypes = LabType::where('section_id', $sectionId)->get();
            
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
     * Get lab tests for a specific lab type
     */
    public function getLabTypeTests($labTypeId)
    {
        try {
            $tests = \App\Models\LabTest::where('lab_type_id', $labTypeId)
                ->with('parameters')
                ->get()
                ->map(function($test) {
                    return [
                        'id' => $test->id,
                        'name' => $test->name,
                        'has_parameters' => $test->has_parameters,
                        'parameters_count' => $test->parameters->count(),
                        'lab_type_section' => $test->labTypeSection->section ?? null
                    ];
                });
            
            return response()->json([
                'success' => true,
                'tests' => $tests
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab type tests',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new lab test via Ajax
     */
    public function storeLabTest(Request $request, $type = 'appointment', $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lab_type_id' => 'required|array|min:1',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'lab_type_section_id' => 'required|exists:lab_type_sections,id',
                'status' => 'nullable|boolean',
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
                // Set the appropriate ID field based on entity type from request
                $labData = [
                    'branch_id' => $request->branch_id,
                    'appointment_id' => null,
                    'hospitalization_id' => null,
                    'under_review_id' => null,
                    'i_c_u_id' => null,
                    'lab_type_id' => $request->lab_type_id[0],
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'lab_type_section_id' => $request->lab_type_section_id,
                    'status' => $request->status ?? false,
                    'created_by' => auth()->id(),
                ];

                // Set entity-specific fields based on request data
                $this->setEntityFields($labData, $request->entity_type, $request->entity_id);

                $lab = Lab::create($labData);

                // Create lab items for each selected lab type
                foreach ($request->lab_type_id as $labTypeId) {
                    $labItemData = [
                        'lab_id' => $lab->id,
                        'lab_type_id' => $labTypeId,
                        'appointment_id' => null,
                        'patient_id' => $request->patient_id,
                        'doctor_id' => $request->doctor_id,
                        'branch_id' => $request->branch_id,
                        'hospitalization_id' => null,
                        'under_review_id' => null,
                        'i_c_u_id' => null,
                    ];

                    // Set entity-specific fields for lab items
                    $this->setEntityFields($labItemData, $request->entity_type, $request->entity_id);
                    LabItem::create($labItemData);
                }

                // Send notification
                SendNewLabNotification::dispatch($lab->created_by, $lab->id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Lab test created successfully',
                    'data' => [
                        'lab_id' => $lab->id,
                        'lab_type_count' => count($request->lab_type_id)
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create lab test',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get lab tests for any entity type (unified method)
     */
    public function loadList(int $id, string $type = "appointment")
    {
        try {
            $labs = Lab::query();
            
            switch($type) {
                case 'appointment':
                    $labs->where('appointment_id', $id);
                    break;
                case 'icu':
                    $labs->where('i_c_u_id', $id);
                    break;
                case 'hospitalization':
                    $labs->where('hospitalization_id', $id);
                    break;
                case 'under_review':
                    $labs->where('under_review_id', $id);
                    break;
                default:
                    $labs->where('appointment_id', $id);
            }
            
            $labs = $labs->with(['labType', 'patient', 'doctor', 'labTypeSection.relatedSection', 'labItems.labType'])
                ->latest()
                ->get();
                
            return response()->json([
                'success' => true,
                'data' => $labs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch labs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get lab items for a specific lab
     */
    public function getLabItems($labId)
    {
        try {
            $lab = Lab::with([
                'labItems.labType',
                'patient',
                'doctor',
                'labTypeSection.relatedSection'
            ])->findOrFail($labId);

            return response()->json([
                'success' => true,
                'data' => $lab
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch lab items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update lab test status
     */
    public function updateLabStatus(Request $request, $labId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|boolean',
                'result' => 'nullable|string',
                'result_file' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $lab = Lab::findOrFail($labId);
            
            $updateData = [
                'status' => $request->status,
                'result' => $request->result,
            ];

            // Handle file upload
            if ($request->hasFile('result_file')) {
                $resultFile = $request->file('result_file');
                $resultFileName = time() . '.' . $resultFile->getClientOriginalExtension();
                $resultFile->storeAs('public', $resultFileName);
                $updateData['result_file'] = $resultFileName;
            }

            $lab->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Lab status updated successfully',
                'data' => $lab
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update lab status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a lab test
     */
    public function deleteLabTest($labId)
    {
        try {
            $lab = Lab::findOrFail($labId);
            $lab->delete();

            return response()->json([
                'success' => true,
                'message' => 'Lab test deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete lab test',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set entity-specific fields based on type
     */
    private function setEntityFields(array &$data, string $type, int $id): void
    {
        switch($type) {
            case 'appointment':
                $data['appointment_id'] = $id;
                break;
            case 'icu':
                $data['i_c_u_id'] = $id;
                $data['appointment_id'] = ICU::find($id)->appointment_id;
                break;
            case 'hospitalization':
                $data['hospitalization_id'] = $id;
                $data['appointment_id'] = Hospitalization::find($id)->appointment_id;
                break;
            case 'under_review':
                $data['under_review_id'] = $id;
                
                $data['appointment_id'] = UnderReview::find($id)->appointment_id;
                break;
            default:
                $data['appointment_id'] = $id;
        }
    }
}
