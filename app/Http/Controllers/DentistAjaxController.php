<?php

namespace App\Http\Controllers;

use App\Models\DentistRegistration;
use App\Models\DentalExamination;
use App\Models\DentalTreatment;
use App\Models\DentalXray;
use App\Models\DentalNote;
use Illuminate\Http\Request;

class DentistAjaxController extends Controller
{
    /**
     * Get examinations for a registration
     */
    public function getExaminations(DentistRegistration $dentistRegistration)
    {
        try {
            $examinations = $dentistRegistration->examinations()->orderBy('examination_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $examinations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch examinations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get treatments for a registration
     */
    public function getTreatments(DentistRegistration $dentistRegistration)
    {
        try {
            $treatments = $dentistRegistration->treatments()->orderBy('treatment_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $treatments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch treatments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get X-rays for a registration
     */
    public function getXrays(DentistRegistration $dentistRegistration)
    {
        try {
            $xrays = $dentistRegistration->xrays()->orderBy('xray_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $xrays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch X-rays',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notes for a registration
     */
    public function getNotes(DentistRegistration $dentistRegistration)
    {
        try {
            $notes = $dentistRegistration->dentalNotes()->orderBy('note_date', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $notes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store examination via AJAX
     */
    public function storeExamination(Request $request, DentistRegistration $dentistRegistration)
    {
        try {
            $validatedData = $request->validate([
                'examination_date' => 'required|date',
                'chief_complaint' => 'nullable|string',
                'clinical_findings' => 'nullable|string',
                'diagnosis' => 'nullable|string',
                'treatment_plan' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            $validatedData['dentist_registration_id'] = $dentistRegistration->id;
            $examination = DentalExamination::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Examination created successfully',
                'data' => $examination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create examination',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store treatment via AJAX
     */
    public function storeTreatment(Request $request, DentistRegistration $dentistRegistration)
    {
        try {
            $validatedData = $request->validate([
                'treatment_type' => 'required|string',
                'tooth_number' => 'nullable|string',
                'treatment_description' => 'required|string',
                'treatment_date' => 'required|date',
                'status' => 'required|in:planned,in_progress,completed,cancelled',
                'cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $validatedData['dentist_registration_id'] = $dentistRegistration->id;
            $treatment = DentalTreatment::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Treatment created successfully',
                'data' => $treatment
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create treatment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get registrations for an appointment
     */
    public function getRegistrations($appointmentId)
    {
        try {
            $registrations = DentistRegistration::where('appointment_id', $appointmentId)
                ->with([
                    'appointment.patient',
                    'dentist',
                    'examinations',
                    'treatments',
                    'xrays',
                    'dentalNotes'
                ])
                ->get()
                ->map(function($registration) {
                    return [
                        'id' => $registration->id,
                        'ref_no' => $registration->ref_no,
                        'status' => $registration->status,
                        'registration_date' => $registration->registration_date,
                        'appointment' => [
                            'patient' => $registration->appointment->patient ?? null
                        ],
                        'dentist' => $registration->dentist ?? null,
                        'examinations_count' => $registration->examinations->count(),
                        'treatments_count' => $registration->treatments->count(),
                        'xrays_count' => $registration->xrays->count(),
                        'notes_count' => $registration->dentalNotes->count(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $registrations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch registrations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
