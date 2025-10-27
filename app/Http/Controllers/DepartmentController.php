<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::with(['category']);
        
        // Add category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        $departments = $query->get();
        $categories = Category::all();
        return view('pages.departments.index',compact('departments', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        $categories = Category::all();
        return view('pages.departments.create', compact('branches', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'room_number' => 'nullable|string|max:255',
            'branch_id' => 'required',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        Department::create($data);

        return redirect()->route('departments.index')->with('success', localize('global.department_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return view('pages.departments.show', compact('department'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $branches = Branch::all();
        $categories = Category::all();
        return view('pages.departments.edit', compact('department', 'branches', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'name' => 'required',
            'room_number' => 'nullable|string|max:255',
            'branch_id' => 'required',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $department->update($data);

        return redirect()->route('departments.index')->with('success', localize('global.department_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        $department->delete();
        return redirect()->route('departments.index')->with('success', localize('global.department_deleted_successfully.'));
    }
}
