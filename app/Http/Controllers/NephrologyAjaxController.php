<?php

namespace App\Http\Controllers;

use App\Models\NephrologyRegistration;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NephrologyAjaxController extends Controller
{
    public function getRegistrations($appointmentId)
    {
        try {
            $registrations = NephrologyRegistration::where('appointment_id', $appointmentId)
                ->with(['appointment.patient', 'doctor', 'patient'])
                ->latest()
                ->get()
                ->map(function ($registration) {
                    return [
                        'id' => $registration->id,
                        'ref_no' => $registration->ref_no,
                        'status' => $registration->status,
                        'visit_date' => $registration->visit_date,
                        'diagnosis' => $registration->diagnosis,
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
}
