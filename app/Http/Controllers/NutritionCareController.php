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
        $nurses = \App\Models\Nurse::all();
        return view('pages.nutrition-cares.create', compact('nurses'));
    }

    public function store(StoreNutritionCareRequest $request)
    {
        $nutritionCare = NutritionCare::create($request->validated());

        return response()->json([
            'message' => 'Nutrition care record created successfully',
            'data' => $nutritionCare->load(['morphable.patient', 'createdBy', 'nurse'])
        ], 201);
    }

    public function show(NutritionCare $nutritionCare)
    {
        $nutritionCare->load(['morphable.patient', 'createdBy', 'updatedBy', 'nurse']);
        $nurses = \App\Models\Nurse::all();
        return view('pages.nutrition-cares.show', compact('nutritionCare', 'nurses'));
    }

    public function edit(NutritionCare $nutritionCare)
    {
        $nurses = \App\Models\Nurse::all();
        return view('pages.nutrition-cares.edit', compact('nutritionCare', 'nurses'));
    }

    public function update(UpdateNutritionCareRequest $request, NutritionCare $nutritionCare)
    {
        $nutritionCare->update($request->validated());

        return response()->json([
            'message' => 'Nutrition care record updated successfully',
            'data' => $nutritionCare->load(['morphable.patient', 'updatedBy', 'nurse'])
        ]);
    }

    public function destroy(NutritionCare $nutritionCare)
    {
        $nutritionCare->delete();

        return response()->json([
            'message' => 'Nutrition care record deleted successfully'
        ]);
    }
}