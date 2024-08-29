<?php

namespace App\Http\Controllers;

use App\Models\ICUProcedureType;
use Illuminate\Http\Request;

class ICUProcedureTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $procedure_types = ICUProcedureType::all();
        return view('pages.procedure_types.index',compact('procedure_types'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.procedure_types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:i_c_u_procedure_types,name',
        ]);

        $icuProcedureType = new ICUProcedureType;
        $icuProcedureType->name = $request->name;
        $icuProcedureType->save();

        return redirect()->route('procedure_types.index')
                        ->with('success',localize('global.icu_procedure_type_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(ICUProcedureType $iCUProcedureType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ICUProcedureType $iCUProcedureType)
    {
        return view('pages.procedure_types.edit',compact('iCUProcedureType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ICUProcedureType $iCUProcedureType)
    {
        $request->validate([
            'name' => 'required|unique:i_c_u_procedure_types,name,' . $iCUProcedureType->id,
        ]);

        $iCUProcedureType->name = $request->name;
        $iCUProcedureType->save();

        return redirect()->route('procedure_types.index')
                        ->with('success',localize('global.icu_procedure_type_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ICUProcedureType $iCUProcedureType)
    {
        $iCUProcedureType->delete();

        return redirect()->route('procedure_types.index')
                        ->with('success',localize('global.icu_procedure_type_deleted_successfully.'));
    }
}
