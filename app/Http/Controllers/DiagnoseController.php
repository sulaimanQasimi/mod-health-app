<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Diagnose;
use Illuminate\Http\Request;

class DiagnoseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $diagnoses = Diagnose::all();
        return view('pages.diagnoses.index',compact('diagnoses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'description' => 'required',
            'patient_id' => 'required',
            'appointment_id' => 'required',
            'type' => 'required',
            'bp' => 'nullable',
            'pr' => 'nullable',
            'weight' => 'nullable',
            't' => 'nullable',
            'spo2' => 'nullable',
            'pain' => 'nullable',
        ]);

        Diagnose::create($data);

        return redirect()->back()->with('success', localize('global.diagnose_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Diagnose $diagnose)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diagnose $diagnose)
    {
        return view('pages.diagnoses.edit',compact('diagnose'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diagnose $diagnose)
    {
        $data = $request->validate([
            'description' => 'required',
            'patient_id' => 'required',
            'appointment_id' => 'required',
            'type' => 'required',
            'bp' => 'nullable',
            'pr' => 'nullable',
            'weight' => 'nullable',
            't' => 'nullable',
            'spo2' => 'nullable',
            'pain' => 'nullable',
        ]);

        $diagnose->update($data);

        return redirect()->route('appointments.index')->with('success', localize('global.diagnose_updated_successfully.'));

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diagnose $diagnose)
    {
        $item = Diagnose::findOrFail($diagnose->id);
        $item->delete();
        return redirect()->back()->with('success', localize('global.diagnose_deleted_successfully.'));

    }

    public function createDiagnose(Appointment $appointment)
    {
        return view('pages.diagnoses.create',compact('appointment'));
    }

    /**
     * Get diagnoses for a specific appointment (AJAX)
     */
    public function getAppointmentDiagnoses(Appointment $appointment)
    {
        try {
            $diagnoses = Diagnose::where('appointment_id', $appointment->id)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $diagnoses
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در بارگذاری تشخیص‌ها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store diagnosis via AJAX
     */
    public function ajaxStore(Request $request)
    {
        try {
            $data = $request->validate([
                'description' => 'required|string',
                'patient_id' => 'required|exists:patients,id',
                'appointment_id' => 'required|exists:appointments,id',
                'type' => 'required|in:0,1',
                'bp' => 'nullable|string',
                'pr' => 'nullable|string',
                'weight' => 'nullable|string',
                't' => 'nullable|string',
                'spo2' => 'nullable|string',
                'pain' => 'nullable|string',
            ]);

            $diagnose = Diagnose::create($data);

            return response()->json([
                'success' => true,
                'message' => 'تشخیص با موفقیت ایجاد شد',
                'data' => $diagnose
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ایجاد تشخیص',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update diagnosis via AJAX
     */
    public function ajaxUpdate(Request $request, Diagnose $diagnose)
    {
        try {
            $data = $request->validate([
                'description' => 'required|string',
                'type' => 'required|in:0,1',
                'bp' => 'nullable|string',
                'pr' => 'nullable|string',
                'weight' => 'nullable|string',
                't' => 'nullable|string',
                'spo2' => 'nullable|string',
                'pain' => 'nullable|string',
            ]);

            $diagnose->update($data);

            return response()->json([
                'success' => true,
                'message' => 'تشخیص با موفقیت ویرایش شد',
                'data' => $diagnose
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در اعتبارسنجی داده‌ها',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ویرایش تشخیص',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete diagnosis via AJAX
     */
    public function ajaxDelete(Diagnose $diagnose)
    {
        try {
            $diagnose->delete();

            return response()->json([
                'success' => true,
                'message' => 'تشخیص با موفقیت حذف شد'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در حذف تشخیص',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
