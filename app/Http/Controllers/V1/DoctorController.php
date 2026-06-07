<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    private const INDEX_FILTER_KEYS = [
        'search',
        'department_id',
        'branch_id',
        'gender',
        'clinic_type',
        'active_status',
        'join_date_from',
        'join_date_to',
        'per_page',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Doctor::class);

        $user = $request->user();
        $query = $this->buildIndexQuery($request);

        $allDoctors = (clone $query)->get();

        $perPage = (int) $request->input('per_page', 15);
        $perPage = in_array($perPage, [10, 15, 20, 50, 100], true) ? $perPage : 15;

        $paginator = $query->orderBy('name')->paginate($perPage)->withQueryString();

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Doctors/Index', [
            'doctors' => [
                'data' => collect($paginator->items())
                    ->map(fn (Doctor $doctor) => $this->transformDoctorForIndex($doctor))
                    ->values()
                    ->all(),
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
                'active' => $allDoctors->where('active_status', true)->count(),
                'inactive' => $allDoctors->where('active_status', false)->count(),
                'total' => $allDoctors->count(),
                'dentists' => $allDoctors->where('is_dentist', true)->count(),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            ],
            'permissions' => $this->doctorPermissions($user),
            'urls' => [
                'index' => route('react.doctors.index'),
                'create' => route('react.doctors.create'),
                'show' => url('/react/doctors'),
                'edit' => url('/react/doctors'),
                'destroy' => url('/react/doctors'),
                'updateStatus' => url('/react/doctors'),
            ],
        ]);
    }

    public function show(Request $request, Doctor $doctor): Response
    {
        $this->authorize('view', $doctor);

        $doctor->load([
            'department:id,name',
            'branch:id,name',
            'user.roles',
        ]);

        return Inertia::render('Doctors/Show', [
            'doctor' => $this->transformDoctorForShow($doctor),
            'permissions' => [
                'edit' => $request->user()->can('update', $doctor),
                'delete' => $request->user()->can('delete', $doctor),
            ],
            'urls' => [
                'index' => route('react.doctors.index'),
                'edit' => route('react.doctors.edit', $doctor),
                'destroy' => route('react.doctors.destroy', $doctor),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Doctor::class);

        return Inertia::render('Doctors/Create', [
            'mode' => 'create',
            'formData' => $this->buildFormData(),
            'urls' => $this->buildFormUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Doctor::class);

        $data = $this->validateDoctor($request);
        $data = $this->prepareDoctorData($request, $data);

        Doctor::create($data);

        return redirect()
            ->route('react.doctors.index')
            ->with('success', localize('global.doctor_created_successfully'));
    }

    public function edit(Request $request, Doctor $doctor): Response
    {
        $this->authorize('update', $doctor);

        return Inertia::render('Doctors/Edit', [
            'mode' => 'edit',
            'doctor' => $this->transformDoctorForForm($doctor),
            'formData' => $this->buildFormData(),
            'urls' => $this->buildFormUrls($doctor),
        ]);
    }

    public function update(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('update', $doctor);

        $data = $this->validateDoctor($request, true);
        $data = $this->prepareDoctorData($request, $data, $doctor);

        $doctor->update($data);

        return redirect()
            ->route('react.doctors.index')
            ->with('success', localize('global.doctor_updated_successfully'));
    }

    public function destroy(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('delete', $doctor);

        $doctor->delete();

        return redirect()
            ->route('react.doctors.index')
            ->with('success', localize('global.doctor_deleted_successfully'));
    }

    public function updateStatus(Request $request, Doctor $doctor): RedirectResponse
    {
        $this->authorize('toggleStatus', $doctor);

        $validated = $request->validate([
            'active_status' => 'required|boolean',
        ]);

        $doctor->update(['active_status' => $validated['active_status']]);

        return back()->with('success', localize('global.doctor_updated_successfully'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Doctor>
     */
    private function buildIndexQuery(Request $request)
    {
        $query = Doctor::query()->with(['department:id,name', 'branch:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhere('qualification', 'like', "%{$search}%")
                    ->orWhere('room_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('active_status') && in_array($request->active_status, ['0', '1'], true)) {
            $query->where('active_status', $request->active_status === '1');
        }

        if ($request->filled('clinic_type') && in_array($request->clinic_type, ['hospital', 'clinic'], true)) {
            $query->where('clinic_type', $request->clinic_type);
        }

        if ($request->filled('join_date_from')) {
            try {
                $query->whereDate('join_date', '>=', Verta::parse($request->join_date_from)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }

        if ($request->filled('join_date_to')) {
            try {
                $query->whereDate('join_date', '<=', Verta::parse($request->join_date_to)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDoctorForIndex(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'qualification' => $doctor->qualification,
            'contact_number' => $doctor->contact_number,
            'department_name' => $doctor->department?->name,
            'branch_name' => $doctor->branch?->name,
            'specialization' => $doctor->specialization,
            'gender' => $doctor->gender,
            'clinic_type' => $doctor->clinic_type,
            'active_status' => (bool) $doctor->active_status,
            'is_dentist' => (bool) $doctor->is_dentist,
            'is_nephrologist' => (bool) $doctor->is_nephrologist,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDoctorForShow(Doctor $doctor): array
    {
        $linkedUser = $doctor->user;

        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'father_name' => $doctor->father_name,
            'gender' => $doctor->gender,
            'contact_number' => $doctor->contact_number,
            'address' => $doctor->address,
            'specialization' => $doctor->specialization,
            'qualification' => $doctor->qualification,
            'room_no' => $doctor->room_no,
            'clinic_type' => $doctor->clinic_type,
            'join_date' => $doctor->join_date ? verta($doctor->join_date)->format('Y-m-d') : null,
            'active_status' => (bool) $doctor->active_status,
            'is_dentist' => (bool) $doctor->is_dentist,
            'is_nephrologist' => (bool) $doctor->is_nephrologist,
            'department_name' => $doctor->department?->name,
            'branch_name' => $doctor->branch?->name,
            'linked_user' => $linkedUser ? [
                'id' => $linkedUser->id,
                'name' => trim($linkedUser->name.' '.($linkedUser->last_name ?? '')),
                'email' => $linkedUser->email,
                'roles' => $linkedUser->roles->map(fn ($role) => [
                    'id' => $role->id,
                    'name' => $role->name_dr ?? $role->name,
                ])->values()->all(),
                'permissions' => $linkedUser->getAllPermissions()->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name_dr ?? $permission->name,
                ])->values()->all(),
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDoctorForForm(Doctor $doctor): array
    {
        return [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'father_name' => $doctor->father_name ?? '',
            'gender' => $doctor->gender ?? '',
            'contact_number' => $doctor->contact_number ?? '',
            'address' => $doctor->address ?? '',
            'specialization' => $doctor->specialization ?? '',
            'qualification' => $doctor->qualification ?? '',
            'room_no' => $doctor->room_no ?? '',
            'clinic_type' => $doctor->clinic_type ?? '',
            'join_date' => $doctor->join_date ? verta($doctor->join_date)->format('Y-m-d') : '',
            'department_id' => $doctor->department_id ? (string) $doctor->department_id : '',
            'user_id' => $doctor->user_id ? (string) $doctor->user_id : '',
            'active_status' => (bool) $doctor->active_status,
            'is_dentist' => (bool) $doctor->is_dentist,
            'is_nephrologist' => (bool) $doctor->is_nephrologist,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'doctorUsers' => User::query()
                ->where('is_doctor', true)
                ->orderBy('name')
                ->get(['id', 'name', 'last_name', 'email']),
            'clinicTypes' => [
                ['value' => 'hospital', 'label_key' => 'global.hospital'],
                ['value' => 'clinic', 'label_key' => 'global.clinic'],
            ],
            'genders' => [
                ['value' => 'Male', 'label_key' => 'global.male'],
                ['value' => 'Female', 'label_key' => 'global.female'],
                ['value' => 'Other', 'label_key' => 'global.other'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildFormUrls(?Doctor $doctor = null): array
    {
        return [
            'index' => route('react.doctors.index'),
            'store' => route('react.doctors.store'),
            'update' => $doctor ? route('react.doctors.update', $doctor) : '',
            'back' => route('react.doctors.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateDoctor(Request $request, bool $isUpdate = false): array
    {
        return $request->validate([
            'name' => 'required|string|max:191',
            'gender' => 'required|in:Male,Female,Other',
            'contact_number' => 'required|string|max:191',
            'father_name' => 'nullable|string|max:191',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string|max:191',
            'qualification' => 'nullable|string|max:191',
            'room_no' => 'nullable|string|max:191',
            'clinic_type' => 'nullable|in:hospital,clinic',
            'join_date' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'active_status' => 'nullable|boolean',
            'is_dentist' => 'nullable|boolean',
            'is_nephrologist' => 'nullable|boolean',
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareDoctorData(Request $request, array $data, ?Doctor $doctor = null): array
    {
        $data['branch_id'] = $request->user()->branch_id ?? $doctor?->branch_id;

        if (! $data['branch_id']) {
            abort(422, localize('global.branch_id_required'));
        }

        $data['active_status'] = $request->boolean('active_status');
        $data['is_dentist'] = $request->boolean('is_dentist');
        $data['is_nephrologist'] = $request->boolean('is_nephrologist');

        if ($request->filled('join_date')) {
            try {
                $data['join_date'] = Verta::parse($request->join_date)->datetime();
            } catch (\Throwable) {
                abort(422, localize('global.invalid_date_format'));
            }
        } else {
            $data['join_date'] = null;
        }

        return $data;
    }

    /**
     * @return array<string, bool>
     */
    private function doctorPermissions(User $user): array
    {
        return [
            'create' => $user->can('create', Doctor::class),
            'edit' => $user->hasRole(['super_admin', 'admin', 'hr'])
                || $user->hasPermissionTo('edit-doctors'),
            'delete' => $user->hasRole(['super_admin', 'admin', 'hr'])
                || $user->hasPermissionTo('delete-doctors'),
            'toggleStatus' => $user->hasRole(['super_admin', 'admin', 'hr'])
                || $user->hasPermissionTo('edit-doctors'),
        ];
    }
}
