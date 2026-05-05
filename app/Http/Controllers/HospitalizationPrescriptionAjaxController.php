<?php

namespace App\Http\Controllers;

use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\MedicineUsageType;
use App\Models\Hospitalization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HospitalizationPrescriptionAjaxController extends Controller
{
    public function getMedicineTypes()
    {
        try {
            $medicineTypes = MedicineType::all();
            return response()->json([
                'success' => true,
                'data' => $medicineTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری انواع دارو',
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
                'message' => 'خطا در بارگذاری انواع مصرف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllMedicines()
    {
        try {
            $medicines = Medicine::with('medicineType')->get();
            return response()->json([
                'success' => true,
                'data' => $medicines
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری داروها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getHospitalizationPrescriptions($hospitalizationId)
    {
        try {
            $viewerClinicType = auth()->user()->clinic_type;
            $prescriptions = Prescription::where('hospitalization_id', $hospitalizationId)
                ->visibleToClinicType($viewerClinicType)
                ->with(['patient', 'doctor', 'prescriptionItems.medicine', 'prescriptionItems.medicineType', 'prescriptionItems.usageType'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $prescriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری نسخه‌ها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPrescriptionItems($prescriptionId)
    {
        try {
            $viewerClinicType = auth()->user()->clinic_type;
            $prescription = Prescription::with([
                'patient',
                'doctor',
                'prescriptionItems.medicine',
                'prescriptionItems.medicineType',
                'prescriptionItems.usageType'
            ])
                ->whereKey($prescriptionId)
                ->visibleToClinicType($viewerClinicType)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => $prescription
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری جزئیات نسخه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function storePrescription(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'hospitalization_id' => 'required|exists:hospitalizations,id',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'prescription_items' => 'required|array|min:1',
                'prescription_items.*.medicine_id' => 'required|exists:medicines,id',
                'prescription_items.*.usage_type_id' => 'required|exists:medicine_usage_types,id',
                'prescription_items.*.dosage' => 'required|string|max:255',
                'prescription_items.*.frequency' => 'required|string|max:255',
                'prescription_items.*.amount' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی داده‌ها',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Get appointment_id from hospitalization
            $hospitalization = Hospitalization::findOrFail($request->hospitalization_id);

            // Create prescription
            $prescription = Prescription::create([
                'hospitalization_id' => $request->hospitalization_id,
                'appointment_id' => $hospitalization->appointment_id,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'branch_id' => $request->branch_id,
                'is_completed' => false,
            ]);

            // Create prescription items
            foreach ($request->prescription_items as $item) {
                PrescriptionItem::create([
                    'prescription_id' => $prescription->id,
                    'medicine_id' => $item['medicine_id'],
                    'usage_type_id' => $item['usage_type_id'],
                    'dosage' => $item['dosage'],
                    'frequency' => $item['frequency'],
                    'amount' => $item['amount'],
                    'is_delivered' => false,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'نسخه با موفقیت ایجاد شد',
                'data' => $prescription->load(['patient', 'doctor', 'prescriptionItems.medicine', 'prescriptionItems.medicineType', 'prescriptionItems.usageType'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد نسخه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
