<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewPACUNotification;
use App\Models\PACU;
use Illuminate\Http\Request;

class PACUController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pacus = PACU::where('branch_id',auth()->user()->branch_id)->with('patient')->where('status', 'new')->get();

                if ($pacus) {
                    return response()->json([
                        'data' => $pacus,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $pacus = PACU::where('branch_id',auth()->user()->branch_id)->with(['patient'])->where('status', 'new')->get();
        return view('pages.pacus.index', compact('pacus'));
    }

    public function completed()
    {
        $pacus = PACU::where('status', 'completed')->latest()->paginate(10);

        return view('pages.pacus.completed', compact('pacus'));
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
            'patient_id' => 'required',
            'department_id' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'description' => 'required',
            'operation_id' => 'nullable',
        ]);

        // Create a new appointment
        $pacu = PACU::create($validatedData);

        SendNewPACUNotification::dispatch($pacu->created_by, $pacu->id);
        // Redirect to the appointments index page with a success message
        return redirect()->back()->with('success', 'PACU created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PACU $pacu)
    {
        return view('pages.pacus.show',compact('pacu'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PACU $pacu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PACU $pacu)
    {
        $data = $request->validate([
            'status' => 'nullable',

        ]);

        $pacu->update($data);


        return redirect()->route('pacus.new')->with('success', 'PACU updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PACU $pacu)
    {
        //
    }

    public function complete($pacuId)
    {
        $pacu = PACU::findOrFail($pacuId);
        $pacu->complete();
        return redirect()->route('pacus.index')->with('success', 'PACU Completed successfully.');

    }
}
