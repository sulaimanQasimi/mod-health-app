<?php

namespace App\Http\Controllers;

use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:pharmacy.index')->only('index');
        $this->middleware('permission:pharmacy.create')->only(['create', 'store']);
        $this->middleware('permission:pharmacy.edit')->only(['edit', 'update']);
        $this->middleware('permission:pharmacy.delete')->only('destroy');
        $this->middleware('permission:pharmacy.show')->only('show');
        $this->middleware('permission:pharmacy.manage_users')->only(['manageUsers', 'addUser', 'removeUser', 'updateUserRole']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pharmacies = Pharmacy::with(['activeUsers', 'outcomes'])->get();

            if ($pharmacies) {
                return response()->json([
                    'data' => $pharmacies,
                ]);
            } else {
                return response()->json([
                    'message' => localize('global.internal_server_error'),
                    'code' => 500,
                    'data' => [],
                ]);
            }
        }

        $pharmacies = Pharmacy::with(['activeUsers', 'outcomes'])->get();
        return view('pages.pharmacies.index', compact('pharmacies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('pages.pharmacies.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'address' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:manager,staff,viewer',
        ]);

        DB::beginTransaction();
        try {
            $pharmacy = new Pharmacy();
            $pharmacy->name = $request->name;
            $pharmacy->phone = $request->phone;
            $pharmacy->address = $request->address;
            $pharmacy->created_by = Auth::id();
            $pharmacy->save();

            // Add users to pharmacy with roles
            foreach ($request->user_ids as $index => $userId) {
                $role = $request->roles[$index] ?? 'staff';
                $pharmacy->addUser($userId, $role);
            }

            DB::commit();
            return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_created_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', localize('global.error_creating_pharmacy'));
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pharmacy = Pharmacy::with(['activeUsers', 'managers', 'staff', 'createdBy', 'updatedBy', 'outcomes'])->findOrFail($id);
        $statistics = $pharmacy->getStatistics();
        return view('pages.pharmacies.show', compact('pharmacy', 'statistics'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pharmacy = Pharmacy::with(['activeUsers'])->findOrFail($id);
        $users = User::all();
        return view('pages.pharmacies.edit', compact('pharmacy', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:191',
            'phone' => 'required|string|max:191',
            'address' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'in:manager,staff,viewer',
        ]);

        DB::beginTransaction();
        try {
            $pharmacy = Pharmacy::findOrFail($id);
            $pharmacy->name = $request->name;
            $pharmacy->phone = $request->phone;
            $pharmacy->address = $request->address;
            $pharmacy->updated_by = Auth::id();
            $pharmacy->save();

            // Update pharmacy users
            $pharmacy->users()->detach(); // Remove all existing users
            
            // Add users with new roles
            foreach ($request->user_ids as $index => $userId) {
                $role = $request->roles[$index] ?? 'staff';
                $pharmacy->addUser($userId, $role);
            }

            DB::commit();
            return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_updated_successfully'));
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', localize('global.error_updating_pharmacy'));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->deleted_by = Auth::id();
        $pharmacy->save();
        $pharmacy->delete();

        return redirect()->route('pharmacies.index')->with('success', localize('global.pharmacy_deleted_successfully'));
    }

    /**
     * Manage users for a pharmacy
     */
    public function manageUsers(string $id)
    {
        $pharmacy = Pharmacy::with(['activeUsers', 'managers', 'staff'])->findOrFail($id);
        $allUsers = User::all();
        $availableUsers = $allUsers->diff($pharmacy->activeUsers);
        
        return view('pages.pharmacies.manage-users', compact('pharmacy', 'availableUsers'));
    }

    /**
     * Add user to pharmacy
     */
    public function addUser(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:manager,staff,viewer',
            'permissions' => 'nullable|array'
        ]);

        $pharmacy = Pharmacy::findOrFail($id);
        
        if ($pharmacy->hasUser($request->user_id)) {
            return redirect()->back()->with('error', localize('global.user_already_assigned_to_pharmacy'));
        }

        $pharmacy->addUser($request->user_id, $request->role, $request->permissions);
        
        return redirect()->back()->with('success', localize('global.user_added_to_pharmacy_successfully'));
    }

    /**
     * Remove user from pharmacy
     */
    public function removeUser(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->removeUser($request->user_id);
        
        return redirect()->back()->with('success', localize('global.user_removed_from_pharmacy_successfully'));
    }

    /**
     * Update user role in pharmacy
     */
    public function updateUserRole(Request $request, string $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:manager,staff,viewer',
            'permissions' => 'nullable|array'
        ]);

        $pharmacy = Pharmacy::findOrFail($id);
        $pharmacy->updateUserRole($request->user_id, $request->role, $request->permissions);
        
        return redirect()->back()->with('success', localize('global.user_role_updated_successfully'));
    }
}
