<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Doctor;
use App\Models\HemodialysisSession;
use App\Models\NephrologyRegistration;
use App\Models\Patient;
use Illuminate\Http\Request;

class HemodialysisSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = HemodialysisSession::with(['patient', 'doctor', 'nephrologyRegistration', 'branch']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('patient_id', 'like', '%' . $request->patient_name . '%');
            });
        }

        if ($request->filled('session_date')) {
            $query->whereDate('session_date', $request->session_date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('session_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('session_date', '<=', $request->date_to);
        }

        $sessions = $query->latest('session_date')->latest('id')->paginate(25)->withQueryString();
        $doctors = Doctor::where('active_status', true)->where('is_nephrologist', true)->get();

        if ($doctors->isEmpty()) {
            $doctors = Doctor::where('active_status', true)->get();
        }

        return view('pages.nephrology.hemodialysis.index', compact('sessions', 'doctors'));
    }

    public function create(Request $request)
    {
        $doctors = Doctor::where('active_status', true)->where('is_nephrologist', true)->get();
        if ($doctors->isEmpty()) {
            $doctors = Doctor::where('active_status', true)->get();
        }

        $selectedPatient = null;
        $selectedRegistration = null;

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        if ($request->filled('nephrology_registration_id')) {
            $selectedRegistration = NephrologyRegistration::with('patient')->find($request->nephrology_registration_id);
            if ($selectedRegistration && !$selectedPatient) {
                $selectedPatient = $selectedRegistration->patient;
            }
        }

        return view('pages.nephrology.hemodialysis.create', compact('doctors', 'selectedPatient', 'selectedRegistration'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate(self::validationRules());

        $patient = Patient::findOrFail($validatedData['patient_id']);
        $validatedData['branch_id'] = $patient->branch_id ?? auth()->user()->branch_id;

        if (!empty($validatedData['nephrology_registration_id'])) {
            $registration = NephrologyRegistration::find($validatedData['nephrology_registration_id']);
            if ($registration) {
                $validatedData['appointment_id'] = $registration->appointment_id;
                if (empty($validatedData['diagnosis']) && $registration->diagnosis) {
                    $validatedData['diagnosis'] = $registration->diagnosis;
                }
            }
        }

        $session = HemodialysisSession::create($validatedData);

        return redirect()->route('hemodialysis-sessions.show', $session)
            ->with('success', localize('global.hemodialysis_session_created_successfully'));
    }

    public function show(HemodialysisSession $hemodialysisSession)
    {
        $hemodialysisSession->load([
            'patient',
            'doctor',
            'nephrologyRegistration',
            'appointment',
            'branch',
        ]);

        return view('pages.nephrology.hemodialysis.show', compact('hemodialysisSession'));
    }

    public function edit(HemodialysisSession $hemodialysisSession)
    {
        $hemodialysisSession->load(['patient', 'nephrologyRegistration']);
        $doctors = Doctor::where('active_status', true)->where('is_nephrologist', true)->get();
        if ($doctors->isEmpty()) {
            $doctors = Doctor::where('active_status', true)->get();
        }

        return view('pages.nephrology.hemodialysis.edit', compact('hemodialysisSession', 'doctors'));
    }

    public function update(Request $request, HemodialysisSession $hemodialysisSession)
    {
        $validatedData = $request->validate(self::validationRules($hemodialysisSession->id));

        $hemodialysisSession->update($validatedData);

        return redirect()->route('hemodialysis-sessions.show', $hemodialysisSession)
            ->with('success', localize('global.hemodialysis_session_updated_successfully'));
    }

    public function destroy(HemodialysisSession $hemodialysisSession)
    {
        $hemodialysisSession->delete();

        return redirect()->route('hemodialysis-sessions.index')
            ->with('success', localize('global.hemodialysis_session_deleted_successfully'));
    }

    public static function validationRules(?int $ignoreId = null): array
    {
        return [
            'patient_id' => 'required|exists:patients,id',
            'nephrology_registration_id' => 'nullable|exists:nephrology_registrations,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'diagnosis' => 'nullable|string',
            'dialysis_schedule' => 'nullable|string|max:255',
            'session_date' => 'required|date',
            'session_time' => 'nullable|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:1|max:720',
            'vascular_access_type' => 'nullable|in:av_fistula,graft,catheter',
            'pre_blood_pressure' => 'nullable|string|max:50',
            'pre_weight' => 'nullable|numeric|min:0',
            'pre_pulse' => 'nullable|integer|min:0|max:300',
            'pre_temperature' => 'nullable|numeric|min:30|max:45',
            'post_blood_pressure' => 'nullable|string|max:50',
            'post_weight' => 'nullable|numeric|min:0',
            'post_pulse' => 'nullable|integer|min:0|max:300',
            'post_temperature' => 'nullable|numeric|min:30|max:45',
            'fluid_removed_ml' => 'nullable|numeric|min:0',
            'dialyzer_type' => 'nullable|string|max:255',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'complications_notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ];
    }
}
