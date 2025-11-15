<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::all();
        return view('pages.doctors.index',compact('doctors'));
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
