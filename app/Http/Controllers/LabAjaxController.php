<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewLabNotification;
use App\Models\Appointment;
use App\Models\Lab;
use App\Models\LabItem;
use App\Models\LabType;
use App\Models\LabTypeSection;
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
            $labTypes = LabType::where('section_id', $sectionId)
                ->get();
            
            \Log::info('Found ' . $labTypes->count() . ' lab types');
            
            return response()->json([
                'success' => true,
                'data' => $labTypes
            ]);
     }

    /**
     * Get lab type tests by lab type ID
     */
    public function getLabTypeTests($labTypeId)
    {
        try {
            $tests = LabType::where('parent_id', $labTypeId)->get();
            
            return response()->json([
                'success' => true,
                'data' => $tests
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
    public function storeLabTest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lab_type_id' => 'required|array|min:1',
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'branch_id' => 'required|exists:branches,id',
                'lab_type_section_id' => 'required|exists:lab_type_sections,id',
                'status' => 'nullable|boolean',
                'hospitalization_id' => 'nullable|exists:hospitalizations,id',
                'under_review_id' => 'nullable|exists:under_reviews,id',
                'i_c_u_id' => 'nullable|exists:i_c_us,id',
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
                // Create main lab record
                $labData = [
                    'branch_id' => $request->branch_id,
                    'appointment_id' => $request->appointment_id,
                    'hospitalization_id' => $request->hospitalization_id,
                    'under_review_id' => $request->under_review_id,
                    'i_c_u_id' => $request->i_c_u_id,
                    'lab_type_id' => $request->lab_type_id[0],
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'lab_type_section_id' => $request->lab_type_section_id,
                    'status' => $request->status ?? false,
                    'created_by' => auth()->id(),
                ];

                $lab = Lab::create($labData);

                // Create lab items for each selected lab type
                foreach ($request->lab_type_id as $labTypeId) {
                    LabItem::create([
                        'lab_id' => $lab->id,
                        'lab_type_id' => $labTypeId,
                        'appointment_id' => $request->appointment_id,
                        'patient_id' => $request->patient_id,
                        'doctor_id' => $request->doctor_id,
                        'branch_id' => $request->branch_id,
                        'hospitalization_id' => $request->hospitalization_id,
                        'under_review_id' => $request->under_review_id,
                        'i_c_u_id' => $request->i_c_u_id,
                    ]);
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
     * Get lab tests for a specific appointment
     */
    public function getAppointmentLabs($appointmentId)
    {
        try {
            $labs = Lab::where('appointment_id', $appointmentId)
                ->with(['labType', 'patient', 'doctor', 'labTypeSection.relatedSection', 'labItems.labType'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $labs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointment labs',
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
}
