<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\OperationType;
use Illuminate\Http\Request;

class OperationTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $operationTypes = OperationType::all();
        return view('pages.operation_types.index',compact('operationTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::all();
        return view('pages.operation_types.create',compact('branches'));
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

        OperationType::create($data);

        return redirect()->route('operation_types.index')->with('success', 'Operation Type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OperationType $operationType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(OperationType $operationType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OperationType $operationType)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OperationType $operationType)
    {
        //
    }
}
