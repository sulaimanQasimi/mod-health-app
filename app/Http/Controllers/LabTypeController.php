<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Section;
use Illuminate\Http\Request;

class LabTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LabType::with(['branch', 'section', 'departmentSection', 'parent']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('branch', function($branchQuery) use ($search) {
                      $branchQuery->where('name', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('section', function($sectionQuery) use ($search) {
                      $sectionQuery->where('section', 'like', '%' . $search . '%');
                  })
                  ->orWhereHas('departmentSection', function($departmentSectionQuery) use ($search) {
                      $departmentSectionQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by section
        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }

        // Filter by department section
        if ($request->filled('department_section_id')) {
            $query->where('department_section_id', $request->department_section_id);
        }

        // Filter by parent
        if ($request->filled('parent_id')) {
            if ($request->parent_id === 'null') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $request->parent_id);
            }
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        
        if (in_array($sortBy, ['name', 'created_at'])) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 15);
        $labTypes = $query->paginate($perPage)->appends(request()->query());

        // Get filter options for the view
        $branches = Branch::orderBy('name')->get();
        $labTypeSections = LabTypeSection::orderBy('section')->get();
        $sections = Section::orderBy('name')->get();
        $parentLabTypes = LabType::whereNull('parent_id')->orderBy('name')->get();

        return view('pages.lab_types.index', compact(
            'labTypes', 
            'branches', 
            'labTypeSections', 
            'sections',
            'parentLabTypes'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        $sections = Section::all();
        return view('pages.lab_types.create',compact('branches','labTypes','labTypeSections','sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
            'section_id' => 'required',
            'department_section_id' => 'nullable',
            'parent_id' => 'nullable',
        ]);

        LabType::create($data);

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_created_successfully.'));
    }

   /**
     * Display the specified resource.
     */
    public function show(LabType $labType)
    {
        return view('pages.lab_types.show', compact('labType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabType $labType)
    {
        $branches = Branch::all();
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        $sections = Section::all();
        return view('pages.lab_types.edit', compact('labType', 'branches', 'labTypes', 'labTypeSections', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LabType $labType)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
            'section_id' => 'required',
            'department_section_id' => 'nullable',
            'parent_id' => 'nullable',
        ]);

        $labType->update($data);

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabType $labType)
    {
        $labType->delete();

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_deleted_successfully.'));
    }

}
