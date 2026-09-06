<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    private const INDEX_FILTER_KEYS = [
        'search',
        'category_id',
        'department_id',
        'status',
        'role_id',
        'is_doctor',
        'clinic_type',
        'per_page',
    ];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $user = $request->user();
        $query = $this->buildIndexQuery($request);

        $statsBase = clone $query;
        $allUsers = $statsBase->get();
        $currentMonth = now()->format('Y-m');

        $perPage = (int) $request->input('per_page', 20);
        $perPage = in_array($perPage, [10, 20, 50, 100], true) ? $perPage : 20;

        $paginator = $query->paginate($perPage)->withQueryString();

        $filters = [];
        foreach (self::INDEX_FILTER_KEYS as $key) {
            $filters[$key] = (string) $request->input($key, '');
        }

        return Inertia::render('Users/Index', [
            'users' => [
                'data' => collect($paginator->items())
                    ->map(fn (User $item) => $this->transformUserForIndex($item))
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
                'active' => $allUsers->where('status', 1)->count(),
                'inactive' => $allUsers->where('status', 0)->count(),
                'total' => $allUsers->count(),
                'new_this_month' => $allUsers->filter(
                    fn (User $item) => $item->created_at?->format('Y-m') === $currentMonth,
                )->count(),
            ],
            'filters' => $filters,
            'filterOptions' => [
                'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
                'departments' => Department::query()->orderBy('name')->get(['id', 'name']),
                'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'name_dr']),
            ],
            'permissions' => $this->userPermissions($user),
            'currentUserId' => $user->id,
            'urls' => [
                'index' => route('users.index'),
                'create' => route('users.create'),
                'edit' => url('/users'),
                'updateStatus' => url('/users'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Create', [
            'mode' => 'create',
            'formData' => $this->buildFormData(),
            'urls' => $this->buildFormUrls(),
        ]);
    }

    public function store(CreateUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = new User;
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->branch_id = $request->branch_id;
        $user->department_id = $request->department_id;
        $user->section_id = $request->section_id;
        $user->category_id = $request->category_id;
        $user->is_doctor = $request->boolean('is_doctor');
        $user->clinic_type = $request->clinic_type;
        $user->status = 1;
        $user->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $user->avatar = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        $this->syncRolesAndPermissions($user, $request->input('roles', []), $request->input('permissions', []));

        return redirect()
            ->route('users.index')
            ->with('success', localize('global.user_create_success'));
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['roles:id,name,name_dr', 'permissions:id,name,name_dr']);

        return Inertia::render('Users/Edit', [
            'mode' => 'edit',
            'user' => $this->transformUserForForm($user),
            'formData' => $this->buildFormData(),
            'urls' => $this->buildFormUrls($user),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->only([
            'name',
            'last_name',
            'email',
            'branch_id',
            'department_id',
            'section_id',
            'category_id',
            'clinic_type',
        ]);

        $data['is_doctor'] = $request->boolean('is_doctor');

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        $this->syncRolesAndPermissions($user, $request->input('roles', []), $request->input('permissions', []));

        return redirect()
            ->route('users.index')
            ->with('success', localize('global.user_update_success'));
    }

    public function updateStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorize('toggleStatus', $user);

        $validated = $request->validate([
            'status' => 'required|boolean',
        ]);

        $user->update(['status' => $validated['status'] ? 1 : 0]);

        $message = $user->status == 1
            ? localize('global.user_status_update_success')
            : localize('global.user_status_deactivated');

        return back()->with('success', $message);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function buildIndexQuery(Request $request)
    {
        $query = User::query()->with(['roles:id,name,name_dr', 'category:id,name', 'department:id,name']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status') && in_array($request->status, ['0', '1'], true)) {
            $query->where('status', (int) $request->status);
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $request->role_id));
        }

        if ($request->filled('is_doctor') && in_array($request->is_doctor, ['0', '1'], true)) {
            $query->where('is_doctor', (int) $request->is_doctor);
        }

        if ($request->filled('clinic_type') && in_array($request->clinic_type, ['hospital', 'clinic', 'both'], true)) {
            $query->where('clinic_type', $request->clinic_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->latest();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformUserForIndex(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => trim($user->name.' '.$user->last_name),
            'email' => $user->email,
            'avatar_url' => $user->avatar
                ? asset('storage/'.$user->avatar)
                : asset('assets/img/avatars/1.png'),
            'category_name' => $user->category?->name,
            'department_name' => $user->department?->name,
            'is_doctor' => (bool) $user->is_doctor,
            'clinic_type' => $user->clinic_type,
            'status' => (int) $user->status,
            'roles' => $user->roles->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name_dr ?? $role->name,
            ])->values()->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformUserForForm(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'branch_id' => $user->branch_id ? (string) $user->branch_id : '',
            'department_id' => $user->department_id ? (string) $user->department_id : '',
            'section_id' => $user->section_id ? (string) $user->section_id : '',
            'category_id' => $user->category_id ? (string) $user->category_id : '',
            'is_doctor' => (bool) $user->is_doctor,
            'clinic_type' => $user->clinic_type ?? '',
            'avatar_url' => $user->avatar
                ? asset('storage/'.$user->avatar)
                : asset('assets/img/avatars/1.png'),
            'roles' => $user->roles->pluck('id')->map(fn ($id) => (string) $id)->all(),
            'permissions' => $user->permissions->pluck('id')->map(fn ($id) => (string) $id)->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'branches' => Branch::query()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::query()->orderBy('name')->get(['id', 'name', 'branch_id']),
            'sections' => Section::query()->orderBy('name')->get(['id', 'name', 'department_id']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'roles' => Role::query()->orderBy('name')->get(['id', 'name', 'name_dr']),
            'permissions' => Permission::query()->orderBy('name')->get(['id', 'name', 'name_dr']),
            'clinicTypes' => [
                ['value' => 'hospital', 'label_key' => 'global.hospital'],
                ['value' => 'clinic', 'label_key' => 'global.clinic'],
                ['value' => 'both', 'label_key' => 'global.both'],
            ],
            'defaultAvatar' => asset('assets/img/avatars/1.png'),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function buildFormUrls(?User $user = null): array
    {
        return [
            'index' => route('users.index'),
            'store' => route('users.store'),
            'update' => $user ? route('users.update', $user) : '',
            'back' => route('users.index'),
        ];
    }

    /**
     * @param  array<int|string>  $roleIds
     * @param  array<int|string>  $permissionIds
     */
    private function syncRolesAndPermissions(User $user, array $roleIds, array $permissionIds): void
    {
        $roleIds = collect($roleIds)->filter()->map(fn ($id) => (int) $id)->all();
        $permissionIds = collect($permissionIds)->filter()->map(fn ($id) => (int) $id)->all();

        if (! empty($roleIds)) {
            $user->roles()->sync($roleIds);
        } else {
            $user->roles()->detach();
        }

        $user->syncPermissions($permissionIds);
    }

    /**
     * @return array<string, bool>
     */
    private function userPermissions(User $user): array
    {
        return [
            'create' => $user->can('create', User::class),
            'edit' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('edit-users'),
            'toggleStatus' => $user->hasRole(['super_admin', 'admin'])
                || $user->hasPermissionTo('deactivate-users'),
        ];
    }
}
