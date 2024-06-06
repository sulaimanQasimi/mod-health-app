<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Bed;
use App\Models\Operation;
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
        return view('pages.operations.show',compact('operation','operation_doctors','rooms','beds'));
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
            'operation_expense_remarks' => 'nullable'

        ]);

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
