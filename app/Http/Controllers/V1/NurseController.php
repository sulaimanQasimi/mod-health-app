<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class NurseController extends Controller
{
    use PaginatesInertiaIndex;

    private const INDEX_FILTER_KEYS = [
        'search',
        'department_id',
        'branch_id',
        'shift',
        'employment_status',
        'per_page',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Nurse::class);

        $query = $this->buildIndexQuery($request);
        $paginator = $this->paginateQuery($query->orderByDesc('created_at'), $request);

        return Inertia::render('Nurses/Index', [
            'nurses' => $this->paginationPayload($paginator, fn (Nurse $nurse) => $this->transformNurseForIndex($nurse)),
            'filters' => $this->collectFilters($request, self::INDEX_FILTER_KEYS),
            'filterOptions' => [
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
                'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
                'shifts' => [
                    ['value' => 'morning', 'label_key' => 'global.morning'],
                    ['value' => 'evening', 'label_key' => 'global.evening'],
                    ['value' => 'night', 'label_key' => 'global.night'],
                ],
                'employmentStatuses' => [
                    ['value' => 'active', 'label_key' => 'global.active'],
                    ['value' => 'inactive', 'label_key' => 'global.inactive'],
                    ['value' => 'on_leave', 'label_key' => 'global.on_leave'],
                ],
            ],
            'permissions' => $this->nursePermissions($request->user()),
            'urls' => [
                'index' => route('react.nurses.index'),
                'create' => route('react.nurses.create'),
                'show' => url('/react/nurses'),
                'edit' => url('/react/nurses'),
                'destroy' => url('/react/nurses'),
            ],
        ]);
    }

    public function show(Request $request, Nurse $nurse): Response
    {
        $this->authorize('view', $nurse);

        $nurse->load([
            'user:id,name,last_name,email',
            'department:id,name',
            'branch:id,name',
            'createdBy:id,name,last_name',
            'updatedBy:id,name,last_name',
        ]);

        return Inertia::render('Nurses/Show', [
            'nurse' => $this->transformNurseForShow($nurse),
            'permissions' => [
                'edit' => $request->user()->can('update', $nurse),
                'delete' => $request->user()->can('delete', $nurse),
            ],
            'urls' => [
                'index' => route('react.nurses.index'),
                'edit' => route('react.nurses.edit', $nurse),
                'destroy' => route('react.nurses.destroy', $nurse),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Nurse::class);

        return Inertia::render('Nurses/Create', [
            'mode' => 'create',
            'formData' => $this->buildFormData(),
            'urls' => $this->buildFormUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Nurse::class);

        Nurse::create($this->validateNurse($request));

        return redirect()
            ->route('react.nurses.index')
            ->with('success', localize('global.nurse_created_successfully'));
    }

    public function edit(Request $request, Nurse $nurse): Response
    {
        $this->authorize('update', $nurse);

        return Inertia::render('Nurses/Edit', [
            'mode' => 'edit',
            'nurse' => $this->transformNurseForForm($nurse),
            'formData' => $this->buildFormData($nurse),
            'urls' => $this->buildFormUrls($nurse),
        ]);
    }

    public function update(Request $request, Nurse $nurse): RedirectResponse
    {
        $this->authorize('update', $nurse);

        $nurse->update($this->validateNurse($request, $nurse));

        return redirect()
            ->route('react.nurses.index')
            ->with('success', localize('global.nurse_updated_successfully'));
    }

    public function destroy(Request $request, Nurse $nurse): RedirectResponse
    {
        $this->authorize('delete', $nurse);

        $nurse->delete();

        return redirect()
            ->route('react.nurses.index')
            ->with('success', localize('global.nurse_deleted_successfully'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Nurse>
     */
    private function buildIndexQuery(Request $request)
    {
        $query = Nurse::query()->with(['department:id,name', 'branch:id,name']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        return $query;
    }

    /**
     * @return array<string, mixed>
     */
    private function transformNurseForIndex(Nurse $nurse): array
    {
        return [
            'id' => $nurse->id,
            'first_name' => $nurse->first_name,
            'last_name' => $nurse->last_name,
            'full_name' => $nurse->full_name,
            'employee_id' => $nurse->employee_id,
            'gender' => $nurse->gender,
            'specialization' => $nurse->specialization,
            'shift' => $nurse->shift,
            'employment_status' => $nurse->employment_status,
            'department_name' => $nurse->department?->name,
            'branch_name' => $nurse->branch?->name,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformNurseForShow(Nurse $nurse): array
    {
        return [
            'id' => $nurse->id,
            'first_name' => $nurse->first_name,
            'last_name' => $nurse->last_name,
            'full_name' => $nurse->full_name,
            'gender' => $nurse->gender,
            'date_of_birth' => $nurse->date_of_birth?->format('Y-m-d'),
            'phone' => $nurse->phone,
            'email' => $nurse->email,
            'address' => $nurse->address,
            'employee_id' => $nurse->employee_id,
            'specialization' => $nurse->specialization,
            'shift' => $nurse->shift,
            'employment_status' => $nurse->employment_status,
            'date_of_joining' => $nurse->date_of_joining?->format('Y-m-d'),
            'department_name' => $nurse->department?->name,
            'branch_name' => $nurse->branch?->name,
            'linked_user' => $nurse->user ? [
                'id' => $nurse->user->id,
                'name' => trim($nurse->user->name.' '.($nurse->user->last_name ?? '')),
                'email' => $nurse->user->email,
            ] : null,
            'created_by_name' => $nurse->createdBy
                ? trim($nurse->createdBy->name.' '.($nurse->createdBy->last_name ?? ''))
                : null,
            'updated_by_name' => $nurse->updatedBy
                ? trim($nurse->updatedBy->name.' '.($nurse->updatedBy->last_name ?? ''))
                : null,
            'created_at' => $nurse->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $nurse->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformNurseForForm(Nurse $nurse): array
    {
        return [
            'id' => $nurse->id,
            'first_name' => $nurse->first_name,
            'last_name' => $nurse->last_name,
            'gender' => $nurse->gender ?? '',
            'date_of_birth' => $nurse->date_of_birth?->format('Y-m-d') ?? '',
            'phone' => $nurse->phone ?? '',
            'email' => $nurse->email ?? '',
            'address' => $nurse->address ?? '',
            'employee_id' => $nurse->employee_id,
            'department_id' => $nurse->department_id ? (string) $nurse->department_id : '',
            'branch_id' => $nurse->branch_id ? (string) $nurse->branch_id : '',
            'specialization' => $nurse->specialization ?? '',
            'shift' => $nurse->shift ?? '',
            'employment_status' => $nurse->employment_status ?? '',
            'date_of_joining' => $nurse->date_of_joining?->format('Y-m-d') ?? '',
            'user_id' => $nurse->user_id ? (string) $nurse->user_id : '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(?Nurse $nurse = null): array
    {
        return [
            'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'users' => User::query()
                ->where(function ($query) use ($nurse) {
                    $query->whereDoesntHave('nurse');
                    if ($nurse?->user_id) {
                        $query->orWhere('id', $nurse->user_id);
                    }
                })
                ->orderBy('name')
                ->get(['id', 'name', 'last_name', 'email']),
            'genders' => [
                ['value' => 'male', 'label_key' => 'global.male'],
                ['value' => 'female', 'label_key' => 'global.female'],
            ],
            'shifts' => [
                ['value' => 'morning', 'label_key' => 'global.morning'],
                ['value' => 'evening', 'label_key' => 'global.evening'],
                ['value' => 'night', 'label_key' => 'global.night'],
            ],
            'employmentStatuses' => [
                ['value' => 'active', 'label_key' => 'global.active'],
                ['value' => 'inactive', 'label_key' => 'global.inactive'],
                ['value' => 'on_leave', 'label_key' => 'global.on_leave'],
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildFormUrls(?Nurse $nurse = null): array
    {
        return [
            'index' => route('react.nurses.index'),
            'store' => route('react.nurses.store'),
            'update' => $nurse ? route('react.nurses.update', $nurse) : '',
            'back' => route('react.nurses.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateNurse(Request $request, ?Nurse $nurse = null): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'employee_id' => [
                'required',
                'string',
                'max:255',
                Rule::unique('nurses', 'employee_id')->ignore($nurse?->id),
            ],
            'department_id' => 'nullable|exists:departments,id',
            'branch_id' => 'nullable|exists:branches,id',
            'user_id' => 'nullable|exists:users,id',
            'specialization' => 'nullable|string|max:255',
            'shift' => 'required|in:morning,evening,night',
            'employment_status' => 'required|in:active,inactive,on_leave',
            'date_of_joining' => 'required|date',
        ]);
    }

    /**
     * @return array<string, bool>
     */
    private function nursePermissions(User $user): array
    {
        return [
            'create' => $user->can('create', Nurse::class),
            'edit' => $user->can('update', Nurse::class),
            'delete' => $user->can('delete', Nurse::class),
        ];
    }
}
