<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Models\Recipient;
use App\Models\Role;
use App\Models\Permission;
use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        // $this->middleware(['permission:create-roles|edit-roles']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $roles = Role::with('permissions')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'data' => $roles->items(),
                'current_page' => $roles->currentPage(),
                'last_page' => $roles->lastPage(),
                'per_page' => $roles->perPage(),
                'total' => $roles->total(),
            ]);
        }

        return view('pages.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $permissions = Permission::all();
        $recipients = Recipient::all();

        return view('pages.roles.create',compact('permissions', 'recipients'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateRoleRequest $request)
    {
        $role_name = $request['name'];
        $role = new Role();
        $role->name = $role_name;
        $role->name_dr = $request->name_dr;
        if ($request->has('recipients')) {
            $role->recipients = $request->recipients;
        }
        $role->sector_id = $request->sector ? $request->sector : currentUser()->sector_id;
        $role->save();

        $role->syncPermissions($request->input('permission'));

        return redirect()->route('roles.index')->with('success', localize('global.role_create_success'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();

        return view('pages.roles.show',compact('role','rolePermissions'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Role $role)
    {
        $permissionFolders = $this->getPermissionFolders();
        $recipients = Recipient::all();
        $roleRecipients = $role->recipients;
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('pages.roles.edit', compact('role', 'permissionFolders', 'recipients', 'roleRecipients', 'rolePermissionIds'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $role->name = $request->input('name');
        $role->name_dr = $request->input('name_dr');
        $role->recipients = $request->has('recipients') ? $request->recipients : null;
        $role->save();
        $role->syncPermissions($request->input('permission'));
        return redirect()->route('roles.index')->with('success', localize('global.role_update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::where('id', $id);
        $role->delete();
        return redirect()->route('roles.index')->with('success',localize('global.role_delete_success'));
    }

    public function getPermissionTree($parentId = null)
    {
        $permissions = Permission::where('parent_id', $parentId)->get();
        $tree = [];
        foreach ($permissions as $permission) {
            $subPermissions = $this->getPermissionTree($permission->id);

            if ($subPermissions->isNotEmpty()) {
                $permission->sub_permissions = $subPermissions;
            }
            $tree[] = $permission;
        }
        return collect($tree);
    }

    /**
     * Group permissions into folders using parent_id when available,
     * otherwise by related module name (e.g. users, depot, roles).
     */
    public function getPermissionFolders()
    {
        $all = Permission::orderBy('id')->get();
        $byParent = $all->groupBy(fn ($permission) => $permission->parent_id);
        $folders = collect();
        $usedIds = collect();

        foreach ($byParent->get(null, collect()) as $parent) {
            $children = $byParent->get($parent->id, collect());

            if ($children->isEmpty()) {
                continue;
            }

            $items = collect([$parent])->merge($children)->values();
            $groupKey = $this->inferPermissionGroupKey($parent->name);
            $folders->push([
                'key' => 'parent-' . $parent->id,
                'title' => $this->inferPermissionGroupTitle($groupKey, $items, $parent),
                'permissions' => $items,
            ]);
            $usedIds = $usedIds->merge($items->pluck('id'));
        }

        $remaining = $all->whereNotIn('id', $usedIds->unique()->all())->values();

        foreach ($remaining->groupBy(fn ($permission) => $this->inferPermissionGroupKey($permission->name)) as $key => $permissions) {
            $folders->push([
                'key' => 'group-' . $key,
                'title' => $this->inferPermissionGroupTitle($key, $permissions),
                'permissions' => $permissions->values(),
            ]);
        }

        return $folders->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    protected function inferPermissionGroupKey(string $name): string
    {
        if (str_contains($name, '.')) {
            return explode('.', $name)[0];
        }

        $key = preg_replace('/^(show|create|edit|delete|manage|deactivate|issue|receive|add|update|view)-/', '', $name);
        $key = preg_replace('/-menu$/', '', $key);

        return $key ?: $name;
    }

    protected function inferPermissionGroupTitle(string $key, $permissions, $parent = null): string
    {
        // 1) Dynamically resolve from lang/{locale}/global.php
        $fromLang = localize_from_key($key);
        if ($fromLang !== null) {
            return $fromLang;
        }

        if ($parent) {
            $fromParentName = localize_from_key($parent->name);
            if ($fromParentName !== null) {
                return $fromParentName;
            }
        }

        // 2) Prefer menu-style permission label from DB
        $menuPermission = $permissions->first(function ($permission) use ($key) {
            return in_array($permission->name, [
                "show-{$key}-menu",
                "{$key}.view",
                "show-{$key}",
            ], true);
        });

        if ($menuPermission) {
            return permission_label($menuPermission);
        }

        if ($parent) {
            return permission_label($parent);
        }

        // 3) Humanized key as last resort
        return ucwords(str_replace(['-', '_'], ' ', $key));
    }
}
