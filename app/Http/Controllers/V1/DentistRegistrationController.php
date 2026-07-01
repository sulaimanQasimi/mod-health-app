<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDentistRegistrationListing;
use App\Models\DentalNote;
use App\Models\DentalTreatment;
use App\Models\DentalXray;
use App\Models\DentistRegistration;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DentistRegistrationController extends Controller
{
    use ManagesDentistRegistrationListing;

    public function index(Request $request): Response
    {
        $this->authorizeAccess($request->user());

        $filters = $this->listFilters($request);
        $user = $request->user();
        $query = $this->scopedRegistrationQuery($request);
        $filteredQuery = $this->applyRegistrationFilters(clone $query, $filters, $user->branch_id);
        $registrations = $this->paginateRegistrations($filteredQuery, $filters);
        $stats = $this->registrationStats($this->applyRegistrationFilters(clone $query, $filters, $user->branch_id));

        return Inertia::render('DentistRegistrations/Index', [
            'registrations' => $registrations,
            'stats' => $stats,
            'filters' => array_merge([
                'search' => '',
                'status' => '',
                'branch_id' => '',
                'dentist_id' => '',
                'sort_by' => 'created_at',
                'sort_order' => 'desc',
                'per_page' => '25',
            ], $filters),
            'filterOptions' => $this->filterOptions($request),
            'permissions' => $this->listPermissions($request->user()),
            'urls' => [
                'current' => route('react.dentist-registrations.index'),
                'show' => url('/react/dentist-registrations'),
            ],
        ]);
    }

    public function show(Request $request, DentistRegistration $dentistRegistration): Response
    {
        $this->authorizeRegistrationAccess($dentistRegistration, $request->user());

        $dentistRegistration->load([
            'appointment.patient:id,name,last_name',
            'appointment:id,patient_id,date,is_completed',
            'appointment.prescription:id,appointment_id',
            'dentist:id,name',
            'branch:id,name',
            'treatments' => fn ($query) => $query->orderByDesc('treatment_date'),
            'xrays' => fn ($query) => $query->orderByDesc('xray_date'),
            'dentalNotes' => fn ($query) => $query->orderByDesc('note_date'),
            'dentalCharts',
        ]);

        $user = $request->user();
        $formOptions = null;
        if ($this->showPermissions($user)['edit']) {
            $formOptions = [
                'dentists' => $this->dentistDoctorsForForm()->map(fn ($doctor) => [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                ])->values()->all(),
            ];
        }

        return Inertia::render('DentistRegistrations/Show', [
            'registration' => $this->transformDetail($dentistRegistration),
            'formOptions' => $formOptions,
            'permissions' => $this->showPermissions($user),
            'urls' => $this->registrationUrls($dentistRegistration),
        ]);
    }

    public function update(Request $request, DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, $request->user());
        abort_unless($this->showPermissions($request->user())['edit'], 403);

        $dentistIds = $this->dentistDoctorsForForm()->pluck('id')->all();

        $validated = $request->validate([
            'dentist_id' => ['nullable', Rule::in($dentistIds)],
            'registration_date' => 'required|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);

        $dentistRegistration->update([
            'dentist_id' => $validated['dentist_id'] ?? null,
            'registration_date' => Verta::parse($validated['registration_date'])->datetime(),
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('react.dentist-registrations.show', $dentistRegistration)
            ->with('success', localize('global.dentist_registration_updated_successfully'));
    }

    public function destroy(DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['delete'], 403);

        $dentistRegistration->delete();

        return redirect()
            ->route('react.dentist-registrations.index')
            ->with('success', localize('global.dentist_registration_deleted_successfully'));
    }

    public function markCompleted(DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $dentistRegistration->markCompleted();

        return redirect()->back()->with('success', localize('global.registration_marked_completed'));
    }

    public function markInProgress(DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $dentistRegistration->markInProgress();

        return redirect()->back()->with('success', localize('global.registration_marked_in_progress'));
    }

    public function cancel(DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['markStatus'], 403);

        $dentistRegistration->cancel();

        return redirect()->back()->with('success', localize('global.registration_cancelled'));
    }

    public function storeTreatment(Request $request, DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, $request->user());
        abort_unless($this->showPermissions($request->user())['manageTreatments'], 403);

        $validated = $request->validate([
            'treatment_type' => 'required|string|max:255',
            'tooth_number' => 'nullable|integer|min:11|max:48',
            'treatment_description' => 'required|string',
            'treatment_date' => 'required|string',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        DentalTreatment::create([
            'dentist_registration_id' => $dentistRegistration->id,
            'treatment_type' => $validated['treatment_type'],
            'tooth_number' => $validated['tooth_number'] ?? null,
            'treatment_description' => $validated['treatment_description'],
            'treatment_date' => Verta::parse($validated['treatment_date'])->datetime(),
            'status' => $validated['status'],
            'cost' => $validated['cost'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', localize('global.treatment_created_successfully'));
    }

    public function destroyTreatment(
        DentistRegistration $dentistRegistration,
        DentalTreatment $dentalTreatment,
    ): RedirectResponse {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['manageTreatments'], 403);
        abort_unless((int) $dentalTreatment->dentist_registration_id === (int) $dentistRegistration->id, 404);

        $dentalTreatment->delete();

        return redirect()->back()->with('success', localize('global.treatment_deleted_successfully'));
    }

    public function storeXray(Request $request, DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, $request->user());
        abort_unless($this->showPermissions($request->user())['manageXrays'], 403);

        $validated = $request->validate([
            'xray_type' => 'required|string|max:255',
            'xray_date' => 'required|string',
            'file' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:10240',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data = [
            'dentist_registration_id' => $dentistRegistration->id,
            'xray_type' => $validated['xray_type'],
            'xray_date' => Verta::parse($validated['xray_date'])->datetime(),
            'description' => $validated['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time().'_'.$file->getClientOriginalName();
            $data['file_path'] = $file->storeAs('dental_xrays', $filename, 'public');
        }

        DentalXray::create($data);

        return redirect()->back()->with('success', localize('global.xray_created_successfully'));
    }

    public function destroyXray(
        DentistRegistration $dentistRegistration,
        DentalXray $dentalXray,
    ): RedirectResponse {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['manageXrays'], 403);
        abort_unless((int) $dentalXray->dentist_registration_id === (int) $dentistRegistration->id, 404);

        if ($dentalXray->file_path && Storage::disk('public')->exists($dentalXray->file_path)) {
            Storage::disk('public')->delete($dentalXray->file_path);
        }

        $dentalXray->delete();

        return redirect()->back()->with('success', localize('global.xray_deleted_successfully'));
    }

    public function storeNote(Request $request, DentistRegistration $dentistRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($dentistRegistration, $request->user());
        abort_unless($this->showPermissions($request->user())['manageNotes'], 403);

        $validated = $request->validate([
            'note_date' => 'required|string',
            'note_type' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        DentalNote::create([
            'dentist_registration_id' => $dentistRegistration->id,
            'note_date' => Verta::parse($validated['note_date'])->datetime(),
            'note_type' => $validated['note_type'],
            'content' => $validated['content'],
        ]);

        return redirect()->back()->with('success', localize('global.note_created_successfully'));
    }

    public function destroyNote(
        DentistRegistration $dentistRegistration,
        DentalNote $dentalNote,
    ): RedirectResponse {
        $this->authorizeRegistrationAccess($dentistRegistration, request()->user());
        abort_unless($this->showPermissions(request()->user())['manageNotes'], 403);
        abort_unless((int) $dentalNote->dentist_registration_id === (int) $dentistRegistration->id, 404);

        $dentalNote->delete();

        return redirect()->back()->with('success', localize('global.note_deleted_successfully'));
    }

    /**
     * @return array<string, string|null>
     */
    private function registrationUrls(DentistRegistration $dentistRegistration): array
    {
        return [
            'index' => route('react.dentist-registrations.index'),
            'show' => route('react.dentist-registrations.show', $dentistRegistration),
            'update' => route('react.dentist-registrations.update', $dentistRegistration),
            'destroy' => route('react.dentist-registrations.destroy', $dentistRegistration),
            'markCompleted' => route('react.dentist-registrations.mark-completed', $dentistRegistration),
            'markInProgress' => route('react.dentist-registrations.mark-in-progress', $dentistRegistration),
            'cancel' => route('react.dentist-registrations.cancel', $dentistRegistration),
            'storeTreatment' => route('react.dentist-registrations.treatments.store', $dentistRegistration),
            'storeXray' => route('react.dentist-registrations.xrays.store', $dentistRegistration),
            'storeNote' => route('react.dentist-registrations.notes.store', $dentistRegistration),
            'appointment' => $dentistRegistration->appointment_id
                ? route('react.appointments.show', $dentistRegistration->appointment_id)
                : null,
            'chartIndex' => route('react.dental-charts.index', $dentistRegistration),
            'chartCreate' => route('react.dental-charts.create', $dentistRegistration),
            'chartStore' => route('react.dental-charts.store', $dentistRegistration),
            'chartHistory' => route('react.dental-charts.history', $dentistRegistration),
            'chartCompare' => route('react.dental-charts.compare', $dentistRegistration),
            'chartPrint' => route('react.dental-charts.print', $dentistRegistration),
            'chartExport' => route('react.dental-charts.export', $dentistRegistration),
        ];
    }
}
