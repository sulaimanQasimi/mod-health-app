<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consultation;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\ICU;
use Illuminate\Support\Facades\Log;
use HanifHefaz\Dcter\Dcter;

class ConsultationAjaxController extends Controller
{
    public function branches()
    {
        try {
            $branches = Branch::all();
            return response()->json([
                'success' => true,
                'data' => $branches
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading branches: ' . $e->getMessage()
            ], 500);
        }
    }

    public function departments()
    {
        try {
            $departments = Department::all();
            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading departments: ' . $e->getMessage()
            ], 500);
        }
    }

    public function appointmentConsultations($id, $type = 'appointment')
    {
        try {
            if ($type == 'appointment') {
                $appointment = Appointment::findOrFail($id);
                $consultations = $appointment->consultations()->with(['comments.doctor'])->get();
            } else {
                $icu = ICU::findOrFail($id);
                $consultations = $icu->consultations()->with(['comments.doctor'])->get();
            }
          
            $consultations = $consultations->map(function ($consultation) {
                // Load the associated departments using the accessor
                $departments = $consultation->associated_departments;
                $consultation->associated_departments = $departments;
                
                // Convert date to Jalali format
                if ($consultation->date) {
                    try {
                        $consultation->jalali_date = Dcter::GregorianToJalali(Dcter::Carbonize($consultation->date));
                    } catch (\Exception $e) {
                        $consultation->jalali_date = $consultation->date;
                    }
                } else {
                    $consultation->jalali_date = '';
                }
                
                return $consultation;
            });
            
            return response()->json([
                'success' => true,
                'data' => $consultations
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading consultations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate required fields first
            $validatedData = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'branch_id' => 'required|exists:branches,id',
                'title' => 'required|string|max:255',
                'branch' => 'required',
                'department_id' => 'required|array|min:1',
                'department_id.*' => 'exists:departments,id',
                'consultation_type' => 'required|in:0,1',
                'date' => 'required|string',
                'time' => 'required|string'
            ]);

            // Handle appointment_id and i_c_u_id separately
            $appointmentId = $request->input('appointment_id');
            $icuId = $request->input('i_c_u_id');

            // Ensure either appointment_id or i_c_u_id is provided
            if (empty($appointmentId) && empty($icuId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Either appointment_id or i_c_u_id must be provided'
                ], 422);
            }

            // Validate the specific ID if provided
            if ($appointmentId && !Appointment::find($appointmentId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid appointment_id'
                ], 422);
            }

            if ($icuId && !ICU::find($icuId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid i_c_u_id'
                ], 422);
            }

            // Convert Persian date to Gregorian format if needed
            $date = Dcter::JalaliToGregorian(Dcter::Carbonize($validatedData['date']));
            $validatedData['date'] = $date;


            // Create consultation
            $consultationData = [
                'patient_id' => $validatedData['patient_id'],
                'branch_id' => $validatedData['branch_id'],
                'title' => $validatedData['title'],
                'branch' => $validatedData['branch'],
                'consultation_type' => $validatedData['consultation_type'],
                'date' => $validatedData['date'],
                'time' => $validatedData['time']
            ];

            // Add either appointment_id or i_c_u_id
            if ($appointmentId) {
                $consultationData['appointment_id'] = $appointmentId;
                $consultationData['i_c_u_id'] = null;
            }
            if ($icuId) {
                $consultationData['i_c_u_id'] = $icuId;
                // For ICU consultations, we need to get the appointment_id from the ICU
                $icu = ICU::find($icuId);
                $consultationData['appointment_id'] = $icu ? $icu->appointment_id : null;
            }

            $consultation = Consultation::create($consultationData);
            
            // Store department IDs as JSON in the department_id field
            $consultation->department_id = json_encode($validatedData['department_id']);
            $consultation->save();

            // Load the associated departments for the response
            $departments = $consultation->associated_departments;
            $consultation->associated_departments = $departments;
            
            // Convert date to Jalali format for response
            if ($consultation->date) {
                try {
                    $consultation->jalali_date = Dcter::GregorianToJalali(Dcter::Carbonize($consultation->date));
                } catch (\Exception $e) {
                    $consultation->jalali_date = $consultation->date;
                }
            } else {
                $consultation->jalali_date = '';
            }

            return response()->json([
                'success' => true,
                'message' => 'Consultation created successfully',
                'data' => $consultation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating consultation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $consultationId)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'branch' => 'required',
                'department_id' => 'required|array|min:1',
                'department_id.*' => 'exists:departments,id',
                'consultation_type' => 'required|in:0,1'
            ]);

            // Date and time are not editable in update

            // Find the consultation
            $consultation = Consultation::findOrFail($consultationId);

            // Update consultation (date and time are not editable)
            $consultation->update([
                'title' => $validatedData['title'],
                'branch' => $validatedData['branch'],
                'consultation_type' => $validatedData['consultation_type']
            ]);

            // Update department IDs as JSON in the department_id field
            $consultation->department_id = json_encode($validatedData['department_id']);
            $consultation->save();

            // Load the associated departments for the response
            $departments = $consultation->associated_departments;
            $consultation->associated_departments = $departments;
            
            // Convert date to Jalali format for response
            if ($consultation->date) {
                try {
                    $consultation->jalali_date = Dcter::GregorianToJalali(Dcter::Carbonize($consultation->date));
                } catch (\Exception $e) {
                    $consultation->jalali_date = $consultation->date;
                }
            } else {
                $consultation->jalali_date = '';
            }

            return response()->json([
                'success' => true,
                'message' => 'Consultation updated successfully',
                'data' => $consultation
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating consultation: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete($consultationId)
    {
        try {
            $consultation = Consultation::findOrFail($consultationId);
            $consultation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Consultation deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting consultation: ' . $e->getMessage()
            ], 500);
        }
    }
}
