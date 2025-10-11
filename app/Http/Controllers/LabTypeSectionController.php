<?php

namespace App\Http\Controllers;

use App\Models\LabTypeSection;
use App\Models\Section;
use Illuminate\Http\Request;

class LabTypeSectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labTypeSections = LabTypeSection::with('relatedSection')->paginate(10);
        return view('pages.lab_type_sections.index',compact('labTypeSections'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $sections = Section::all();
        return view('pages.lab_type_sections.create', compact('sections'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'section' => 'required',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        LabTypeSection::create($data);

        return redirect()->route('lab_type_sections.index')->with('success', localize('global.lab_type_section_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(LabTypeSection $labTypeSection)
    {
        return view('pages.lab_type_sections.show', compact('labTypeSection'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabTypeSection $labTypeSection)
    {
        $sections = Section::all();
        return view('pages.lab_type_sections.edit', compact('labTypeSection', 'sections'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LabTypeSection $labTypeSection)
    {
        $data = $request->validate([
            'section' => 'required',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $labTypeSection->update($data);

        return redirect()->route('lab_type_sections.index')->with('success', localize('global.lab_type_section_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabTypeSection $labTypeSection)
    {
        $labTypeSection->delete();

        return redirect()->route('lab_type_sections.index')->with('success', localize('global.lab_type_section_deleted_successfully.'));
    }
}
