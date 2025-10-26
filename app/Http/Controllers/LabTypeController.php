<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LabType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * LabTypeController
 * 
 * Handles both web and API requests for lab types management.
 * 
 * API Endpoints:
 * GET    /api/lab-types              - List all lab types
 * POST   /api/lab-types              - Create new lab type
 * GET    /api/lab-types/{id}         - Show specific lab type
 * PUT    /api/lab-types/{id}         - Update lab type
 * DELETE /api/lab-types/{id}         - Delete lab type
 * GET    /api/lab-types/select/dropdown - Get lab types for select dropdown
 * GET    /api/categories             - Get categories for select dropdown
 */
class LabTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Build query with only necessary relationships
        $query = LabType::with(['category']);
        
        // Optimized search - search in name and category name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhereHas('category', function($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Category filter
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Order by name for consistent results
        $query->orderBy('name');
        
        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            $labTypes = $query->get();
            return response()->json([
                'success' => true,
                'data' => $labTypes
            ]);
        }
        
        // Paginate with optimized page size for web requests
        $labTypes = $query->paginate(20);
        
        // Cache categories for better performance
        $categories = Cache::remember('lab_types_categories', 300, function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
        
        return view('pages.lab_types.index', compact('labTypes', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Only load necessary data for the create form
        $categories = Cache::remember('lab_types_categories', 300, function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
        return view('pages.lab_types.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:lab_types,name',
            'category_id' => 'required|exists:categories,id',
        ]);

        $labType = LabType::create($data);
        $labType->load(['category']);

        // Clear categories cache when new lab type is created
        Cache::forget('lab_types_categories');

        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => localize('global.lab_type_created_successfully'),
                'data' => $labType
            ], 201);
        }

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_created_successfully.'));
    }

    /**
     * Display the specified resource (API).
     */
    public function show(LabType $labType)
    {
        // Load relationships for API response
        $labType->load(['category', 'directLabTestParameters']);
        
        return response()->json([
            'success' => true,
            'data' => $labType
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabType $labType)
    {
        // Load categories for the edit form
        $categories = Cache::remember('lab_types_categories', 300, function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
        return view('pages.lab_types.edit', compact('labType', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LabType $labType)
    {
        $data = $request->validate([
            'name' => 'required|unique:lab_types,name,' . $labType->id,
            'category_id' => 'required|exists:categories,id',
        ]);

        $labType->update($data);
        $labType->load(['category']);

        // Clear categories cache when lab type is updated
        Cache::forget('lab_types_categories');

        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => localize('global.lab_type_updated_successfully'),
                'data' => $labType
            ]);
        }

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, LabType $labType)
    {
        $labType->delete();

        // Clear categories cache when lab type is deleted
        Cache::forget('lab_types_categories');

        // Check if this is an API request
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => true,
                'message' => localize('global.lab_type_deleted_successfully')
            ]);
        }

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_deleted_successfully.'));
    }

    /**
     * Get lab types for select dropdown (API)
     */
    public function getLabTypesForSelect()
    {
        $labTypes = Cache::remember('lab_types_select', 300, function () {
            return LabType::select('id', 'name')->orderBy('name')->get();
        });
        
        return response()->json($labTypes);
    }

}
