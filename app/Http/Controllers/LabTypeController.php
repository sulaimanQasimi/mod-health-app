<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\LabType;
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
        return view('pages.lab_types.create',compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
        ]);

        LabType::create($data);

        return redirect()->route('lab_types.index')->with('success', 'Doctor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LabType $labType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabType $labType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LabType $labType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabType $labType)
    {
        //
    }
}
