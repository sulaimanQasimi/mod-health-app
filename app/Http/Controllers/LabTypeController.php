<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LabType;
use App\Models\LabTypeSection;
use Illuminate\Http\Request;

class LabTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labTypes = LabType::all();
        return view('pages.lab_types.index',compact('labTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        return view('pages.lab_types.create',compact('branches','labTypes','labTypeSections'));
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
        return view('pages.lab_types.edit', compact('labType', 'branches', 'labTypes', 'labTypeSections'));
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
