<?php

namespace App\Http\Controllers;

use App\Jobs\SendNewHospitalizationNotification;
use App\Models\Bed;
use App\Models\Hospitalization;
use App\Models\LabType;
use App\Models\LabTypeSection;
use App\Models\Medicine;
use App\Models\MedicineType;
use App\Models\OperationType;
use App\Models\User;
use Illuminate\Http\Request;

class HospitalizationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();

                if ($hospitalizations) {
                    return response()->json([
                        'data' => $hospitalizations,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','0')->with(['patient','room','bed'])->get();
        return view('pages.hospitalizations.index', compact('hospitalizations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    public function discharged(Request $request)
    {
        if ($request->ajax()) {
            $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','1')->with(['patient','room','bed'])->get();

                if ($hospitalizations) {
                    return response()->json([
                        'data' => $hospitalizations,
                    ]);
                } else {
                    return response()->json([
                        'message' => 'Internal Server Error',
                        'code' => 500,
                        'data' => [],
                    ]);
                }
        }

        $hospitalizations = Hospitalization::where('branch_id',auth()->user()->branch_id)->where('is_discharged','1')->with(['patient','room','bed'])->get();
        return view('pages.hospitalizations.discharged',compact('hospitalizations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'reason' => 'required',
            'remarks' => 'required',
            'room_id' => 'required',
            'patient_id' => 'required',
            'doctor_id' => 'required',
            'bed_id' => 'required',
            'appointment_id' => 'required',
            'is_discharged' => 'nullable',
            'discharge_remark' => 'nullable',
            'branch_id' => 'required',
            'discharge_status'=> 'nullable',
            'food_type_id'=> 'nullable',
            'patinet_companion'=> 'nullable',
            'companion_father_name'=> 'nullable',
            'relation_to_patient'=> 'nullable',
            'companion_card_type'=> 'nullable',
            'discharged_at'=> 'nullable',
            'under_review_id'=> 'nullable',
        ]);

        $data['food_type_id'] = json_encode($data['food_type_id']);

        $hospitalization = Hospitalization::create($data);

        $occupied_bed = Bed::findOrFail($data['bed_id']);

        $occupied_bed->update(['is_occupied' => true]);
        $occupied_bed->save();

        SendNewHospitalizationNotification::dispatch($hospitalization->created_by, $hospitalization->id);


        return redirect()->back()->with('success', 'Hospitalization created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Hospitalization $hospitalization)
    {

        $labTypeSections = LabTypeSection::all();
        $operationTypes = OperationType::where('branch_id', auth()->user()->branch_id)->get();
        $labTypes = LabType::all();
        $operation_doctors = User::where('branch_id', auth()->user()->branch_id)->get();
        $medicineTypes = MedicineType::all();
        $medicines = Medicine::all();
        return view('pages.hospitalizations.show',compact('hospitalization','labTypeSections','operationTypes','labTypes','operation_doctors',
    'medicineTypes','medicines'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Hospitalization $hospitalization)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hospitalization $hospitalization)
    {
        $data = $request->validate([
            'is_discharged' => 'required',
            'discharge_remark' => 'required',
            'discharge_status' => 'required',
            'discharged_at' => 'required',
        ]);


        $hospitalization->update($data);

        $occupied_bed = Bed::findOrFail($hospitalization->bed_id);
        $occupied_bed->update(['is_occupied' => false]);
        $occupied_bed->save();

        return redirect()->route('hospitalizations.index')->with('success', 'Hospitalization updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hospitalization $hospitalization)
    {
        //
    }
}
