<?php

namespace App\Http\Controllers;

use App\Models\Hospitalization;
use App\Models\Visit;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hospitalizations = Hospitalization::where('branch_id', auth()->user()->branch_id)->latest()->paginate(10);

        return view('pages.hospitalizations.index',compact('hospitalizations'));
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

        $data = $request->validate([
            'description' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'hospitalization_id' => 'required',

        ]);



        Visit::create($data);


        return redirect()->back()->with('success', 'Visit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Visit $visit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Visit $visit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Visit $visit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Visit $visit)
    {
        //
    }
}
