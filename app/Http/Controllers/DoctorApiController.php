<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorApiController extends Controller
{
    /**
     * Get filtered doctors list
     */
    public function getDoctors(Request $request)
    {
        $is_dentist = $request->filled('is_dentist') ? $request->is_dentist : 0;
        try {
            $query = Doctor::with(['department', 'branch']);
            // Dentist filter
            $query->when($is_dentist == 1, function ($query) {
                return $query->where('is_dentist', 1);
            });

            // Branch filter (default to current user's branch if not specified)
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            } else {
                $query->where('branch_id', auth()->user()->branch_id);
            }

            // Active status filter (default to active only)
            if ($request->filled('active_status')) {
                $query->where('active_status', $request->active_status == '1');
            } else {
                $query->where('active_status', true);
            }

            // Clinic type filter
            if ($request->filled('clinic_type')) {
                $query->where('clinic_type', $request->clinic_type);
            }

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Gender filter
            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%")
                        ->orWhere('qualification', 'like', "%{$search}%");
                });
            }

            // Order by name
            $doctors = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $doctors,
                'message' => localize('global.doctors_retrieved_successfully') ?: 'Doctors retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_doctors') ?: 'Failed to retrieve doctors',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hospital doctors (clinic_type = hospital and active_status = true)
     */
    public function getHospitalDoctors(Request $request)
    {
        try {
            $query = Doctor::with(['department', 'branch'])
                ->where('clinic_type', 'hospital')
                ->where('active_status', true);

            // Branch filter (default to current user's branch if not specified)
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            } else {
                $query->where('branch_id', auth()->user()->branch_id);
            }

            // Department filter
            if ($request->filled('department_id')) {
                $query->where('department_id', $request->department_id);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%");
                });
            }

            // Order by name
            $doctors = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $doctors,
                'message' => localize('global.hospital_doctors_retrieved_successfully') ?: 'Hospital doctors retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_retrieve_doctors') ?: 'Failed to retrieve doctors',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
