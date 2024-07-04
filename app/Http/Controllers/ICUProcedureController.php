<?php

namespace App\Http\Controllers;

use App\Models\ICUProcedure;
use Illuminate\Http\Request;

class ICUProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $request->validate([
            'icu_procedure_type_id' => 'required|exists:i_c_u_procedure_types,id',
            'description' => 'required',
            'i_c_u_id' => 'required',
        ]);

        $icuProcedure = new ICUProcedure();
        $icuProcedure->icu_procedure_type_id = $request->icu_procedure_type_id;
        $icuProcedure->description = $request->description;
        $icuProcedure->i_c_u_id = $request->i_c_u_id;
        $icuProcedure->save();

        return redirect()->back()
                        ->with('success', 'ICU Procedure created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ICUProcedure $iCUProcedure)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ICUProcedure $iCUProcedure)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ICUProcedure $iCUProcedure)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICUProcedure $iCUProcedure)
    {
        //
    }
}
