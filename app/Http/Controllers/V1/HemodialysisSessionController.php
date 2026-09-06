<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\HemodialysisSessionController as LegacyHemodialysisSessionController;
use App\Http\Controllers\NephrologyRegistrationController;
use App\Http\Controllers\V1\Concerns\ManagesHemodialysisSessionListing;
use App\Models\HemodialysisSession;
use App\Models\NephrologyRegistration;
use App\Models\Patient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class HemodialysisSessionController extends Controller
{
    use ManagesHemodialysisSessionListing;

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request->user());

        $filters = $this->listFilters($request);
        $query = $this->scopedSessionQuery($request);
        $filteredQuery = $this->applySessionFilters(clone $query, $filters);
        $sessions = $this->paginateSessions($filteredQuery, $filters);
        $stats = $this->sessionStats($this->applySessionFilters(clone $query, $filters));

        return Inertia::render('HemodialysisSessions/Index', [
            'sessions' => $sessions,
            'stats' => $stats,
            'filters' => array_merge([
                'patient_id' => '',
                'patient_name' => '',
                'session_date' => '',
                'date_from' => '',
                'date_to' => '',
                'doctor_id' => '',
                'status' => '',
                'per_page' => '25',
            ], $filters),
            'filterOptions' => $this->filterOptions(),
            'permissions' => $this->listPermissions($request->user()),
            'urls' => [
                'current' => route('hemodialysis-sessions.index'),
                'create' => route('hemodialysis-sessions.create'),
                'show' => url('/hemodialysis-sessions'),
                'edit' => url('/hemodialysis-sessions'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeAccess($request->user());

        $selectedPatient = null;
        $selectedRegistration = null;

        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::query()
                ->select(['id', 'name', 'last_name', 'id_card'])
                ->find($request->patient_id);
        }

        if ($request->filled('nephrology_registration_id')) {
            $selectedRegistration = NephrologyRegistration::query()
                ->with(['patient:id,name,last_name,id_card', 'disease:id,name'])
                ->find($request->nephrology_registration_id);

            if ($selectedRegistration && ! $selectedPatient) {
                $selectedPatient = $selectedRegistration->patient;
            }
        }

        return Inertia::render('HemodialysisSessions/Create', [
            'formOptions' => [
                'doctors' => $this->nephrologistDoctorsForForm()->all(),
            ],
            'prefill' => [
                'patient' => $selectedPatient ? [
                    'id' => $selectedPatient->id,
                    'name' => trim($selectedPatient->name.' '.$selectedPatient->last_name),
                    'identifier' => $selectedPatient->id_card ?? $selectedPatient->id,
                ] : null,
                'registration' => $selectedRegistration ? [
                    'id' => $selectedRegistration->id,
                    'ref_no' => $selectedRegistration->ref_no,
                    'diagnosis' => $selectedRegistration->displayDiagnosis(),
                ] : null,
            ],
            'urls' => [
                'index' => route('hemodialysis-sessions.index'),
                'store' => route('hemodialysis-sessions.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request->user());

        $validated = $request->validate($this->validationRules());

        try {
            $validated['session_date'] = LegacyHemodialysisSessionController::normalizeSessionDate($validated['session_date']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['session_date' => localize('global.invalid_session_date_format')]);
        }

        $patient = Patient::findOrFail($validated['patient_id']);
        $validated['branch_id'] = $patient->branch_id ?? $request->user()->branch_id;
        $validated = $this->applyRegistrationDefaults($validated);

        $session = HemodialysisSession::create($validated);

        return redirect()
            ->route('hemodialysis-sessions.show', $session)
            ->with('success', localize('global.hemodialysis_session_created_successfully'));
    }

    public function show(Request $request, HemodialysisSession $hemodialysisSession): Response
    {
        $this->authorizeSessionAccess($hemodialysisSession, $request->user());

        $hemodialysisSession->load([
            'patient:id,name,last_name,id_card',
            'doctor:id,name',
            'nephrologyRegistration:id,ref_no,patient_id',
            'appointment:id',
            'branch:id,name',
        ]);

        return Inertia::render('HemodialysisSessions/Show', [
            'session' => $this->transformDetail($hemodialysisSession),
            'permissions' => $this->listPermissions($request->user()),
            'urls' => $this->sessionUrls($hemodialysisSession),
        ]);
    }

    public function edit(Request $request, HemodialysisSession $hemodialysisSession): Response
    {
        $this->authorizeSessionAccess($hemodialysisSession, $request->user());
        abort_unless($this->listPermissions($request->user())['edit'], 403);

        $hemodialysisSession->load([
            'patient:id,name,last_name,id_card',
            'nephrologyRegistration:id,ref_no,patient_id',
        ]);

        return Inertia::render('HemodialysisSessions/Edit', [
            'session' => $this->transformFormSession($hemodialysisSession),
            'formOptions' => [
                'doctors' => $this->nephrologistDoctorsForForm()->all(),
            ],
            'urls' => [
                'show' => route('hemodialysis-sessions.show', $hemodialysisSession),
                'update' => route('hemodialysis-sessions.update', $hemodialysisSession),
            ],
        ]);
    }

    public function update(Request $request, HemodialysisSession $hemodialysisSession): RedirectResponse
    {
        $this->authorizeSessionAccess($hemodialysisSession, $request->user());
        abort_unless($this->listPermissions($request->user())['edit'], 403);

        $validated = $request->validate($this->validationRules($hemodialysisSession->id));

        try {
            $validated['session_date'] = LegacyHemodialysisSessionController::normalizeSessionDate($validated['session_date']);
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['session_date' => localize('global.invalid_session_date_format')]);
        }

        if (! empty($validated['nephrology_registration_id'])) {
            $registration = NephrologyRegistration::find($validated['nephrology_registration_id']);
            if ($registration && (int) $registration->patient_id !== (int) $validated['patient_id']) {
                throw ValidationException::withMessages([
                    'nephrology_registration_id' => localize('global.registration_patient_mismatch'),
                ]);
            }
        }

        $hemodialysisSession->update($validated);

        return redirect()
            ->route('hemodialysis-sessions.show', $hemodialysisSession)
            ->with('success', localize('global.hemodialysis_session_updated_successfully'));
    }

    public function destroy(HemodialysisSession $hemodialysisSession): RedirectResponse
    {
        $this->authorizeSessionAccess($hemodialysisSession, request()->user());
        abort_unless($this->listPermissions(request()->user())['delete'], 403);

        $hemodialysisSession->delete();

        return redirect()
            ->route('hemodialysis-sessions.index')
            ->with('success', localize('global.hemodialysis_session_deleted_successfully'));
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(?int $ignoreId = null): array
    {
        $doctorIds = NephrologyRegistrationController::nephrologistDoctors()->pluck('id')->all();

        return array_merge(LegacyHemodialysisSessionController::validationRules($ignoreId), [
            'doctor_id' => ['nullable', Rule::in($doctorIds)],
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyRegistrationDefaults(array $validated): array
    {
        if (empty($validated['nephrology_registration_id'])) {
            return $validated;
        }

        $registration = NephrologyRegistration::with('disease')->find($validated['nephrology_registration_id']);
        if (! $registration) {
            throw ValidationException::withMessages([
                'nephrology_registration_id' => localize('global.nephrology_registration_not_found'),
            ]);
        }

        if ((int) $registration->patient_id !== (int) $validated['patient_id']) {
            throw ValidationException::withMessages([
                'nephrology_registration_id' => localize('global.registration_patient_mismatch'),
            ]);
        }

        $validated['appointment_id'] = $registration->appointment_id;
        if (empty($validated['diagnosis'])) {
            $validated['diagnosis'] = $registration->displayDiagnosis();
        }

        return $validated;
    }

    /**
     * @return array<string, string|null>
     */
    private function sessionUrls(HemodialysisSession $hemodialysisSession): array
    {
        return [
            'index' => route('hemodialysis-sessions.index'),
            'show' => route('hemodialysis-sessions.show', $hemodialysisSession),
            'edit' => route('hemodialysis-sessions.edit', $hemodialysisSession),
            'destroy' => route('hemodialysis-sessions.destroy', $hemodialysisSession),
            'patient' => $hemodialysisSession->patient_id
                ? route('patients.show', $hemodialysisSession->patient_id)
                : null,
            'nephrologyRegistration' => $hemodialysisSession->nephrology_registration_id
                ? route('nephrology-registrations.show', $hemodialysisSession->nephrology_registration_id)
                : null,
        ];
    }
}
