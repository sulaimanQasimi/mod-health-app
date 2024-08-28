<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Floor;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rooms = Room::all();
        return view('pages.rooms.index',compact('rooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        $floors = Floor::all();
        $departments = Department::all();
        return view('pages.rooms.create', compact('branches','floors','departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
            'floor_id' => 'required',
            'department_id' => 'required',
        ]);

        Room::create($data);

        return redirect()->route('rooms.index')->with('success', localize('global.room_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Room $room)
    {
        // Assuming you have a relationship for beds or a method to get the bed count
        $room->load('beds'); // Adjust this if you have a relationship for beds
        return view('pages.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Room $room)
    {
        $branches = Branch::all();
        $floors = Floor::all();
        $departments = Department::all();
        return view('pages.rooms.edit', compact('room', 'branches', 'floors', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'name' => 'required',
            'branch_id' => 'required',
            'floor_id' => 'required',
            'department_id' => 'required',
        ]);

        $room->update($data);

        return redirect()->route('rooms.index')->with('success', localize('global.room_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('rooms.index')->with('success', localize('global.room_deleted_successfully.'));
    }
}
