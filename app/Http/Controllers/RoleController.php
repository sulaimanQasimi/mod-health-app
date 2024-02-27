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
        if ($request->ajax()) {
            $roles = Role::with('permissions')->get();

            if ($roles) {
                return response()->json([
                    'data' => $roles,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        return view('pages.roles.index');
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
        $permissions = $this->getPermissionTree();
        $recipients = Recipient::all();
        $roleRecipients = $role->recipients;

        return view('pages.roles.edit', compact('role', 'permissions', 'recipients', 'roleRecipients'));
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
        return redirect()->route('roles.index')->with('success','Role deleted successfully');
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
}
