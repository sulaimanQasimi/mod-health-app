<?php

namespace App\Http\Controllers;

use App\Models\Advice;
use Illuminate\Http\Request;

class AdviceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'description' => 'required|max:1000',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_id' => 'required|exists:appointments,id',
            'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
            'hospitalization_id' => 'nullable|exists:hospitalizations,id',
        ]);

        $advice = Advice::create($validatedData);

        return redirect()->back()->with('success', localize('global.advice_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Advice $advice)
    {
        return view('advices.show', compact('advice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Advice $advice)
    {
        return view('advices.edit', compact('advice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Advice $advice)
    {
        $validatedData = $request->validate([
            'description' => 'required|max:1000',
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'appointment_id' => 'required|exists:appointments,id',
            'i_c_u_id' => 'nullable|exists:i_c_u_s,id',
            'hospitalization_id' => 'nullable|exists:hospitalizations,id',
        ]);

        $advice->update($validatedData);

        return redirect()->route('advices.show', $advice)->with('success', localize('global.advice_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Advice $advice)
    {
        $advice->delete();

        return redirect()->back()->with('success', localize('global.advice_deleted_successfully.'));
    }
}
