<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Disease;
use App\Models\NephrologyRegistration;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

class NephrologyRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = NephrologyRegistration::with(['appointment.patient', 'doctor', 'patient', 'branch', 'disease']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }

        $registrations = $query->latest()->paginate(25)->withQueryString();
        $branches = Branch::all();
        $doctors = Doctor::where('active_status', true)->get();

        return view('pages.nephrology.registrations.index', compact('registrations', 'branches', 'doctors'));
    }

    public function create(Appointment $appointment)
    {
        $doctors = Doctor::where('active_status', true)->get();

        return view('pages.nephrology.registrations.create', compact('appointment', 'doctors'));
    }

    public function store(Request $request, Appointment $appointment)
    {
        $validatedData = $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'visit_date' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $validatedData['visit_date'] = self::normalizeVisitDate($validatedData['visit_date']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['visit_date' => 'Invalid date format. Please use Persian date format.']);
        }

        $validatedData['appointment_id'] = $appointment->id;
        $validatedData['patient_id'] = $appointment->patient_id;
        $validatedData['branch_id'] = $appointment->branch_id ?? auth()->user()->branch_id;

        if (empty($validatedData['doctor_id']) && $appointment->doctor_id) {
            $validatedData['doctor_id'] = $appointment->doctor_id;
        }

        $registration = NephrologyRegistration::create($validatedData);
        $registration->assignToCurrentDoctorIfMissing();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.nephrology_registration_created_successfully'),
                'data' => $registration->load(['appointment.patient', 'doctor', 'patient']),
                'redirect' => route('nephrology-registrations.show', $registration),
            ]);
        }

        return redirect()->route('nephrology-registrations.show', $registration)
            ->with('success', localize('global.nephrology_registration_created_successfully'));
    }

    /**
     * Open existing nephrology record for appointment or create a new one.
     */
    public function open(Appointment $appointment)
    {
        $registration = NephrologyRegistration::where('appointment_id', $appointment->id)
            ->latest()
            ->first();

        if (!$registration) {
            $registration = NephrologyRegistration::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'visit_date' => $appointment->date ?? now()->toDateString(),
                'branch_id' => $appointment->branch_id ?? auth()->user()->branch_id,
                'status' => 'in_progress',
            ]);
            $registration->assignToCurrentDoctorIfMissing();
        }

        return redirect()->route('nephrology-registrations.show', $registration);
    }

    public function show(NephrologyRegistration $nephrologyRegistration)
    {
        $nephrologyRegistration->load([
            'appointment.patient',
            'appointment.prescription',
            'appointment.diagnose',
            'appointment.patientTestRegistrations',
            'patient',
            'doctor',
            'branch',
            'disease',
            'hemodialysisSessions',
        ]);

        $doctors = Doctor::where('active_status', true)->get();
        $appointment = $nephrologyRegistration->appointment;
        $nephrologyDiseases = Disease::forNephrology()->get();
        $hemodialysisSessions = $nephrologyRegistration->hemodialysisSessions()
            ->with('doctor')
            ->latest('session_date')
            ->limit(10)
            ->get();

        return view('pages.nephrology.registrations.show', compact(
            'nephrologyRegistration',
            'doctors',
            'appointment',
            'nephrologyDiseases',
            'hemodialysisSessions'
        ));
    }

    public function edit(NephrologyRegistration $nephrologyRegistration)
    {
        $doctors = Doctor::where('active_status', true)->get();
        $nephrologyDiseases = Disease::forNephrology()->get();

        return view('pages.nephrology.registrations.edit', compact('nephrologyRegistration', 'doctors', 'nephrologyDiseases'));
    }

    public function update(Request $request, NephrologyRegistration $nephrologyRegistration)
    {
        $validatedData = $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'visit_date' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'disease_id' => 'nullable|exists:diseases,id',
            'ckd_aki_stage' => 'nullable|string|max:50',
            'dialysis_required' => 'nullable|boolean',
            'dialysis_type' => 'nullable|in:HD,PD,CRRT',
            'access_type' => 'nullable|in:av_fistula,graft,catheter',
            'notes' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
        ]);

        try {
            $validatedData['visit_date'] = self::normalizeVisitDate($validatedData['visit_date']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['visit_date' => 'Invalid date format. Please use Persian date format.']);
        }

        $validatedData['dialysis_required'] = $request->boolean('dialysis_required');

        if (!$validatedData['dialysis_required']) {
            $validatedData['dialysis_type'] = null;
            $validatedData['access_type'] = null;
        }

        $nephrologyRegistration->update($validatedData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.nephrology_registration_updated_successfully'),
                'data' => $nephrologyRegistration->fresh(['appointment.patient', 'doctor', 'patient']),
            ]);
        }

        return redirect()->route('nephrology-registrations.show', $nephrologyRegistration)
            ->with('success', localize('global.nephrology_registration_updated_successfully'));
    }

    public function destroy(NephrologyRegistration $nephrologyRegistration)
    {
        $nephrologyRegistration->delete();

        return redirect()->route('nephrology-registrations.index')
            ->with('success', localize('global.nephrology_registration_deleted_successfully'));
    }

    public function markCompleted(NephrologyRegistration $nephrologyRegistration)
    {
        $nephrologyRegistration->markCompleted();

        return redirect()->back()->with('success', localize('global.registration_marked_completed'));
    }

    public function markInProgress(NephrologyRegistration $nephrologyRegistration)
    {
        $nephrologyRegistration->markInProgress();

        return redirect()->back()->with('success', localize('global.registration_marked_in_progress'));
    }

    public function cancel(NephrologyRegistration $nephrologyRegistration)
    {
        $nephrologyRegistration->cancel();

        return redirect()->back()->with('success', localize('global.registration_cancelled'));
    }

    public static function clinicalValidationRules(): array
    {
        return [
            'doctor_id' => 'nullable|exists:doctors,id',
            'visit_date' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'disease_id' => 'nullable|exists:diseases,id',
            'ckd_aki_stage' => 'nullable|string|max:50',
            'dialysis_required' => 'nullable|boolean',
            'dialysis_type' => 'nullable|in:HD,PD,CRRT',
            'access_type' => 'nullable|in:av_fistula,graft,catheter',
            'notes' => 'nullable|string',
            'follow_up_plan' => 'nullable|string',
        ];
    }

    public static function normalizeVisitDate(string $visitDate): string
    {
        return Verta::parse($visitDate)->datetime()->format('Y-m-d');
    }
}
