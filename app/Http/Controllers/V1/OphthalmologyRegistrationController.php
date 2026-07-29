<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\OphthalmologyRegistration;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class OphthalmologyRegistrationController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorizeAccess($request);

        $filters = $request->only(['search', 'status', 'examiner_id', 'date_from', 'date_to', 'per_page']);
        $query = OphthalmologyRegistration::query()
            ->with([
                'appointment.patient:id,name,last_name,id_card',
                'appointment:id,patient_id,date',
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
            ->when($request->filled('date_from'), fn (Builder $builder) => $builder->whereDate('registration_date', '>=', Verta::parse($request->date_from)->datetime()))
            ->when($request->filled('date_to'), fn (Builder $builder) => $builder->whereDate('registration_date', '<=', Verta::parse($request->date_to)->datetime()))
            ->latest();

        $requestedPerPage = (int) ($filters['per_page'] ?? 25);
        $perPage = in_array($requestedPerPage, [10, 25, 50], true) ? $requestedPerPage : 25;
        $paginator = $query->paginate($perPage)->withQueryString();

        $statsQuery = OphthalmologyRegistration::query()
            ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId));

        return Inertia::render('OphthalmologyRegistrations/Index', [
            'registrations' => [
                'data' => collect($paginator->items())->map(fn (OphthalmologyRegistration $item) => [
                    'id' => $item->id,
                    'ref_no' => $item->ref_no,
                    'patient_name' => trim(($item->appointment?->patient?->name ?? '').' '.($item->appointment?->patient?->last_name ?? '')),
                    'id_card' => $item->appointment?->patient?->id_card,
                    'examiner_name' => $item->examiner?->name,
                    'registration_date' => $item->registration_date ? verta($item->registration_date)->format('Y-m-d') : null,
                    'status' => $item->status,
                    'diagnosis' => $item->diagnosis,
                    'show_url' => route('react.ophthalmology-registrations.show', $item),
                    'delete_url' => route('react.ophthalmology-registrations.destroy', $item),
                ])->values()->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
                'in_progress' => (clone $statsQuery)->where('status', 'in_progress')->count(),
                'completed' => (clone $statsQuery)->where('status', 'completed')->count(),
            ],
            'filters' => array_merge([
                'search' => '',
                'status' => '',
                'examiner_id' => '',
                'date_from' => '',
                'date_to' => '',
                'per_page' => '25',
            ], $filters),
            'doctors' => Doctor::query()
                ->where('active_status', true)
                ->where('is_eye_doctor', true)
                ->when($request->user()->branch_id, fn (Builder $builder, $branchId) => $builder->where('branch_id', $branchId))
                ->orderBy('name')
                ->get(['id', 'name']),
            'permissions' => [
                'delete' => $request->user()->can('delete-ophthalmology-registrations'),
            ],
            'urls' => [
                'current' => route('react.ophthalmology-registrations.index'),
            ],
        ]);
    }

    public function show(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): Response
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        $this->authorize('view', $ophthalmologyRegistration->appointment);
        $ophthalmologyRegistration->load([
            'appointment.patient:id,name,last_name,father_name,id_card,age,gender,phone,job',
            'appointment:id,patient_id,doctor_id,branch_id,date,is_completed',
            'examiner:id,name',
        ]);

        return Inertia::render('OphthalmologyRegistrations/Show', [
            'registration' => $this->transform($ophthalmologyRegistration),
            'formOptions' => [
                'doctors' => Doctor::query()
                    ->where('active_status', true)
                    ->where('is_eye_doctor', true)
                    ->where('branch_id', $ophthalmologyRegistration->branch_id)
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
            'permissions' => [
                'edit' => ! $ophthalmologyRegistration->appointment?->is_completed
                    && $request->user()->can('edit-ophthalmology-registrations'),
                'changeStatus' => ! $ophthalmologyRegistration->appointment?->is_completed
                    && $request->user()->can('change-ophthalmology-status'),
                'uploadImages' => ! $ophthalmologyRegistration->appointment?->is_completed
                    && $request->user()->can('upload-ophthalmology-images'),
            ],
            'urls' => [
                'update' => route('react.ophthalmology-registrations.update', $ophthalmologyRegistration),
                'appointment' => route('react.appointments.show', $ophthalmologyRegistration->appointment_id),
                'print' => route('react.ophthalmology-registrations.print', $ophthalmologyRegistration),
            ],
        ]);
    }

    public function print(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): Response
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        $this->authorize('view', $ophthalmologyRegistration->appointment);
        $ophthalmologyRegistration->load([
            'appointment.patient:id,name,last_name,father_name,id_card,age,gender,phone,job',
            'appointment:id,patient_id,doctor_id,branch_id,date,is_completed',
            'examiner:id,name',
        ]);

        return Inertia::render('OphthalmologyRegistrations/Print', [
            'registration' => $this->transform($ophthalmologyRegistration),
            'assets' => [
                'leftLogo' => asset('images/logos/لوگو قومنداني.JPG'),
                'rightLogo' => asset('images/logos/لوگوی جدید وزارت دفاع ملی.png'),
            ],
            'generatedAt' => verta()->format('Y/m/d H:i'),
        ]);
    }

    public function update(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        $this->authorize('view', $ophthalmologyRegistration->appointment);
        abort_if($ophthalmologyRegistration->appointment?->is_completed, 403);

        $doctorIds = Doctor::query()
            ->where('active_status', true)
            ->where('is_eye_doctor', true)
            ->where('branch_id', $ophthalmologyRegistration->branch_id)
            ->pluck('id')
            ->all();

        $validated = $request->validate([
            'examiner_id' => ['nullable', Rule::in($doctorIds)],
            'registration_date' => ['sometimes', 'required', 'string'],
            'status' => ['sometimes', 'required', Rule::in(['pending', 'in_progress', 'completed', 'cancelled'])],
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
            'examiner_id',
            'registration_date',
            'chief_complaint',
            'medical_history',
            'visual_examination',
            'refraction',
            'slit_lamp_examination',
            'fundus_examination',
            'diagnosis',
            'treatment_plan',
            'follow_up_date',
            'notes',
        ];
        if (collect($clinicalFields)->contains(fn (string $field) => $request->has($field))) {
            abort_unless($request->user()->can('edit-ophthalmology-registrations'), 403);
        }
        if ($request->has('status')) {
            abort_unless($request->user()->can('change-ophthalmology-status'), 403);
        }
        if ($request->hasFile('fundus_image')) {
            abort_unless($request->user()->can('upload-ophthalmology-images'), 403);
        }

        $data = collect($validated)->except('fundus_image')->all();
        if (array_key_exists('registration_date', $validated)) {
            $data['registration_date'] = Verta::parse($validated['registration_date'])->datetime();
        }
        if (array_key_exists('follow_up_date', $validated)) {
            $data['follow_up_date'] = filled($validated['follow_up_date'])
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
            ->route('react.ophthalmology-registrations.show', $ophthalmologyRegistration)
            ->with('success', localize('global.ophthalmology_registration_updated_successfully'));
    }

    public function destroy(Request $request, OphthalmologyRegistration $ophthalmologyRegistration): RedirectResponse
    {
        $this->authorizeRegistrationAccess($request, $ophthalmologyRegistration);
        abort_unless($request->user()->can('delete-ophthalmology-registrations'), 403);

        $ophthalmologyRegistration->delete();

        return redirect()
            ->route('react.ophthalmology-registrations.index')
            ->with('success', localize('global.ophthalmology_registration_deleted_successfully'));
    }

    private function transform(OphthalmologyRegistration $registration): array
    {
        $patient = $registration->appointment?->patient;

        return [
            'id' => $registration->id,
            'appointment_id' => $registration->appointment_id,
            'ref_no' => $registration->ref_no,
            'examiner_id' => $registration->examiner_id,
            'examiner_name' => $registration->examiner?->name,
            'registration_date' => $registration->registration_date
                ? verta($registration->registration_date)->format('Y-m-d')
                : '',
            'status' => $registration->status,
            'chief_complaint' => $registration->chief_complaint ?? '',
            'medical_history' => $registration->medical_history ?? [],
            'visual_examination' => $registration->visual_examination ?? [],
            'refraction' => $registration->refraction ?? [],
            'slit_lamp_examination' => $registration->slit_lamp_examination ?? [],
            'fundus_examination' => $registration->fundus_examination ?? [],
            'diagnosis' => $registration->diagnosis ?? '',
            'treatment_plan' => $registration->treatment_plan ?? '',
            'follow_up_date' => $registration->follow_up_date
                ? verta($registration->follow_up_date)->format('Y-m-d')
                : '',
            'notes' => $registration->notes ?? '',
            'fundus_image_url' => $registration->fundus_image_path
                ? Storage::disk('public')->url($registration->fundus_image_path)
                : null,
            'patient' => [
                'id' => $patient?->id,
                'name' => trim(($patient?->name ?? '').' '.($patient?->last_name ?? '')),
                'father_name' => $patient?->father_name,
                'id_card' => $patient?->id_card,
                'age' => $patient?->age,
                'gender' => $patient?->gender,
                'phone' => $patient?->phone,
                'occupation' => $patient?->job,
            ],
        ];
    }

    private function authorizeAccess(Request $request): void
    {
        abort_unless($request->user()->can('access-ophthalmology-registrations'), 403);
    }

    private function authorizeRegistrationAccess(
        Request $request,
        OphthalmologyRegistration $registration,
    ): void {
        $this->authorizeAccess($request);
        if ($request->user()->branch_id
            && (int) $request->user()->branch_id !== (int) $registration->branch_id) {
            abort(404);
        }
    }
}
