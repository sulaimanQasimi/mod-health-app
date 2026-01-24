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
                'message' => localize('global.failed_to_fetch_all_medicines'),
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
                'message' => localize('global.failed_to_fetch_medicine_usage_types'),
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
                // 'doctor_id' => 'required|exists:users,id',
                'branch_id' => 'required|exists:branches,id',
                'prescription_items' => 'required|array|min:1',
                'prescription_items.*.medicine_id' => 'required|exists:medicines,id',
                'prescription_items.*.usage_type_id' => 'required|exists:medicine_usage_types,id',
                'prescription_items.*.dosage' => 'required|string',
                'prescription_items.*.frequency' => 'required|string',
                'prescription_items.*.amount' => 'required|string',
                'hospitalization_id' => 'nullable|exists:hospitalizations,id',
                'under_review_id' => 'nullable|exists:under_reviews,id',
                'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Get doctor_id from appointment
            $appointment = Appointment::findOrFail($request->appointment_id);
            $doctor_id = $appointment->doctor_id;

            DB::beginTransaction();

            try {
                // Create main prescription record
                $prescriptionData = [
                    'branch_id' => $request->branch_id,
                    'appointment_id' => $request->appointment_id,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $doctor_id,
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
                        // 'doctor_id' => $doctor_id,
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
                    'message' => localize('global.prescription_created_successfully'),
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
                'message' => localize('global.failed_to_create_prescription'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescriptions for a specific appointment, ICU, or hospitalization
     */
    public function getAppointmentPrescriptions($id, $type = 'appointment')
    {
        try {
            $query = Prescription::query();
            
            if ($type === 'icu') {
                $query->where('i_c_u_id', $id);
            } elseif ($type === 'hospitalization') {
                $query->where('hospitalization_id', $id);
            } else {
                $query->where('appointment_id', $id);
            }
            
            $prescriptions = $query->with(['patient', 'doctor', 'prescriptionItems.medicine', 'prescriptionItems.medicineType', 'prescriptionItems.usageType'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $prescriptions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_fetch_prescriptions'),
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
                'message' => localize('global.failed_to_fetch_prescription_items'),
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
                    'message' => localize('global.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $prescription = Prescription::findOrFail($prescriptionId);
            $prescription->update([
                'is_completed' => $request->is_completed
            ]);

            return response()->json([
                'success' => true,
                'message' => localize('global.prescription_status_updated_successfully'),
                'data' => $prescription
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
                    'message' => localize('global.validation_failed'),
                    'errors' => $validator->errors()
                ], 422);
            }

            $item = PrescriptionItem::findOrFail($itemId);
            $item->update([
                'is_delivered' => $request->is_delivered
            ]);

            return response()->json([
                'success' => true,
                'message' => localize('global.prescription_item_status_updated_successfully'),
                'data' => $item
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_prescription_item_status'),
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
                'message' => localize('global.prescription_deleted_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_delete_prescription'),
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
                'message' => localize('global.prescription_item_deleted_successfully')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_delete_prescription_item'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prescriptions for index page with filtering, sorting, and pagination
     */
    public function getPrescriptionsIndex(Request $request)
    {
        try {
            $userClinicType = auth()->user()->clinic_type;
            
            $query = Prescription::where('branch_id', auth()->user()->branch_id)
                ->with(['patient', 'doctor', 'appointment.doctor', 'appointment.department']);

            // Filter by appointment clinic_type matching user's clinic_type
            if ($userClinicType) {
                $query->whereHas('appointment', function ($q) use ($userClinicType) {
                    $q->where('clinic_type', $userClinicType);
                });
            }

            // Search by patient name or ID card
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('patient', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('last_name', 'like', '%' . $search . '%')
                      ->orWhere('id_card', 'like', '%' . $search . '%');
                });
            }

            // Filter by token ID
            if ($request->filled('token_filter')) {
                $tokenFilter = $request->token_filter;
                $query->whereHas('appointment', function ($q) use ($tokenFilter) {
                    $q->whereHas('patient', function ($patientQuery) use ($tokenFilter) {
                        $patientQuery->whereHas('printedNumbers', function ($tokenQuery) use ($tokenFilter) {
                            $tokenQuery->where('number', 'like', '%' . $tokenFilter . '%')
                                      ->whereColumn('printed_numbers.department_id', 'appointments.department_id')
                                      ->whereColumn('printed_numbers.date', 'appointments.date');
                        });
                    });
                });
            }

            // Filter by status
            if ($request->filled('status')) {
                $query->where('is_completed', $request->status);
            } else {
                // Default to show only not completed prescriptions
                $query->where('is_completed', false);
            }

            // Filter by date range
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', \Hekmatinasser\Verta\Verta::parse($request->date_from)->datetime());
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', \Hekmatinasser\Verta\Verta::parse($request->date_to)->datetime());
            }

            // Filter by doctor_id: use prescription.doctor_id if set, otherwise appointment.doctor_id
            if ($request->filled('doctor_id')) {
                $query->whereRaw(
                    'COALESCE(prescriptions.doctor_id, (SELECT doctor_id FROM appointments WHERE appointments.id = prescriptions.appointment_id LIMIT 1)) = ?',
                    [$request->doctor_id]
                );
            }

            // Order by resolved doctor_id first (from prescription or appointment), then by selected sort
            $query->orderByRaw(
                'COALESCE(prescriptions.doctor_id, (SELECT doctor_id FROM appointments WHERE appointments.id = prescriptions.appointment_id LIMIT 1)) ASC'
            );

            // Sorting
            $sortBy = $request->get('sortBy', 'created_at');
            $sortOrder = $request->get('sortOrder', 'desc');
            
            $allowedSortFields = ['created_at', 'is_completed', 'patient_name', 'doctor_name'];
            if (in_array($sortBy, $allowedSortFields)) {
                if ($sortBy === 'patient_name') {
                    $query->join('patients', 'prescriptions.patient_id', '=', 'patients.id')
                          ->orderBy('patients.name', $sortOrder);
                } elseif ($sortBy === 'doctor_name') {
                    $query->leftJoin('appointments as app_doc_sort', 'prescriptions.appointment_id', '=', 'app_doc_sort.id')
                          ->leftJoin('doctors as doc_resolved', 'doc_resolved.id', '=', DB::raw('COALESCE(prescriptions.doctor_id, app_doc_sort.doctor_id)'))
                          ->orderBy('doc_resolved.name', $sortOrder);
                } else {
                    $query->orderBy('prescriptions.' . $sortBy, $sortOrder);
                }
            } else {
                $query->orderBy('prescriptions.created_at', 'desc');
            }

            // Pagination
            $perPage = $request->get('perPage', 10);
            $prescriptions = $query->paginate($perPage);
            
            // Load token and resolved doctor_name for each prescription
            $prescriptions->getCollection()->transform(function ($prescription) {
                if ($prescription->appointment) {
                    $token = \App\Models\PrintedNumber::where('patient_id', $prescription->patient_id)
                        ->where('department_id', $prescription->appointment->department_id)
                        ->whereDate('date', $prescription->appointment->date)
                        ->first();
                    $prescription->token = $token;
                }
                // Doctor: use prescription.doctor_id if set, otherwise from appointment
                $prescription->doctor_name = $prescription->doctor?->name ?? $prescription->appointment?->doctor?->name ?? '-';
                return $prescription;
            });

            return response()->json([
                'success' => true,
                'data' => $prescriptions->items(),
                'pagination' => [
                    'current_page' => $prescriptions->currentPage(),
                    'last_page' => $prescriptions->lastPage(),
                    'per_page' => $prescriptions->perPage(),
                    'total' => $prescriptions->total(),
                    'from' => $prescriptions->firstItem(),
                    'to' => $prescriptions->lastItem(),
                ],
                'links' => [
                    'first' => $prescriptions->url(1),
                    'last' => $prescriptions->url($prescriptions->lastPage()),
                    'prev' => $prescriptions->previousPageUrl(),
                    'next' => $prescriptions->nextPageUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_fetch_prescriptions'),
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
