<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNursingAssessmentRequest;
use App\Http\Requests\UpdateNursingAssessmentRequest;
use App\Models\NursingAssessment;
use Illuminate\Http\Request;

class NursingAssessmentController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', NursingAssessment::class);

        if ($request->ajax()) {
            $nursingAssessments = NursingAssessment::with(['morphable.patient', 'createdBy', 'nurse'])
                ->when($request->morphable_type, function ($query, $type) {
                    return $query->where('morphable_type', $type);
                })
                ->when($request->morphable_id, function ($query, $id) {
                    return $query->where('morphable_id', $id);
                })
                ->get();

            return response()->json([
                'data' => $nursingAssessments,
            ]);
        }

        $nurses = \App\Models\Nurse::all();
        return view('pages.nursing-assessments.index', compact('nurses'));
    }

    public function create()
    {
        $this->authorize('create', NursingAssessment::class);
        
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nursing-assessments.create', compact('nurses', 'currentUser'));
    }

    public function store(StoreNursingAssessmentRequest $request)
    {
        $this->authorize('create', NursingAssessment::class);
        
        $data = $request->validated();
        
        // Automatically set nurse_id from current authenticated user's nurse
        if (auth()->user()->nurse) {
            $data['nurse_id'] = auth()->user()->nurse->id;
        }
        
        $nursingAssessment = NursingAssessment::create($data);

        return response()->json([
            'message' => 'Nursing assessment created successfully',
            'data' => $nursingAssessment->load(['morphable.patient', 'createdBy', 'nurse'])
        ], 201);
    }

    public function show(NursingAssessment $nursingAssessment)
    {
        $this->authorize('view', $nursingAssessment);
        
        $nursingAssessment->load(['morphable.patient', 'createdBy', 'updatedBy', 'nurse']);
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nursing-assessments.show', compact('nursingAssessment', 'nurses', 'currentUser'));
    }

    public function edit(NursingAssessment $nursingAssessment)
    {
        $this->authorize('update', $nursingAssessment);
        
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nursing-assessments.edit', compact('nursingAssessment', 'nurses', 'currentUser'));
    }

    public function update(UpdateNursingAssessmentRequest $request, NursingAssessment $nursingAssessment)
    {
        $this->authorize('update', $nursingAssessment);
        
        $nursingAssessment->update($request->validated());

        return response()->json([
            'message' => 'Nursing assessment updated successfully',
            'data' => $nursingAssessment->load(['morphable.patient', 'updatedBy', 'nurse'])
        ]);
    }

    public function destroy(NursingAssessment $nursingAssessment)
    {
        $this->authorize('delete', $nursingAssessment);
        
        $nursingAssessment->delete();

        return response()->json([
            'message' => 'Nursing assessment deleted successfully'
        ]);
    }

    public function print(NursingAssessment $nursingAssessment)
    {
        $this->authorize('view', $nursingAssessment);
        
        $nursingAssessment->load(['morphable.patient', 'nurse']);
        return view('pages.nursing-assessments.print', compact('nursingAssessment'));
    }
}