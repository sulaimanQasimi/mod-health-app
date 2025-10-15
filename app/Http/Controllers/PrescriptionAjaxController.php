<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPrescriptionNotification;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use App\Models\MedicineUsageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PrescriptionAjaxController extends Controller
{

    /**
     * Get all medicines
     */
    public function getAllMedicines()
    {
        try {
            $medicines = Medicine::all();
            
            return response()->json([
                'success' => true,
                'data' => $medicines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch all medicines',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get medicine usage types
     */
    public function getMedicineUsageTypes()
    {
        try {
            $usageTypes = MedicineUsageType::all();
            
            return response()->json([
                'success' => true,
                'data' => $usageTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch medicine usage types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new prescription via Ajax
     */
    public function storePrescription(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'prescription_items' => 'required|array|min:1',
                'prescription_items.*.medicine_id' => 'required|exists:medicines,id',
                'prescription_items.*.usage_type_id' => 'required|exists:medicine_usage_types,id',
                'prescription_items.*.dosage' => 'required|string',
                'prescription_items.*.frequency' => 'required|string',
                'prescription_items.*.amount' => 'required|string',
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
                // Create main prescription record
                $prescriptionData = [
                    'branch_id' => $request->branch_id,
                    'appointment_id' => $request->appointment_id,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->doctor_id,
                    'hospitalization_id' => $request->hospitalization_id,
                    'under_review_id' => $request->under_review_id,
                    'i_c_u_id' => $request->i_c_u_id,
                    'is_completed' => false,
                    'created_by' => auth()->id(),
                ];

                $prescription = Prescription::create($prescriptionData);

                // Create prescription items
                foreach ($request->prescription_items as $item) {
                    PrescriptionItem::create([
                        'prescription_id' => $prescription->id,
                        'appointment_id' => $request->appointment_id,
                        'patient_id' => $request->patient_id,
                        'doctor_id' => $request->doctor_id,
                        'branch_id' => $request->branch_id,
                        'hospitalization_id' => $request->hospitalization_id,
                        'under_review_id' => $request->under_review_id,
                        'i_c_u_id' => $request->i_c_u_id,
                        'medicine_id' => $item['medicine_id'],
                        'usage_type_id' => $item['usage_type_id'],
                        'dosage' => $item['dosage'],
                        'frequency' => $item['frequency'],
                        'amount' => $item['amount'],
                        'is_delivered' => false,
                    ]);
                }

                // Send notification
                SendNewPrescriptionNotification::dispatch($prescription->created_by, $prescription->id);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Prescription created successfully',
                    'data' => [
                        'prescription_id' => $prescription->id,
                        'items_count' => count($request->prescription_items)
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create prescription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescriptions for a specific appointment
     */
    public function getAppointmentPrescriptions($appointmentId)
    {
        try {
            $prescriptions = Prescription::where('appointment_id', $appointmentId)
                ->with(['patient', 'doctor', 'prescriptionItems.medicine', 'prescriptionItems.medicineType', 'prescriptionItems.usageType'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $prescriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appointment prescriptions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescription items for a specific prescription
     */
    public function getPrescriptionItems($prescriptionId)
    {
        try {
            $prescription = Prescription::with([
                'prescriptionItems.medicine',
                'prescriptionItems.medicineType',
                'prescriptionItems.usageType',
                'patient',
                'doctor'
            ])->findOrFail($prescriptionId);

            return response()->json([
                'success' => true,
                'data' => $prescription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch prescription items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription status
     */
    public function updatePrescriptionStatus(Request $request, $prescriptionId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_completed' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescription = Prescription::findOrFail($prescriptionId);
            $prescription->update([
                'is_completed' => $request->is_completed
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prescription status updated successfully',
                'data' => $prescription
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prescription status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription item status
     */
    public function updatePrescriptionItemStatus(Request $request, $itemId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'is_delivered' => 'required|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $item = PrescriptionItem::findOrFail($itemId);
            $item->update([
                'is_delivered' => $request->is_delivered
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prescription item status updated successfully',
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update prescription item status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a prescription
     */
    public function deletePrescription($prescriptionId)
    {
        try {
            $prescription = Prescription::findOrFail($prescriptionId);
            $prescription->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prescription deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prescription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a prescription item
     */
    public function deletePrescriptionItem($itemId)
    {
        try {
            $item = PrescriptionItem::findOrFail($itemId);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prescription item deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete prescription item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
