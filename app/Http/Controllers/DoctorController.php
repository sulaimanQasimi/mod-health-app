<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $branches = Branch::all();
        return view('pages.doctors.create',compact('departments','branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'join_date' => 'nullable|date',
            'active_status' => 'nullable|boolean',
            'branch_id' => 'required',
            'department_id' => 'required',
        ]);

        // Convert active_status checkbox to boolean
        $data['active_status'] = $request->has('active_status') ? true : false;

        Doctor::create($data);

        return redirect()->route('doctors.index')->with('success', 'Doctor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $departments = Department::all();
        $branches = Branch::all();
        return view('pages.doctors.edit', compact('doctor', 'departments', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Doctor $doctor)
    {
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
            'join_date' => 'nullable|date',
            'active_status' => 'nullable|boolean',
            'branch_id' => 'required',
            'department_id' => 'required',
        ]);

        // Convert active_status checkbox to boolean
        $data['active_status'] = $request->has('active_status') ? true : false;

        $doctor->update($data);

        return redirect()->route('doctors.index')->with('success', 'Doctor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        //
    }
}
