<?php

namespace App\Http\Controllers;

use App\Models\Disease;
use App\Models\NephrologyRegistration;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NephrologyAjaxController extends Controller
{
    public function getRegistrations($appointmentId)
    {
        try {
            $registrations = NephrologyRegistration::where('appointment_id', $appointmentId)
                ->with(['appointment.patient', 'doctor', 'patient', 'disease'])
                ->latest()
                ->get()
                ->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'ref_no' => $registration->ref_no,
                        'status' => $registration->status,
                        'visit_date' => $registration->visit_date,
                        'diagnosis' => $registration->diagnosis,
                        'disease_id' => $registration->disease_id,
                        'disease' => $registration->disease ? [
                            'id' => $registration->disease->id,
                            'name' => $registration->disease->name,
                        ] : null,
                        'appointment' => [
                            'patient' => $registration->appointment->patient ?? $registration->patient,
                        ],
                        'doctor' => $registration->doctor,
                        'open_url' => route('nephrology-registrations.show', $registration),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $registrations,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch nephrology registrations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRegistration(Request $request, NephrologyRegistration $nephrologyRegistration)
    {
        try {
            $validatedData = $request->validate(NephrologyRegistrationController::clinicalValidationRules());
            $validatedData['dialysis_required'] = $request->boolean('dialysis_required');

            try {
                $validatedData['visit_date'] = NephrologyRegistrationController::normalizeVisitDate($validatedData['visit_date']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format. Please use Persian date format.',
                    'errors' => ['visit_date' => ['Invalid date format. Please use Persian date format.']],
                ], 422);
            }

            if (!$validatedData['dialysis_required']) {
                $validatedData['dialysis_type'] = null;
                $validatedData['access_type'] = null;
            }

            $nephrologyRegistration->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => localize('global.nephrology_registration_updated_successfully'),
                'data' => $nephrologyRegistration->fresh(['appointment.patient', 'doctor', 'patient']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update nephrology registration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDiseases(Request $request)
    {
        try {
            $query = Disease::forNephrology();

            if ($request->filled('disease_category_id')) {
                if ($request->disease_category_id === 'none') {
                    $query->whereNull('disease_category_id');
                } else {
                    $query->where('disease_category_id', (int) $request->disease_category_id);
                }
            }

            $diseases = $query->orderBy('name')->get(['id', 'name', 'disease_category_id']);

            return response()->json([
                'success' => true,
                'data' => $diseases,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch nephrology diseases',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
