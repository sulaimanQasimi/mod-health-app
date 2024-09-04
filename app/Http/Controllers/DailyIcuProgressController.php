<?php

namespace App\Http\Controllers;

use App\Models\DailyIcuProgress;
use App\Models\LabType;
use Illuminate\Http\Request;

class DailyIcuProgressController extends Controller
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
        $data = $request->validate([
            'i_c_u_id' => 'nullable',
            'icu_day' => 'nullable',
            'icu_diagnose' => 'nullable',
            'daily_events' => 'nullable',
            'hr' => 'nullable',
            'bp' => 'nullable',
            'spo2' => 'nullable',
            't' => 'nullable',
            'rr' => 'nullable',
            'gcs' => 'nullable',
            'cvs' => 'nullable',
            'pupils' => 'nullable',
            's1s2' => 'nullable',
            'rs' => 'nullable',
            'gi' => 'nullable',
            'renal' => 'nullable',
            'musculoskeletal_system' => 'nullable',
            'extremities' => 'nullable',
            'lab_ids' => 'nullable',
            'assesment' => 'nullable',
            'plan' => 'nullable'
        ]);

        $data['lab_ids'] = json_encode($data['lab_ids']);

        DailyIcuProgress::create($data);

        return redirect()->back()->with('success', localize('global.daily_progress_created_successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(DailyIcuProgress $dailyIcuProgress)
    {
        $labTypes = LabType::all();
        return view('pages.daily_icu_progress.show',compact('dailyIcuProgress','labTypes'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DailyIcuProgress $dailyIcuProgress)
    {
        $labTypes = LabType::all();
        return view('pages.daily_icu_progress.edit',compact('dailyIcuProgress','labTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DailyIcuProgress $dailyIcuProgress)
    {
        $data = $request->validate([
            'i_c_u_id' => 'nullable',
            'icu_day' => 'nullable',
            'icu_diagnose' => 'nullable',
            'daily_events' => 'nullable',
            'hr' => 'nullable',
            'bp' => 'nullable',
            'spo2' => 'nullable',
            't' => 'nullable',
            'rr' => 'nullable',
            'gcs' => 'nullable',
            'cvs' => 'nullable',
            'pupils' => 'nullable',
            's1s2' => 'nullable',
            'rs' => 'nullable',
            'gi' => 'nullable',
            'renal' => 'nullable',
            'musculoskeletal_system' => 'nullable',
            'extremities' => 'nullable',
            'lab_ids' => 'nullable',
            'assesment' => 'nullable',
            'plan' => 'nullable'
        ]);

        // Encode lab_ids as JSON if it exists
        if (isset($data['lab_ids'])) {
            $data['lab_ids'] = json_encode($data['lab_ids']);
        }

        // Update the record
        $dailyIcuProgress->update($data);

        // Redirect back with success message
        return redirect()->route('icus.show',$dailyIcuProgress->icu->id)->with('success', localize('global.daily_progress_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DailyIcuProgress $dailyIcuProgress)
    {
        //
    }
}
