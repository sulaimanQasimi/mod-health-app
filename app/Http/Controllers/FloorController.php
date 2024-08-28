<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Floor;
use Illuminate\Http\Request;

class FloorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $floors = Floor::all();
        return view('pages.floors.index',compact('floors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        return view('pages.floors.create', compact('branches'));
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

        Floor::create($data);

        return redirect()->route('floors.index')->with('success', localize('global.floor_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Floor $floor)
    {
        return view('pages.floors.show', compact('floor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Floor $floor)
    {
        $branches = Branch::all();
        return view('pages.floors.edit', compact('floor', 'branches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Floor $floor)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
        ]);

        $floor->update($data);

        return redirect()->route('floors.index')->with('success', localize('global.floor_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Floor $floor)
    {
        $floor->delete();
        return redirect()->route('floors.index')->with('success', localize('global.floor_deleted_successfully.'));
    }
}
