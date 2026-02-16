<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Visit;
use App\Models\FoodType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VisitAjaxController extends Controller
{
    /**
     * Get food types for dropdown
     */
    public function getFoodTypes()
    {
        try {
            $foodTypes = FoodType::all();
            
            return response()->json([
                'success' => true,
                'data' => $foodTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch food types',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new visit via Ajax
     */
    public function storeVisit(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'patient_id' => 'required|exists:patients,id',
                'doctor_id' => 'required|exists:doctors,id',
                'hospitalization_id' => 'required|exists:hospitalizations,id',
                'bp' => 'nullable|string',
                'pr' => 'nullable|string',
                'rr' => 'nullable|string',
                't' => 'nullable|string',
                'spo2' => 'nullable|string',
                'pain' => 'nullable|string',
                'antibiotic' => 'nullable|string',
                'food_type_id' => 'nullable|array',
                'food_type_id.*' => 'exists:food_types,id',
                'intake' => 'nullable|string',
                'output' => 'nullable|string',
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
                // visits.doctor_id stores user id; frontend sends doctors.id — resolve user_id from doctor
                $doctor = Doctor::find($request->doctor_id);
                $doctorUserId = $doctor && $doctor->user_id ? $doctor->user_id : auth()->id();

                $visitData = [
                    'description' => $request->description,
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $doctorUserId,
                    'hospitalization_id' => $request->hospitalization_id,
                    'bp' => $request->bp,
                    'pr' => $request->pr,
                    'rr' => $request->rr,
                    't' => $request->t,
                    'spo2' => $request->spo2,
                    'pain' => $request->pain,
                    'antibiotic' => $request->antibiotic,
                    'food_type_id' => $request->food_type_id ? json_encode($request->food_type_id) : null,
                    'intake' => $request->intake,
                    'output' => $request->output,
                ];

                $visit = Visit::create($visitData);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Visit created successfully',
                    'data' => $visit
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create visit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visits for a specific hospitalization
     */
    public function getHospitalizationVisits($hospitalizationId)
    {
        try {
            $visits = Visit::where('hospitalization_id', $hospitalizationId)
                ->with(['doctor'])
                ->latest()
                ->get();

            return response()->json([
                'success' => true,
                'data' => $visits
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hospitalization visits',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get visit details for a specific visit
     */
    public function getVisitDetails($visitId)
    {
        try {
            $visit = Visit::with(['doctor'])
                ->findOrFail($visitId);

            return response()->json([
                'success' => true,
                'data' => $visit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch visit details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a visit
     */
    public function updateVisit(Request $request, $visitId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string',
                'bp' => 'nullable|string',
                'pr' => 'nullable|string',
                'rr' => 'nullable|string',
                't' => 'nullable|string',
                'spo2' => 'nullable|string',
                'pain' => 'nullable|string',
                'antibiotic' => 'nullable|string',
                'food_type_id' => 'nullable|array',
                'food_type_id.*' => 'exists:food_types,id',
                'intake' => 'nullable|string',
                'output' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $visit = Visit::findOrFail($visitId);
            
            $visit->update([
                'description' => $request->description,
                'bp' => $request->bp,
                'pr' => $request->pr,
                'rr' => $request->rr,
                't' => $request->t,
                'spo2' => $request->spo2,
                'pain' => $request->pain,
                'antibiotic' => $request->antibiotic,
                'food_type_id' => $request->food_type_id ? json_encode($request->food_type_id) : null,
                'intake' => $request->intake,
                'output' => $request->output,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visit updated successfully',
                'data' => $visit
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update visit',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a visit
     */
    public function deleteVisit($visitId)
    {
        try {
            $visit = Visit::findOrFail($visitId);
            $visit->delete();

            return response()->json([
                'success' => true,
                'message' => 'Visit deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete visit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
