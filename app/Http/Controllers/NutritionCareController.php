<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNutritionCareRequest;
use App\Http\Requests\UpdateNutritionCareRequest;
use App\Models\NutritionCare;
use Illuminate\Http\Request;

class NutritionCareController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', NutritionCare::class);

        if ($request->ajax()) {
            $nutritionCares = NutritionCare::with(['morphable.patient', 'createdBy', 'nurse'])
                ->when($request->morphable_type, function ($query, $type) {
                    return $query->where('morphable_type', $type);
                })
                ->when($request->morphable_id, function ($query, $id) {
                    return $query->where('morphable_id', $id);
                })
                ->get();

            return response()->json([
                'data' => $nutritionCares,
            ]);
        }

        $nurses = \App\Models\Nurse::all();
        return view('pages.nutrition-cares.index', compact('nurses'));
    }

    public function create()
    {
        $this->authorize('create', NutritionCare::class);
        
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nutrition-cares.create', compact('nurses', 'currentUser'));
    }

    public function store(StoreNutritionCareRequest $request)
    {
        $this->authorize('create', NutritionCare::class);
        
        $data = $request->validated();
        
        // Automatically set nurse_id from current authenticated user's nurse
        if (auth()->user()->nurse) {
            $data['nurse_id'] = auth()->user()->nurse->id;
        }
        
        $nutritionCare = NutritionCare::create($data);

        return response()->json([
            'message' => 'Nutrition care record created successfully',
            'data' => $nutritionCare->load(['morphable.patient', 'createdBy', 'nurse'])
        ], 201);
    }

    public function show(NutritionCare $nutritionCare)
    {
        $this->authorize('view', $nutritionCare);
        
        $nutritionCare->load(['morphable.patient', 'createdBy', 'updatedBy', 'nurse']);
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nutrition-cares.show', compact('nutritionCare', 'nurses', 'currentUser'));
    }

    public function edit(NutritionCare $nutritionCare)
    {
        $this->authorize('update', $nutritionCare);
        
        $nurses = \App\Models\Nurse::all();
        $currentUser = auth()->user()->load('nurse');
        return view('pages.nutrition-cares.edit', compact('nutritionCare', 'nurses', 'currentUser'));
    }

    public function update(UpdateNutritionCareRequest $request, NutritionCare $nutritionCare)
    {
        $this->authorize('update', $nutritionCare);
        
        $nutritionCare->update($request->validated());

        return response()->json([
            'message' => 'Nutrition care record updated successfully',
            'data' => $nutritionCare->load(['morphable.patient', 'updatedBy', 'nurse'])
        ]);
    }

    public function destroy(NutritionCare $nutritionCare)
    {
        $this->authorize('delete', $nutritionCare);
        
        $nutritionCare->delete();

        return response()->json([
            'message' => 'Nutrition care record deleted successfully'
        ]);
    }
}