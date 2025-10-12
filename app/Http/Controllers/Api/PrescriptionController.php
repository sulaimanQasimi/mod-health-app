<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function index(Appointment $appointment){

       $prescriptions= $appointment->prescription();
        return response()->json($prescriptions);
    }
    public function store(Request $request)  {
        
            $validator = Validator::make($request->all(), [
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'branch_id' => 'required|exists:branches,id',
                'prescription_items' => 'required|array|min:1',
                'prescription_items.*.medicine_type_id' => 'required|exists:medicine_types,id',
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
        
    }
}
