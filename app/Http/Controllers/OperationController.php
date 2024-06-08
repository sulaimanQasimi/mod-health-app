<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\FoodType;
use App\Models\Operation;
use App\Models\Relation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;

class OperationController extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function new()
    {

        $operations = Anesthesia::with('patient')->where('status', 'approved')->where('is_operation_approved', '0')->latest()->paginate(15);

        return view('pages.operations.new', compact('operations'));
    }

    public function approved()
    {

        $operations = Anesthesia::with('patient')->where('status', 'approved')->where('is_operation_approved', '1')->where('is_operation_done', '0')->latest()->paginate(15);

        return view('pages.operations.approved', compact('operations'));
    }


    public function completed()
    {
        $operations = Anesthesia::where('is_operation_done', '1')->latest()->paginate(15);

        return view('pages.operations.completed', compact('operations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Anesthesia $operation)
    {
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $rooms = Room::all();
        $beds = Bed::all();
        $foodTypes = FoodType::all();
        $relations = Relation::all();
        return view('pages.operations.show',compact('operation','operation_doctors','rooms','beds','foodTypes','relations'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Operation $operation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'is_operation_approved' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'operation_scrub_nurse_id' => 'nullable',
            'operation_circulation_nurse_id' => 'nullable',
            'date' => 'nullable',
            'time' => 'nullable',
            'operation_expense_remarks' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',

        ]);

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();

        $operation->update($data);

        return redirect()->back()->with('success', 'Operation updated successfully.');
    }

    public function complete(Request $request, Anesthesia $operation)
    {
        $data = $request->validate([
            'is_operation_done' => 'nullable',
            'operation_remark' => 'nullable',
            'operation_result' => 'nullable',
            'room_id' => 'nullable',
            'bed_id' => 'nullable',

        ]);

        $data['room_id'] = $operation->room->id ?? '';
        $data['bed_id'] = $operation->bed->id ?? '';
        
        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => false]);
        $occupied_bed->save();

        $operation->update($data);

        return redirect()->back()->with('success', 'Operation updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Operation $operation)
    {
        //
    }
}
