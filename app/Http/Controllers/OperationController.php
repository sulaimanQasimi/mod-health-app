<?php

namespace App\Http\Controllers;

use App\Models\Anesthesia;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Http\Request;

class OperationController extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function new()
    {

        $operations = Anesthesia::where('status', 'approved')->where('is_operation_done', '0')->latest()->paginate(15);

        return view('pages.operations.new', compact('operations'));
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
        return view('pages.operations.show',compact('operation'));
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
            'is_operation_done' => 'required',
            'operation_remark' => 'required',
            'operation_result' => 'required',
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
