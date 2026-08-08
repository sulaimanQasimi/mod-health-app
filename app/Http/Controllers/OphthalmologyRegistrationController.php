<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\OphthalmologyRegistration;
use Hekmatinasser\Verta\Verta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OphthalmologyRegistrationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAccess($request);

        $query = OphthalmologyRegistration::query()
            ->with([
                'appointment.patient:id,name,last_name,id_card',
                'examiner:id,name',
            ])
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $search = $request->string('search')->toString();
                $builder->where(function (Builder $nested) use ($search) {
                    $nested->where('ref_no', 'like', "%{$search}%")
                        ->orWhereHas('appointment.patient', function (Builder $patient) use ($search) {
                            $patient->where('name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhere('id_card', 'like', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->status))
            ->when($request->filled('examiner_id'), fn (Builder $builder) => $builder->where('examiner_id', $request->examiner_id))
            ->latest();

        $registrations = $query->paginate(25)->withQueryString();

        $doctors = Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('pages.ophthalmology.registrations.index', compact('registrations', 'doctors'));
    }

    public function show(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): View
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        $ophthalmologyRegistration->load([
            'appointment.patient',
            'appointment',
            'examiner:id,name',
        ]);

        $doctors = Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->where('branch_id', $ophthalmologyRegistration->branch_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $canEdit = ! $ophthalmologyRegistration->appointment?->is_completed
            && $request->user()->can('edit-ophthalmology-registrations');
        $canChangeStatus = ! $ophthalmologyRegistration->appointment?->is_completed
            && $request->user()->can('change-ophthalmology-status');
        $canUpload = ! $ophthalmologyRegistration->appointment?->is_completed
            && $request->user()->can('upload-ophthalmology-images');

        return view('pages.ophthalmology.registrations.show', [
            'registration' => $ophthalmologyRegistration,
            'doctors' => $doctors,
            'canEdit' => $canEdit,
            'canChangeStatus' => $canChangeStatus,
            'canUpload' => $canUpload,
        ]);
    }

    public function update(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        abort_if($ophthalmologyRegistration->appointment?->is_completed, 403);

        $doctorIds = Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->where('branch_id', $ophthalmologyRegistration->branch_id)
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'examiner_id' => ['nullable', Rule::in($doctorIds)],
            'registration_date' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
            'chief_complaint' => ['nullable', 'string'],
            'medical_history' => ['nullable', 'array'],
            'visual_examination' => ['nullable', 'array'],
            'refraction' => ['nullable', 'array'],
            'slit_lamp_examination' => ['nullable', 'array'],
            'fundus_examination' => ['nullable', 'array'],
            'diagnosis' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'follow_up_date' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'fundus_image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,pdf', 'max:10240'],
        ]);

        $clinicalFields = [
            'examiner_id', 'registration_date', 'chief_complaint', 'medical_history',
            'visual_examination', 'refraction', 'slit_lamp_examination', 'fundus_examination',
            'diagnosis', 'treatment_plan', 'follow_up_date', 'notes',
        ];
        if (collect($clinicalFields)->contains(fn (string $field) => $request->has($field))) {
            abort_unless($request->user()->can('edit-ophthalmology-registrations'), 403);
        }
        if ($request->filled('status')) {
            abort_unless($request->user()->can('change-ophthalmology-status'), 403);
        }
        if ($request->hasFile('fundus_image')) {
            abort_unless($request->user()->can('upload-ophthalmology-images'), 403);
        }

        $data = collect($validated)->except('fundus_image')->all();

        if (! empty($validated['registration_date'])) {
            $data['registration_date'] = Verta::parse($validated['registration_date'])->datetime();
        }
        if (array_key_exists('follow_up_date', $validated)) {
            $data['follow_up_date'] = filled($validated['follow_up_date'] ?? null)
                ? Verta::parse($validated['follow_up_date'])->datetime()
                : null;
        }

        if ($request->hasFile('fundus_image')) {
            if ($ophthalmologyRegistration->fundus_image_path) {
                Storage::disk('public')->delete($ophthalmologyRegistration->fundus_image_path);
            }
            $data['fundus_image_path'] = $request->file('fundus_image')
                ->store('ophthalmology/fundus', 'public');
        }

        $ophthalmologyRegistration->update($data);

        return redirect()
            ->route('ophthalmology-registrations.show', $ophthalmologyRegistration)
            ->with('success', localize('global.ophthalmology_registration_updated_successfully'));
    }

    public function print(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): View
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        $ophthalmologyRegistration->load([
            'appointment.patient',
            'examiner:id,name',
        ]);

        return view('pages.ophthalmology.registrations.print', [
            'registration' => $ophthalmologyRegistration,
            'leftLogo' => asset('images/logos/لوگو قومنداني.JPG'),
            'rightLogo' => asset('images/logos/لوگوی جدید وزارت دفاع ملی.png'),
            'generatedAt' => verta()->format('Y/m/d H:i'),
        ]);
    }

    public function destroy(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        abort_unless($request->user()->can('delete-ophthalmology-registrations'), 403);

        $ophthalmologyRegistration->delete();

        return redirect()
            ->route('ophthalmology-registrations.index')
            ->with('success', localize('global.ophthalmology_registration_deleted_successfully'));
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->can('access-ophthalmology-registrations'), 403);
    }

    private function authorizeRegistrationAccess(Request $request, OphthalmologyRegistration $registration): void
    {
        $this->authorizeAccess($request);
        if ($request->user()->branch_id
            && (int) $request->user()->branch_id !== (int) $registration->branch_id) {
            abort(404);
        }
    }
}
