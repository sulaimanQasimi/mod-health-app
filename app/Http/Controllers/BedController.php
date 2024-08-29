<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use Illuminate\Http\Request;

class BedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $beds = Bed::all();
        return view('pages.beds.index',compact('beds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $rooms = Room::all();
        return view('pages.beds.create', compact('rooms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'number' => 'required',
            'room_id' => 'required',
            'is_occupied' => 'nullable'
        ]);

        Bed::create($data);

        return redirect()->route('beds.index')->with('success', localize('global.bed_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Bed $bed)
    {
        return view('pages.beds.show', compact('bed'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bed $bed)
    {
        $rooms = Room::all();
        return view('pages.beds.edit', compact('bed', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bed $bed)
    {
        $data = $request->validate([
            'number' => 'required',
            'room_id' => 'required',
            'is_occupied' => 'nullable'
        ]);

        $bed->update($data);

        return redirect()->route('beds.index')->with('success', localize('global.bed_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bed $bed)
    {
        $bed->delete();
        return redirect()->route('beds.index')->with('success', localize('global.bed_deleted_successfully.'));
    }
}
