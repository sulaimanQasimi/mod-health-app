<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Section;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sections = Section::all();
        return view('pages.sections.index',compact('sections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::all();
        $branches = Branch::all();
        return view('pages.sections.create',compact('departments','branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required',
            'department_id' => 'required'
        ]);

        Section::create($data);

        return redirect()->route('sections.index')->with('success', localize('global.section_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Section $section)
    {
        return view('pages.sections.show', compact('section'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Section $section)
    {
        $departments = Department::all();
        return view('pages.sections.edit', compact('section', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Section $section)
    {
        $data = $request->validate([
            'name' => 'required',
            'department_id' => 'required',
        ]);

        $section->update($data);

        return redirect()->route('sections.index')->with('success', localize('global.section_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Section $section)
    {
        $section->delete();
        return redirect()->route('sections.index')->with('success', localize('global.section_deleted_successfully.'));
    }
}
