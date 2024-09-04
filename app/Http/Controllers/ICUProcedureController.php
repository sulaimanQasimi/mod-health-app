<?php

namespace App\Http\Controllers;

use App\Models\ICUProcedure;
use App\Models\ICUProcedureType;
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
                        ->with('success', localize('global.icu_procedure_created_successfully.'));
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
        $procedure_types = ICUProcedureType::all();
        return view('pages.icus.icu_procedure_edit',compact('iCUProcedure','procedure_types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ICUProcedure $iCUProcedure)
    {
        // Validate the request data
        $request->validate([
            'icu_procedure_type_id' => 'required|exists:i_c_u_procedure_types,id',
            'description' => 'required',
        ]);

        // Update the record
        $iCUProcedure->icu_procedure_type_id = $request->icu_procedure_type_id;
        $iCUProcedure->description = $request->description;
        $iCUProcedure->save();

        // Redirect back with success message
        return redirect()->route('icus.show',$iCUProcedure->icu->id)
                        ->with('success', localize('global.icu_procedure_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICUProcedure $iCUProcedure)
    {
        $iCUProcedure->delete();
        return redirect()->route('icus.show',$iCUProcedure->icu->id)
                        ->with('success', localize('global.icu_procedure_deleted_successfully.'));
    }
}
