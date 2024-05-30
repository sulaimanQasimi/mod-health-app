<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewLabNotification;
use App\Models\Appointment;
use App\Models\Lab;
use Illuminate\Http\Request;

class LabController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $labs = Lab::where('branch_id', auth()->user()->branch_id)->where('result',null)->latest()->paginate(10);
        return view('pages.labs.index',compact('labs'));
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
        'lab_type_id' => 'required|array',
        'appointment_id' => 'required',
        'patient_id' => 'required',
        'doctor_id' => 'required',
        'branch_id' => 'required',
        'status' => 'nullable',
        'hospitalization_id' => 'nullable',
        'under_review_id' => 'nullable',
        'i_c_u_id' => 'nullable',
    ]);

    $labTypeIds = $data['lab_type_id'];
    unset($data['lab_type_id']);

    foreach ($labTypeIds as $labTypeId) {
        $labData = array_merge($data, ['lab_type_id' => $labTypeId]);
        $lab = Lab::create($labData);
    }

    SendNewLabNotification::dispatch($lab->created_by, $lab->id);

    return redirect()->back()->with('success', 'Lab Tests created successfully.');
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
            'result_file' => 'nullable|mimes:pdf,jpeg,png,jpg,gif|max:2048',
            'status' => 'required',
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

        return redirect()->route('lab_tests.index')->with('success', 'Lab Test updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lab $lab)
    {
        //
    }

    public function printCard($appointmentId)
    {
        $appointment = Appointment::with(['labs' =>
            function ($query)
            {
                $query->where('status',0);
            }, 'patient'
           ])->findOrFail($appointmentId);

        $labs = $appointment->labs;
        $patient = $appointment->patient;
        return view('pages.labs.print_card', compact('labs','patient'));
    }
}
