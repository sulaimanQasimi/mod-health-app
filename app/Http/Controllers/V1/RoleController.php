<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Role::class);

        $query = Role::query()->userBasedRole()->with('permissions:id,name,name_dr');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('name_dr', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderBy('name'), $request);

        return Inertia::render('Roles/Index', [
            'roles' => $this->paginationPayload($paginator, fn (Role $role) => [
                'id' => $role->id,
                'name' => $role->name,
                'name_dr' => $role->name_dr,
                'permissions_count' => $role->permissions->count(),
                'permissions' => $role->permissions->take(6)->map(fn ($permission) => [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'name_dr' => $permission->name_dr,
                ])->values()->all(),
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => $this->settingsPermissions(
                $request->user(),
                'create-roles',
                'edit-roles',
            ),
            'urls' => [
                'index' => route('react.roles.index'),
                'create' => route('react.roles.create'),
                'edit' => url('/react/roles'),
                'destroy' => url('/react/roles'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Role::class);

        return Inertia::render('Roles/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $data = $this->validateRole($request);

        $role = Role::create([
            'name' => $data['name'],
            'name_dr' => $data['name_dr'],
            'guard_name' => 'web',
            'sector_id' => $request->user()?->sector_id,
        ]);

        $role->syncPermissionIds($data['permission']);

        return redirect()
            ->route('react.roles.index')
            ->with('success', localize('global.role_create_success'));
    }

    public function edit(Request $request, Role $role): Response
    {
        $this->authorize('update', $role);

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'name_dr' => $role->name_dr,
                'permission_ids' => $role->permissions()->pluck('id')->map(fn ($id) => (int) $id)->values()->all(),
            ],
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls($role),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $data = $this->validateRole($request, $role);

        $role->name = $data['name'];
        $role->name_dr = $data['name_dr'];
        $role->save();
        $role->syncPermissionIds($data['permission']);

        return redirect()
            ->route('react.roles.index')
            ->with('success', localize('global.role_update_success'));
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $role->delete();

        return redirect()
            ->route('react.roles.index')
            ->with('success', localize('global.role_delete_success'));
    }

    /**
     * @return array<string, mixed>
     */
    private function buildFormData(): array
    {
        return [
            'permissionTree' => $this->permissionTree(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Role $role = null): array
    {
        return [
            'index' => route('react.roles.index'),
            'store' => route('react.roles.store'),
            'update' => $role ? route('react.roles.update', $role) : '',
            'back' => route('react.roles.index'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRole(Request $request, ?Role $role = null): array
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
                Rule::unique('roles', 'name')->ignore($role?->id),
            ],
            'name_dr' => 'required|string|max:191',
            'permission' => 'nullable|array',
            'permission.*' => 'integer|exists:permissions,id',
        ], [
            'name.required' => localize('global.role_name_en_required'),
            'name_dr.required' => localize('global.role_name_dr_required'),
        ]);

        $validated['permission'] = $validated['permission'] ?? [];

        return $validated;
    }

    /**
     * @return list<array{id: int, name: string, name_dr: string|null, children: array<int, mixed>}>
     */
    private function permissionTree(): array
    {
        $permissions = Permission::query()
            ->orderBy('name_dr')
            ->orderBy('name')
            ->get(['id', 'name', 'name_dr', 'parent_id']);

        return $this->buildPermissionTree($permissions);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Permission>  $permissions
     * @return list<array{id: int, name: string, name_dr: string|null, children: array<int, mixed>}>
     */
    private function buildPermissionTree($permissions, ?int $parentId = null): array
    {
        return $permissions
            ->where('parent_id', $parentId)
            ->values()
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'name_dr' => $permission->name_dr,
                'children' => $this->buildPermissionTree($permissions, $permission->id),
            ])
            ->all();
    }
}
