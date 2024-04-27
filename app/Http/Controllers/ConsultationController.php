<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $consultations = Consultation::all();
        return view('pages.consultations.index',compact('consultations'));
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
            'title' => 'required',
            'appointment_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'branch_id' => 'required',
            'date' => 'required',
            'time' => 'required',
        ]);

        Consultation::create($data);

        return redirect()->back()->with('success', 'Consultation created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lab $lab)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lab $lab)
    {
        return view('pages.labs.edit',compact('lab'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lab $lab)
    {
        $data = $request->validate([
            'result' => 'required',
            'result_file' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048'
        ]);
    
        // Handle the result file upload
        if ($request->hasFile('result_file')) {
            $resultFile = $request->file('result_file');
            $resultFileName = time().'.'.$resultFile->getClientOriginalExtension();
    
            // Store the result file in the storage/app/public directory
            $resultFile->storeAs('public', $resultFileName);
    
            // Update the result_file field
            $data['result_file'] = $resultFileName;
        }
    
        $lab->update($data);
    
        return redirect()->back()->with('success', 'Lab Test updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lab $lab)
    {
        //
    }
}
