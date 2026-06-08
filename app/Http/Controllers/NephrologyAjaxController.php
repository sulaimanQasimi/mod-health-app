<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Disease;
use App\Models\NephrologyRegistration;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NephrologyAjaxController extends Controller
{
    public function getRegistrations($appointmentId)
    {
        try {
            $appointment = Appointment::findOrFail($appointmentId);
            $this->authorizeAppointment($appointment);

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
                        'diagnosis' => $registration->displayDiagnosis(),
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
                'message' => localize('global.failed_to_load_registrations'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateRegistration(Request $request, NephrologyRegistration $nephrologyRegistration)
    {
        try {
            $this->authorizeRegistration($nephrologyRegistration);

            $validatedData = $request->validate(NephrologyRegistrationController::clinicalValidationRules());

            try {
                $validatedData['visit_date'] = NephrologyRegistrationController::normalizeVisitDate($validatedData['visit_date']);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => localize('global.invalid_visit_date_format'),
                    'errors' => ['visit_date' => [localize('global.invalid_visit_date_format')]],
                ], 422);
            }

            $validatedData = NephrologyRegistrationController::applyClinicalDefaults($validatedData, $request);

            $nephrologyRegistration->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => localize('global.nephrology_registration_updated_successfully'),
                'data' => $nephrologyRegistration->fresh(['appointment.patient', 'doctor', 'patient', 'disease']),
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.validation_failed'),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => localize('global.failed_to_update_registration'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getDiseases(Request $request)
    {
        try {
            $query = Disease::query();

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
                'message' => localize('global.failed_to_load_diseases'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function authorizeRegistration(NephrologyRegistration $nephrologyRegistration): void
    {
        $branchId = auth()->user()->branch_id;
        if ($branchId && (int) $nephrologyRegistration->branch_id !== (int) $branchId) {
            abort(403, localize('global.nephrology_access_branch_denied'));
        }
    }

    private function authorizeAppointment(Appointment $appointment): void
    {
        $branchId = auth()->user()->branch_id;
        if ($branchId && (int) $appointment->branch_id !== (int) $branchId) {
            abort(403, localize('global.nephrology_access_branch_denied'));
        }
    }
}
