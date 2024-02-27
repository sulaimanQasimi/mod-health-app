<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\CreatePermissionRequest;

class PermissionController extends Controller
{

    /**
     * Instantiate a new PermissionController instance.
     */
    public function __construct()
    {
        // $this->middleware(['permission:create-permissions|edit-permissions']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $permissions = Permission::all();

            if ($permissions) {
                return response()->json([
                    'data' => $permissions,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        return view('pages.permissions.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permissions = Permission::all();
        return view('pages.permissions.create',compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreatePermissionRequest $request)
    {

        $permission = Permission::create($request->input());

        return redirect()->route('permissions.index')
                        ->with('success', localize('global.permission_create_success'));
    }

    /**
     * Display the specified resource.
     *
     * @param Spatie\Permission\Models\Permission $permission
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Permission $permission)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Permission $Permission
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Permission $permission)
    {
        $permissions = Permission::all();
        return view('pages.permissions.edit', compact('permission','permissions'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Models\Permission $permission
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Permission $permission)
    {
        $permission->update($request->input());
        return redirect()->route('permissions.index')
                        ->with('success', localize('global.permission_update_success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Permission $permission)
    {
        //
    }

}
