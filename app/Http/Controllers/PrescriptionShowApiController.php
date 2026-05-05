<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\PrescriptionAlternativeItem;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PrescriptionShowApiController extends Controller
{
    /**
     * Get full prescription details with all relationships
     */
    public function getPrescriptionDetails($id)
    {
        try {
            \Log::info('PrescriptionShowApiController: Getting prescription details for ID: ' . $id);
            
            $prescription = Prescription::with([
                'patient',
                'doctor',
                'pharmacy',
                'prescriptionItems.medicine',
                'prescriptionItems.medicineType',
                'prescriptionItems.usageType',
                'prescriptionItems.selectedAlternative.medicine',
                'prescriptionItems.selectedAlternative.medicineType',
                'prescriptionItems.selectedAlternative.usageType',
                'prescriptionItems.alternativeItems.medicine',
                'prescriptionItems.alternativeItems.medicineType',
                'prescriptionItems.alternativeItems.usageType'
            ])
                ->whereKey($id)
                ->where('branch_id', auth()->user()->branch_id)
                ->visibleToClinicType(auth()->user()->clinic_type)
                ->firstOrFail();

            \Log::info('PrescriptionShowApiController: Prescription found: ' . $prescription->id);

            return response()->json([
                'success' => true,
                'data' => $prescription,
                'message' => localize('global.prescription_details_retrieved_successfully')
            ]);
        } catch (\Exception $e) {
            \Log::error('PrescriptionShowApiController: Error getting prescription details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_prescription_details'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription completion status
     */
    public function updatePrescriptionStatus(Request $request, $id)
    {
        try {
            $prescription = Prescription::query()
                ->whereKey($id)
                ->where('branch_id', auth()->user()->branch_id)
                ->visibleToClinicType(auth()->user()->clinic_type)
                ->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'is_completed' => 'required|in:0,1',
                'pharmacy_id' => 'nullable|exists:pharmacies,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescription->is_completed = $request->is_completed;
            if ($request->pharmacy_id) {
                $prescription->pharmacy_id = $request->pharmacy_id;
            }
            $prescription->save();

            return response()->json([
                'success' => true,
                'data' => $prescription,
                'message' => localize('global.prescription_status_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_prescription_status'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription item delivery status
     */
    public function updateItemStatus(Request $request, $itemId)
    {
        try {
            $item = PrescriptionItem::query()
                ->whereKey($itemId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'is_delivered' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $item->is_delivered = $request->is_delivered;
            $item->save();

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => localize('global.item_status_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_item_status'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update prescription item amount
     */
    public function updateItemAmount(Request $request, $itemId)
    {
        try {
            $item = PrescriptionItem::query()
                ->whereKey($itemId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'amount' => 'required|max:255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $item->amount = $request->amount;
            $item->save();

            return response()->json([
                'success' => true,
                'data' => $item,
                'message' => localize('global.amount_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_amount'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get alternatives for a prescription item
     */
    public function getAlternatives($itemId)
    {
        try {
            $item = PrescriptionItem::with([
                'alternativeItems.medicine',
                'alternativeItems.medicineType',
                'alternativeItems.usageType'
            ])
                ->whereKey($itemId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $item->alternativeItems,
                'message' => localize('global.alternatives_retrieved_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_alternatives'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add new alternative medicine
     */
    public function addAlternative(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'prescription_id' => 'required|exists:prescriptions,id',
                'prescription_item_id' => 'required|exists:prescription_items,id',
                'medicine_id' => 'required|exists:medicines,id',
                'medicine_type_id' => 'required|exists:medicine_types,id',
                'usage_type_id' => 'required|exists:medicine_usage_types,id',
                'dosage' => 'required|string|max:255',
                'frequency' => 'required|string|max:255',
                'amount' => 'required|string|max:255',
                'notes' => 'nullable|string|max:1000'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ensure user can access the prescription and the item belongs to it.
            Prescription::query()
                ->whereKey($request->prescription_id)
                ->where('branch_id', auth()->user()->branch_id)
                ->visibleToClinicType(auth()->user()->clinic_type)
                ->firstOrFail();

            PrescriptionItem::query()
                ->whereKey($request->prescription_item_id)
                ->where('prescription_id', $request->prescription_id)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();

            $alternative = PrescriptionAlternativeItem::create([
                'prescription_id' => $request->prescription_id,
                'prescription_item_id' => $request->prescription_item_id,
                'medicine_id' => $request->medicine_id,
                'medicine_type_id' => $request->medicine_type_id,
                'usage_type_id' => $request->usage_type_id,
                'dosage' => $request->dosage,
                'frequency' => $request->frequency,
                'amount' => $request->amount,
                'notes' => $request->notes,
                'is_delivered' => '0',
                'is_selected' => '0'
            ]);

            $alternative->load([
                'medicine',
                'medicineType',
                'usageType'
            ]);

            return response()->json([
                'success' => true,
                'data' => $alternative,
                'message' => localize('global.alternative_added_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_add_alternative'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Select/deselect alternative
     */
    public function selectAlternative(Request $request, $alternativeId)
    {
        try {
            $alternative = PrescriptionAlternativeItem::query()
                ->whereKey($alternativeId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();
            
            // Toggle selection
            $alternative->is_selected = $alternative->is_selected ? '0' : '1';
            $alternative->save();

            // If selecting this alternative, deselect others for the same prescription item
            if ($alternative->is_selected) {
                PrescriptionAlternativeItem::where('prescription_item_id', $alternative->prescription_item_id)
                    ->where('id', '!=', $alternativeId)
                    ->update(['is_selected' => '0']);
            }

            return response()->json([
                'success' => true,
                'data' => $alternative,
                'message' => localize('global.alternative_selection_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_alternative_selection'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update alternative delivery status
     */
    public function updateAlternativeStatus(Request $request, $alternativeId)
    {
        try {
            $alternative = PrescriptionAlternativeItem::query()
                ->whereKey($alternativeId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();
            
            $validator = Validator::make($request->all(), [
                'is_delivered' => 'required|in:0,1'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $alternative->is_delivered = $request->is_delivered;
            $alternative->save();

            return response()->json([
                'success' => true,
                'data' => $alternative,
                'message' => localize('global.alternative_status_updated_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_alternative_status'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete alternative
     */
    public function deleteAlternative($alternativeId)
    {
        try {
            $alternative = PrescriptionAlternativeItem::query()
                ->whereKey($alternativeId)
                ->whereHas('prescription', function ($q) {
                    $q->where('branch_id', auth()->user()->branch_id)
                      ->visibleToClinicType(auth()->user()->clinic_type);
                })
                ->firstOrFail();
            $alternative->delete();

            return response()->json([
                'success' => true,
                'message' => localize('global.alternative_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_delete_alternative'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all medicines for alternatives dropdown
     */
    public function getAllMedicines()
    {
        try {
            $medicines = Medicine::orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $medicines,
                'message' => localize('global.medicines_retrieved_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_medicines'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all medicine types for alternatives dropdown
     */
    public function getMedicineTypes()
    {
        try {
            $types = MedicineType::orderBy('type')->get();
            
            return response()->json([
                'success' => true,
                'data' => $types,
                'message' => localize('global.medicine_types_retrieved_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_medicine_types'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all medicine usage types for alternatives dropdown
     */
    public function getMedicineUsageTypes()
    {
        try {
            $usageTypes = MedicineUsageType::orderBy('name')->get();
            
            return response()->json([
                'success' => true,
                'data' => $usageTypes,
                'message' => localize('global.usage_types_retrieved_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_usage_types'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
