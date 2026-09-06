<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Requests\CreatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    use PaginatesInertiaIndex;

    private const FILTER_KEYS = ['search', 'per_page'];

    public function index(Request $request): Response
    {
        $this->ensureCanView($request);

        $query = Permission::query()->with('parent:id,name,name_dr');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('name_dr', 'like', "%{$search}%");
            });
        }

        $paginator = $this->paginateQuery($query->orderBy('name_dr')->orderBy('name'), $request);

        return Inertia::render('Permissions/Index', [
            'permissionsList' => $this->paginationPayload($paginator, fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'name_dr' => $permission->name_dr,
                'parent_name' => $permission->parent?->name_dr ?: $permission->parent?->name,
            ]),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'permissions' => [
                ...$this->settingsPermissions(
                    $request->user(),
                    'create-permissions',
                    'edit-permissions',
                ),
                'delete' => false,
            ],
            'urls' => [
                'index' => route('permissions.index'),
                'create' => route('permissions.create'),
                'edit' => url('/permissions'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->ensureCanCreate($request);

        return Inertia::render('Permissions/Create', [
            'formData' => $this->buildFormData(),
            'urls' => $this->formUrls(),
        ]);
    }

    public function store(CreatePermissionRequest $request): RedirectResponse
    {
        $this->ensureCanCreate($request);

        Permission::create([
            'name' => (string) $request->input('name'),
            'name_dr' => (string) $request->input('name_dr'),
            'parent_id' => $request->filled('parent_id') ? (int) $request->input('parent_id') : null,
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', localize('global.permission_create_success'));
    }

    public function edit(Request $request, Permission $permission): Response
    {
        $this->ensureCanEdit($request);

        return Inertia::render('Permissions/Edit', [
            'permission' => [
                'id' => $permission->id,
                'name' => $permission->name,
                'name_dr' => $permission->name_dr,
                'parent_id' => $permission->parent_id,
            ],
            'formData' => $this->buildFormData($permission),
            'urls' => $this->formUrls($permission),
        ]);
    }

    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $this->ensureCanEdit($request);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'name_dr' => 'required|string|max:191',
            'parent_id' => 'nullable|integer|exists:permissions,id',
        ], [
            'name_dr.required' => localize('global.permission_name_dr_required'),
            'name.required' => localize('global.permission_name_en_required'),
        ]);

        $permission->update([
            'name' => $data['name'],
            'name_dr' => $data['name_dr'],
            'parent_id' => isset($data['parent_id']) && (int) $data['parent_id'] !== $permission->id
                ? (int) $data['parent_id']
                : null,
        ]);

        return redirect()
            ->route('permissions.index')
            ->with('success', localize('global.permission_update_success'));
    }

    /**
     * @return array{parentOptions: list<array{id: int, name: string, name_dr: string|null}>}
     */
    private function buildFormData(?Permission $currentPermission = null): array
    {
        $parentOptions = Permission::query()
            ->when($currentPermission, fn ($query) => $query->whereKeyNot($currentPermission->id))
            ->orderBy('name_dr')
            ->orderBy('name')
            ->get(['id', 'name', 'name_dr'])
            ->map(fn (Permission $permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'name_dr' => $permission->name_dr,
            ])
            ->values()
            ->all();

        return [
            'parentOptions' => $parentOptions,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function formUrls(?Permission $permission = null): array
    {
        return [
            'index' => route('permissions.index'),
            'store' => route('permissions.store'),
            'update' => $permission ? route('permissions.update', $permission) : '',
            'back' => route('permissions.index'),
        ];
    }

    private function ensureCanView(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole(['super_admin', 'admin'])
                || $request->user()?->hasPermissionTo('show-permissions-menu'),
            403,
        );
    }

    private function ensureCanCreate(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole(['super_admin', 'admin'])
                || $request->user()?->hasPermissionTo('create-permissions'),
            403,
        );
    }

    private function ensureCanEdit(Request $request): void
    {
        abort_unless(
            $request->user()?->hasRole(['super_admin', 'admin'])
                || $request->user()?->hasPermissionTo('edit-permissions'),
            403,
        );
    }
}
