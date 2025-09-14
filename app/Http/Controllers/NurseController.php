<?php

namespace App\Http\Controllers;

use App\Models\Nurse;
use App\Models\Department;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NurseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Nurse::with(['user', 'department', 'branch', 'createdBy', 'updatedBy']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
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

        $nurses = $query->orderBy('created_at', 'desc')->paginate(15);
        $departments = Department::all();
        $branches = Branch::all();

        return view('pages.nurses.index', compact('nurses', 'departments', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $branches = Branch::all();
        $users = User::whereDoesntHave('nurse')->get(['id', 'name', 'email']);
        return view('pages.nurses.create', compact('departments', 'branches', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'employee_id' => 'required|string|max:255|unique:nurses,employee_id',
            'department_id' => 'nullable|exists:departments,id',
            'specialization' => 'nullable|string|max:255',
            'shift' => 'required|in:morning,evening,night',
            'employment_status' => 'required|in:active,inactive,on_leave',
            'date_of_joining' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $nurse = Nurse::create($request->all());

            DB::commit();

            return redirect()->route('nurses.index')
                ->with('success', 'Nurse created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to create nurse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Nurse $nurse)
    {
        $nurse->load(['user', 'department', 'branch', 'createdBy', 'updatedBy']);
        return view('pages.nurses.show', compact('nurse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nurse $nurse)
    {
        $departments = Department::all();
        $branches = Branch::all();
        $users = User::whereDoesntHave('nurse')->orWhere('id', $nurse->user_id)->get(['id', 'name', 'email']);
        return view('pages.nurses.edit', compact('nurse', 'departments', 'branches', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nurse $nurse)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'date_of_birth' => 'required|date|before:today',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'employee_id' => 'required|string|max:255|unique:nurses,employee_id,' . $nurse->id,
            'department_id' => 'nullable|exists:departments,id',
            'specialization' => 'nullable|string|max:255',
            'shift' => 'required|in:morning,evening,night',
            'employment_status' => 'required|in:active,inactive,on_leave',
            'date_of_joining' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $nurse->update($request->all());

            DB::commit();

            return redirect()->route('nurses.index')
                ->with('success', 'Nurse updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to update nurse: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nurse $nurse)
    {
        try {
            DB::beginTransaction();

            $nurse->delete();

            DB::commit();

            return redirect()->route('nurses.index')
                ->with('success', 'Nurse deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to delete nurse: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to get nurses for select dropdown
     */
    public function getNursesForSelect(Request $request)
    {
        $query = Nurse::active();

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        $nurses = $query->select('id', 'first_name', 'last_name', 'employee_id')
            ->orderBy('first_name')
            ->get()
            ->map(function ($nurse) {
                return [
                    'id' => $nurse->id,
                    'text' => $nurse->full_name . ' (' . $nurse->employee_id . ')'
                ];
            });

        return response()->json($nurses);
    }
}
