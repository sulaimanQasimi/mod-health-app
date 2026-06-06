<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Doctor;
use App\Models\Disease;
use App\Models\DiseaseCategory;
use App\Models\NephrologyRegistration;
use App\Rules\NephrologyDisease;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

class NephrologyRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $query = NephrologyRegistration::with(['appointment.patient', 'doctor', 'patient', 'branch', 'disease']);

        if (auth()->user()->branch_id) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('patient_id')) {
            $patientIdInput = trim($request->patient_id);
            $query->where(function ($q) use ($patientIdInput) {
                if (is_numeric($patientIdInput)) {
                    $q->where('patient_id', (int) $patientIdInput);
                } else {
                    $q->whereHas('patient', function ($patientQ) use ($patientIdInput) {
                        $patientQ->where('id_card', 'like', '%' . $patientIdInput . '%');
                    });
                }
            });
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->patient_name . '%')
                    ->orWhere('last_name', 'like', '%' . $request->patient_name . '%');
            });
        }

        if ($request->filled('visit_date_from')) {
            try {
                $query->whereDate('visit_date', '>=', self::normalizeVisitDate($request->visit_date_from));
            } catch (\Exception $e) {
                // ignore invalid filter
            }
        }

        if ($request->filled('visit_date_to')) {
            try {
                $query->whereDate('visit_date', '<=', self::normalizeVisitDate($request->visit_date_to));
            } catch (\Exception $e) {
                // ignore invalid filter
            }
        }

        $registrations = $query->latest()->paginate(25)->withQueryString();
        $branches = auth()->user()->branch_id
            ? Branch::where('id', auth()->user()->branch_id)->get()
            : Branch::all();
        $doctors = self::nephrologistDoctors();

        return view('pages.nephrology.registrations.index', compact('registrations', 'branches', 'doctors'));
    }

    public function create(Appointment $appointment)
    {
        $doctors = self::nephrologistDoctors();

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
            return self::visitDateErrorResponse($request);
        }

        $registration = NephrologyRegistration::where('appointment_id', $appointment->id)
            ->latest()
            ->first();

        if ($registration) {
            $registration->update(array_filter([
                'doctor_id' => $validatedData['doctor_id'] ?? null,
                'visit_date' => $validatedData['visit_date'],
                'notes' => $validatedData['notes'] ?? null,
            ], fn ($value) => $value !== null));

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => localize('global.nephrology_registration_updated_successfully'),
                    'data' => $registration->load(['appointment.patient', 'doctor', 'patient']),
                    'redirect' => route('nephrology-registrations.show', $registration),
                ]);
            }

            return redirect()->route('nephrology-registrations.show', $registration)
                ->with('success', localize('global.nephrology_registration_updated_successfully'));
        }

        $validatedData['appointment_id'] = $appointment->id;
        $validatedData['patient_id'] = $appointment->patient_id;
        $validatedData['branch_id'] = $appointment->branch_id ?? auth()->user()->branch_id;
        $validatedData['status'] = 'in_progress';

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
        $this->authorizeRegistration($nephrologyRegistration);

        $nephrologyRegistration->load([
            'appointment.patient',
            'appointment.prescription',
            'appointment.diagnose',
            'appointment.patientTestRegistrations',
            'patient',
            'doctor',
            'branch',
            'disease.category',
            'hemodialysisSessions',
        ]);

        $doctors = self::nephrologistDoctors();
        $appointment = $nephrologyRegistration->appointment;
        [$diseaseCategories, $nephrologyDiseases] = $this->nephrologyDiseaseFormData();
        $hemodialysisSessions = $nephrologyRegistration->hemodialysisSessions()
            ->with('doctor')
            ->latest('session_date')
            ->limit(10)
            ->get();

        return view('pages.nephrology.registrations.show', compact(
            'nephrologyRegistration',
            'doctors',
            'appointment',
            'diseaseCategories',
            'nephrologyDiseases',
            'hemodialysisSessions'
        ));
    }

    public function edit(NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);

        $doctors = self::nephrologistDoctors();
        [$diseaseCategories, $nephrologyDiseases] = $this->nephrologyDiseaseFormData();

        return view('pages.nephrology.registrations.edit', compact(
            'nephrologyRegistration',
            'doctors',
            'diseaseCategories',
            'nephrologyDiseases'
        ));
    }

    public function update(Request $request, NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);

        $validatedData = $request->validate(self::clinicalValidationRules());

        try {
            $validatedData['visit_date'] = self::normalizeVisitDate($validatedData['visit_date']);
        } catch (\Exception $e) {
            return self::visitDateErrorResponse($request);
        }

        $validatedData = self::applyClinicalDefaults($validatedData, $request);

        $nephrologyRegistration->update($validatedData);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => localize('global.nephrology_registration_updated_successfully'),
                'data' => $nephrologyRegistration->fresh(['appointment.patient', 'doctor', 'patient', 'disease']),
            ]);
        }

        return redirect()->route('nephrology-registrations.show', $nephrologyRegistration)
            ->with('success', localize('global.nephrology_registration_updated_successfully'));
    }

    public function destroy(NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);

        $nephrologyRegistration->delete();

        return redirect()->route('nephrology-registrations.index')
            ->with('success', localize('global.nephrology_registration_deleted_successfully'));
    }

    public function markCompleted(NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);
        $nephrologyRegistration->markCompleted();

        return redirect()->back()->with('success', localize('global.registration_marked_completed'));
    }

    public function markInProgress(NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);
        $nephrologyRegistration->markInProgress();

        return redirect()->back()->with('success', localize('global.registration_marked_in_progress'));
    }

    public function cancel(NephrologyRegistration $nephrologyRegistration)
    {
        $this->authorizeRegistration($nephrologyRegistration);
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
            'disease_id' => ['nullable', 'exists:diseases,id', new NephrologyDisease()],
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
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($visitDate))) {
            return trim($visitDate);
        }

        return Verta::parse($visitDate)->datetime()->format('Y-m-d');
    }

    public static function applyClinicalDefaults(array $validatedData, Request $request): array
    {
        $validatedData['dialysis_required'] = $request->boolean('dialysis_required');

        if (!$validatedData['dialysis_required']) {
            $validatedData['dialysis_type'] = null;
            $validatedData['access_type'] = null;
        }

        if (!empty($validatedData['disease_id'])) {
            $disease = Disease::find($validatedData['disease_id']);
            if ($disease) {
                $validatedData['diagnosis'] = $disease->name;
            }
        }

        return $validatedData;
    }

    public static function nephrologistDoctors()
    {
        $doctors = Doctor::where('active_status', true)->where('is_nephrologist', true)->get();

        if ($doctors->isEmpty()) {
            $doctors = Doctor::where('active_status', true)->get();
        }

        return $doctors;
    }

    /**
     * @return array{0: \Illuminate\Support\Collection, 1: \Illuminate\Support\Collection}
     */
    private function nephrologyDiseaseFormData(): array
    {
        $nephrologyDiseases = Disease::forNephrology()
            ->orderBy('name')
            ->get(['id', 'name', 'disease_category_id']);

        $categoryIds = $nephrologyDiseases->pluck('disease_category_id')->filter()->unique();

        $diseaseCategories = DiseaseCategory::query()
            ->whereIn('id', $categoryIds)
            ->orderBy('name')
            ->get();

        return [$diseaseCategories, $nephrologyDiseases];
    }

    private function authorizeRegistration(NephrologyRegistration $nephrologyRegistration): void
    {
        $branchId = auth()->user()->branch_id;
        if ($branchId && (int) $nephrologyRegistration->branch_id !== (int) $branchId) {
            abort(403, localize('global.nephrology_access_branch_denied'));
        }
    }

    private static function visitDateErrorResponse(Request $request)
    {
        $message = localize('global.invalid_visit_date_format');

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => ['visit_date' => [$message]],
            ], 422);
        }

        return redirect()->back()
            ->withInput()
            ->withErrors(['visit_date' => $message]);
    }
}
