<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Department;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Section;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

    public function __construct()
    {

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    if ($request->ajax()) {
        $users = User::with(['roles', 'category'])->get();

            if ($users) {
                return response()->json([
                    'data' => $users,
                ]);
            } else {
                return response()->json([
                    'message' => 'Internal Server Error',
                    'code' => 500,
                    'data' => [],
                ]);
            }
    }

    $query = User::with(['category']);
    
    // Add category filter
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }
    
    $users = $query->get();
    $categories = Category::all();
    return view('pages.users.index', compact('users', 'categories'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::all();
        $branches = Branch::all();
        $departments = Department::all();
        $sections = Section::all();
        $categories = Category::all();
        return view('pages.users.create',compact('roles','branches','departments','sections','categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateUserRequest $request)
    {

        $user = new User;
        $user->name = $request->name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->branch_id = $request->branch_id;
        $user->department_id = $request->department_id;
        $user->section_id = $request->section_id;
        $user->category_id = $request->category_id;
        $user->is_doctor = $request->has('is_doctor') ? true : false;
        $user->clinic_type = $request->clinic_type;
        $user->password = Hash::make($request->password);

        $user->save();

        $roles = $request['roles'];


        if (isset($roles)) {

            foreach ($roles as $role) {
                $role_r = Role::where('id', '=', $role)->firstOrFail();
                $user->assignRole($role_r);
            }
        }

        return redirect()->route('users.index')->with('success', localize('global.user_create_success'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {

        $roles = Role::all();
        $branches = Branch::all();
        $departments = Department::all();
        $sections = Section::all();
        $permissions = Permission::all();
        $categories = Category::all();

        return view('pages.users.edit',compact('user','roles','branches','departments','sections','permissions','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, $user_id)
    {
        $user = User::findOrFail($user_id);
    
        // Only hash and update the password if it's provided
        if (!empty($request->password)) {
            $request['password'] = Hash::make($request->password);
        } else {
            // Remove the password from the input if not provided
            $request->request->remove('password');
        }
    
        // Handle checkbox - if not present, set to false
        $request->merge(['is_doctor' => $request->has('is_doctor') ? true : false]);
    
        $user->update($request->input());
    
        $roles = $request['roles'];
    
        if (isset($roles)) {
            $user->roles()->sync($roles);
        } else {
            $user->roles()->detach();
        }
    
        $user->syncPermissions($request->input('permissions', []));
    
        return redirect()->route('users.index')
            ->with('success', localize('global.user_update_success'));
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

    }

    public function account(User $user)
    {
        $user = auth()->user();
        return view('pages.users.account', compact('user'));
    }

     public function viewProfile(User $user)
    {
        $user = auth()->user();
        return view('pages.users.profile', compact('user'));
    }

    public function changePassword(Request $request)
    {
        {
            $request->validate([
                'current_password' => 'required',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user();

            // Check if the current password matches the one in the database
            if (Hash::check($request->current_password, $user->password)) {
                // Update the user's password with the new hashed password
                $user->update([
                    'password' => Hash::make($request->new_password),
                ]);

                return redirect()->back()->with('success', localize('global.password_updated_successfully.'));
            }

            return redirect()->back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }
    }

    public function updateStatus(Request $request)
    {
        $userId = $request->input('user_id');
        $status = $request->input('status');

        // Find the user

        $user = User::findOrFail($userId);

        // Update the status
        $user->status = $status;

        $user->save();

        if($user->status == 1)
        {
            $request->session()->flash('success', localize('global.user_status_update_success'));
        }
        else {
            $request->session()->flash('success', localize('global.user_status_deactivated'));
        }

    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Get the currently authenticated user
        $user = Auth::user();

        // Delete the old avatar if it exists
        if ($user->avatar) {
            \Storage::disk('public')->delete($user->avatar);
        }

        // Store the new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        // Update the user's avatar column in the database
        $user->update(['avatar' => $avatarPath]);

        return redirect()->back()->with('success', localize('global.avatar_updated_successfully.'));
    }
}
