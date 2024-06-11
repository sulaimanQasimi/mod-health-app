<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewBloodBankNotification;
use App\Models\BloodBank;
use Illuminate\Http\Request;

class BloodBankController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function new()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status','new')->paginate(15);
        return view('pages.blood_banks.new',compact('bloodRequests'));
    }

    public function approved()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status','approved')->paginate(15);
        return view('pages.blood_banks.approved',compact('bloodRequests'));
    }

    public function rejected()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status','rejected')->paginate(15);
        return view('pages.blood_banks.rejected',compact('bloodRequests'));
    }

    public function delivered()
    {
        $bloodRequests = BloodBank::where('branch_id', auth()->user()->branch_id)->where('status','delivered')->paginate(15);
        return view('pages.blood_banks.delivered',compact('bloodRequests'));
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
            'group' => 'required',
            'rh' => 'required',
            'quantity' => 'required',
            'branch_id' => 'required',
            'appointment_id' => 'nullable',
            'hospitalization_id' => 'nullable',
            'i_c_u_id' => 'nullable',
            'operation_id' => 'nullable',
            'under_review_id' => 'nullable',
            'anesthesia_id' => 'nullable',
            'patient_id' => 'nullable',
            'department_id' => 'nullable',
        ]);


        $bloodBank = BloodBank::create($validatedData);

        SendNewBloodBankNotification::dispatch($bloodBank->created_by, $bloodBank->id);

        return redirect()->back()->with('success', 'Blood Request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BloodBank $bloodBank)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BloodBank $bloodBank)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BloodBank $bloodBank)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BloodBank $bloodBank)
    {
        //
    }
}
