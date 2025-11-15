<?php

namespace App\Http\Controllers;

use App\Models\Advice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdviceAjaxController extends Controller
{
    /**
     * Get advices for a specific appointment
     */
    public function getAppointmentAdvices($appointmentId)
    {
        try {
            $advices = Advice::where('appointment_id', $appointmentId)
                ->with(['doctor'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $advices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری توصیه‌ها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store advice via AJAX
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|max:1000',
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
                'hospitalization_id' => 'nullable|exists:hospitalizations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی داده‌ها',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get doctor_id from appointment
            $appointment = \App\Models\Appointment::findOrFail($request->appointment_id);
            $data = $request->all();
            $data['doctor_id'] = $appointment->doctor_id;

            $advice = Advice::create($data);

            return response()->json([
                'success' => true,
                'message' => 'توصیه با موفقیت ایجاد شد',
                'data' => $advice->load('doctor')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد توصیه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update advice via AJAX
     */
    public function update(Request $request, Advice $advice)
    {
        try {
            $validator = Validator::make($request->all(), [
                'description' => 'required|string|max:1000',
                'appointment_id' => 'required|exists:appointments,id',
                'patient_id' => 'required|exists:patients,id',
                'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
                'hospitalization_id' => 'nullable|exists:hospitalizations,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'خطا در اعتبارسنجی داده‌ها',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get doctor_id from appointment
            $appointment = \App\Models\Appointment::findOrFail($request->appointment_id);
            $data = $request->all();
            $data['doctor_id'] = $appointment->doctor_id;

            $advice->update($data);

            return response()->json([
                'success' => true,
                'message' => 'توصیه با موفقیت ویرایش شد',
                'data' => $advice->load('doctor')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش توصیه',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete advice via AJAX
     */
    public function delete(Advice $advice)
    {
        try {
            $advice->delete();

            return response()->json([
                'success' => true,
                'message' => 'توصیه با موفقیت حذف شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف توصیه',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
