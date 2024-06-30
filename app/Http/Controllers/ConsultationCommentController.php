<?php

namespace App\Http\Controllers;

use App\Models\ConsultationComment;
use Illuminate\Http\Request;

class ConsultationCommentController extends Controller
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
        // Validate the input
        $validatedData = $request->validate([
            'comment' => 'required',
            'consultation_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'department_id' => 'required',
            'appointment_id' => 'required',
        ]);

        ConsultationComment::create($validatedData);

        return redirect()->back()->with('success', 'Comment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ConsultationComment $consultationComment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ConsultationComment $consultationComment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ConsultationComment $consultationComment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ConsultationComment $consultationComment)
    {
        //
    }
}
