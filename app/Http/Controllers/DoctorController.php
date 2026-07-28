<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\User;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Doctor::class);

        $query = Doctor::with(['department', 'branch']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('contact_number', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('qualification', 'like', "%{$search}%")
                  ->orWhere('room_no', 'like', "%{$search}%");
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Gender filter
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Active status filter
        if ($request->filled('active_status')) {
            $query->where('active_status', $request->active_status == '1');
        }

        // Clinic type filter
        if ($request->filled('clinic_type')) {
            $query->where('clinic_type', $request->clinic_type);
        }

        // Join date from filter
        if ($request->filled('join_date_from')) {
            $query->whereDate('join_date', '>=', verta::parse($request->join_date_from)->datetime());
        }

        // Join date to filter
        if ($request->filled('join_date_to')) {
            $query->whereDate('join_date', '<=', Verta::parse($request->join_date_to)->datetime());
        }

        $doctors = $query->orderBy('name')->paginate(15)->withQueryString();
        $departments = Department::all();
        $branches = Branch::all();

        return view('pages.doctors.index', compact('doctors', 'departments', 'branches'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        $this->authorize('view', $doctor);

        $doctor->load(['department', 'branch', 'user']);
        return view('pages.doctors.show', compact('doctor'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Doctor::class);

        $departments = Department::all();
        $users = User::where('is_doctor', true)->orderBy('name')->get();
        return view('pages.doctors.create', compact('departments', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Doctor::class);

        $data = $request->validate([
            'name' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'contact_number' => 'required',
            'father_name' => 'nullable|string',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string',
            'qualification' => 'nullable|string',
            'room_no' => 'nullable|string',
            'clinic_type' => 'nullable|in:hospital,clinic',
            'join_date' => 'nullable|string',
            'active_status' => 'nullable',
            'is_dentist' => 'nullable',
            'is_nephrologist' => 'nullable',
            'is_eye_doctor' => 'nullable',
            'department_id' => 'required',
        ]);

        // Set branch_id from authenticated user
        $data['branch_id'] = auth()->user()->branch_id;
        
        if (!$data['branch_id']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Branch ID is required. Please contact administrator.');
        }

        // Convert active_status checkbox to boolean
        $data['active_status'] = $request->has('active_status') ? true : false;

        // Convert is_dentist checkbox to boolean
        $data['is_dentist'] = $request->has('is_dentist') ? true : false;
        $data['is_nephrologist'] = $request->has('is_nephrologist') ? true : false;
        $data['is_eye_doctor'] = $request->has('is_eye_doctor') ? true : false;

        // Convert Dari date to standard date format if provided
        if ($request->filled('join_date')) {
            try {
                $data['join_date'] = Verta::parse($request->join_date)->datetime();
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Invalid date format for join date.');
            }
        }

        // Set created_by
        $data['created_by'] = auth()->id();

        Doctor::create($data);

        return redirect()->route('doctors.index')->with('success', localize('global.doctor_created_successfully'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        $departments = Department::all();
        $branches = Branch::all();
        $users = User::where('is_doctor', true)->orderBy('name')->get();
        return view('pages.doctors.edit', compact('doctor', 'departments', 'branches', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
        $this->authorize('update', $doctor);

        $data = $request->validate([
            'name' => 'required',
            'gender' => 'required|in:Male,Female,Other',
            'contact_number' => 'required',
            'father_name' => 'nullable|string',
            'address' => 'nullable|string',
            'specialization' => 'nullable|string',
            'qualification' => 'nullable|string',
            'room_no' => 'nullable|string',
            'clinic_type' => 'nullable|in:hospital,clinic',
            'join_date' => 'nullable|string',
            'active_status' => 'nullable',
            'is_dentist' => 'nullable',
            'is_nephrologist' => 'nullable',
            'is_eye_doctor' => 'nullable',
            'department_id' => 'required',
            'user_id' => 'nullable|exists:users,id',
        ]);

        // Automatically set branch_id from authenticated user (or keep existing if user doesn't have one)
        $data['branch_id'] = auth()->user()->branch_id ?? $doctor->branch_id;
        
        if (!$data['branch_id']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Branch ID is required. Please contact administrator.');
        }

        // Automatically set section_id from authenticated user, or get first section from department, or keep existing
        $data['section_id'] = auth()->user()->section_id ?? null;
        
        // If user doesn't have section_id, try to get first section from the selected department
        if (!$data['section_id'] && $data['department_id']) {
            $section = \App\Models\Section::where('department_id', $data['department_id'])->first();
            if ($section) {
                $data['section_id'] = $section->id;
            }
        }
        
        // If still no section_id, keep the existing one
        if (!$data['section_id']) {
            $data['section_id'] = $doctor->section_id;
        }

        // Convert active_status checkbox to boolean
        $data['active_status'] = $request->has('active_status') ? true : false;

        // Convert is_dentist checkbox to boolean
        $data['is_dentist'] = $request->has('is_dentist') ? true : false;
        $data['is_nephrologist'] = $request->has('is_nephrologist') ? true : false;
        $data['is_eye_doctor'] = $request->has('is_eye_doctor') ? true : false;

        // Convert Dari date to standard date format if provided
        if ($request->filled('join_date')) {
            try {
                $data['join_date'] = Verta::parse($request->join_date)->datetime();
            } catch (\Exception $e) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Invalid date format for join date.');
            }
        }

        $doctor->update($data);

        return redirect()->route('doctors.index')->with('success', localize('global.doctor_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $this->authorize('delete', $doctor);

        $doctor->delete();

        return redirect()->route('doctors.index')->with('success', localize('global.doctor_deleted_successfully'));
    }
}
