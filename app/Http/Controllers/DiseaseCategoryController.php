<?php

namespace App\Http\Controllers;

use App\Models\DiseaseCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DiseaseCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = DiseaseCategory::withCount('diseases')->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:disease_categories,name'],
        ]);

        $category = DiseaseCategory::create($data);
        $category->loadCount('diseases');

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_category_created_successfully.'),
            'data' => $category,
        ], 201);
    }

    public function update(Request $request, DiseaseCategory $diseaseCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('disease_categories', 'name')->ignore($diseaseCategory->id),
            ],
        ]);

        $diseaseCategory->update($data);
        $diseaseCategory->loadCount('diseases');

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_category_updated_successfully.'),
            'data' => $diseaseCategory,
        ]);
    }

    public function destroy(DiseaseCategory $diseaseCategory): JsonResponse
    {
        if ($diseaseCategory->diseases()->exists()) {
            return response()->json([
                'success' => false,
                'message' => localize('global.disease_category_has_diseases.'),
            ], 422);
        }

        $diseaseCategory->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.disease_category_deleted_successfully.'),
        ]);
    }
}
