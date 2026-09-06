<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NephrologyRegistrationController as LegacyNephrologyRegistrationController;
use App\Http\Controllers\V1\Concerns\ManagesNephrologyRegistrationListing;
use App\Models\NephrologyRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NephrologyRegistrationController extends Controller
{
    use ManagesNephrologyRegistrationListing;

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request->user());

        $filters = $this->listFilters($request);
        $query = $this->scopedRegistrationQuery($request);
        $filteredQuery = $this->applyRegistrationFilters(clone $query, $filters);
        $registrations = $this->paginateRegistrations($filteredQuery, $filters);
        $stats = $this->registrationStats($this->applyRegistrationFilters(clone $query, $filters));

        return Inertia::render('NephrologyRegistrations/Index', [
            'registrations' => $registrations,
            'stats' => $stats,
            'filters' => array_merge([
                'patient_id' => '',
                'patient_name' => '',
                'status' => '',
                'branch_id' => '',
                'doctor_id' => '',
                'visit_date_from' => '',
                'visit_date_to' => '',
                'per_page' => '25',
            ], $filters),
            'filterOptions' => $this->filterOptions($request),
            'permissions' => $this->listPermissions($request->user()),
            'urls' => [
                'current' => route('nephrology-registrations.index'),
                'show' => url('/nephrology-registrations'),
            ],
        ]);
    }

    public function show(Request $request, NephrologyRegistration $nephrologyRegistration): Response|RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, $request->user());

        if ($nephrologyRegistration->needsAcceptance()) {
            return redirect()
                ->route('nephrology-registrations.index')
                ->with('error', localize('global.nephrology_accept_on_index_hint'));
        }

        $nephrologyRegistration->load([
            'appointment.patient:id,name,last_name',
            'appointment.prescription:id,appointment_id',
            'appointment.diagnose:id,appointment_id',
            'appointment.patientTestRegistrations:id,testable_id,testable_type',
            'patient:id,name,last_name',
            'doctor:id,name',
            'branch:id,name',
            'disease.category:id,name',
            'hemodialysisSessions' => fn ($query) => $query
                ->with('doctor:id,name')
                ->latest('session_date')
                ->limit(10),
        ]);

        $user = $request->user();
        $formOptions = null;

        if ($this->showPermissions($user)['edit']) {
            [$diseaseCategories, $nephrologyDiseases] = $this->nephrologyDiseaseFormData();

            $formOptions = [
                'doctors' => $this->nephrologistDoctorsForForm()
                    ->map(fn ($doctor) => ['id' => $doctor->id, 'name' => $doctor->name])
                    ->values()
                    ->all(),
                'disease_categories' => $diseaseCategories
                    ->map(fn ($category) => ['id' => $category->id, 'name' => $category->name])
                    ->values()
                    ->all(),
                'diseases' => $nephrologyDiseases
                    ->map(fn ($disease) => [
                        'id' => $disease->id,
                        'name' => $disease->name,
                        'disease_category_id' => $disease->disease_category_id,
                    ])
                    ->values()
                    ->all(),
                'has_uncategorized_diseases' => $nephrologyDiseases->contains(
                    fn ($disease) => empty($disease->disease_category_id),
                ),
            ];
        }

        return Inertia::render('NephrologyRegistrations/Show', [
            'registration' => $this->transformDetail($nephrologyRegistration),
            'formOptions' => $formOptions,
            'permissions' => $this->showPermissions($user),
            'urls' => $this->registrationUrls($nephrologyRegistration),
        ]);
    }

    public function update(Request $request, NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, $request->user());
        abort_unless($this->showPermissions($request->user())['edit'], 403);

        $doctorIds = $this->nephrologistDoctorsForForm()->pluck('id')->all();
        $validated = $request->validate(array_merge(
            LegacyNephrologyRegistrationController::clinicalValidationRules(),
            [
                'doctor_id' => ['nullable', Rule::in($doctorIds)],
            ],
        ));

        try {
            $validated['visit_date'] = LegacyNephrologyRegistrationController::normalizeVisitDate($validated['visit_date']);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['visit_date' => localize('global.invalid_visit_date_format')]);
        }

        $validated = LegacyNephrologyRegistrationController::applyClinicalDefaults($validated, $request);
        $nephrologyRegistration->update($validated);

        return redirect()
            ->route('nephrology-registrations.show', $nephrologyRegistration)
            ->with('success', localize('global.nephrology_registration_updated_successfully'));
    }

    public function destroy(NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['delete'], 403);

        $nephrologyRegistration->delete();

        return redirect()
            ->route('nephrology-registrations.index')
            ->with('success', localize('global.nephrology_registration_deleted_successfully'));
    }

    public function markCompleted(NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $nephrologyRegistration->markCompleted();

        return redirect()->back()->with('success', localize('global.registration_marked_completed'));
    }

    public function markInProgress(NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $nephrologyRegistration->markInProgress();

        return redirect()->back()->with('success', localize('global.registration_marked_in_progress'));
    }

    public function accept(NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, request()->user());
        abort_unless($this->listPermissions(request()->user())['accept'], 403);

        if (in_array($nephrologyRegistration->status, ['completed', 'cancelled'], true)) {
            return redirect()->back()->with('error', localize('global.registration_cannot_be_accepted'));
        }

        if (! $nephrologyRegistration->acceptByCurrentNephrologist()) {
            return redirect()->back()->with('error', localize('global.nephrology_access_nephrologist_only'));
        }

        return redirect()
            ->route('nephrology-registrations.show', $nephrologyRegistration)
            ->with('success', localize('global.registration_accepted_successfully'));
    }

    public function cancel(NephrologyRegistration $nephrologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($nephrologyRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $nephrologyRegistration->cancel();

        return redirect()->back()->with('success', localize('global.registration_cancelled'));
    }

    /**
     * @return array<string, string|null>
     */
    private function registrationUrls(NephrologyRegistration $nephrologyRegistration): array
    {
        $patientId = $nephrologyRegistration->patient_id;

        return [
            'index' => route('nephrology-registrations.index'),
            'show' => route('nephrology-registrations.show', $nephrologyRegistration),
            'update' => route('nephrology-registrations.update', $nephrologyRegistration),
            'destroy' => route('nephrology-registrations.destroy', $nephrologyRegistration),
            'markCompleted' => route('nephrology-registrations.mark-completed', $nephrologyRegistration),
            'markInProgress' => route('nephrology-registrations.mark-in-progress', $nephrologyRegistration),
            'cancel' => route('nephrology-registrations.cancel', $nephrologyRegistration),
            'appointment' => $nephrologyRegistration->appointment_id
                ? route('appointments.show', $nephrologyRegistration->appointment_id)
                : null,
            'hemodialysisCreate' => route('hemodialysis-sessions.create', [
                'nephrology_registration_id' => $nephrologyRegistration->id,
                'patient_id' => $patientId,
            ]),
            'hemodialysisIndex' => route('hemodialysis-sessions.index', [
                'patient_id' => $patientId,
            ]),
        ];
    }
}
