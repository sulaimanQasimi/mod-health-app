<?php

namespace App\Http\Controllers;

use App\Models\TestCategory;
use Illuminate\Http\Request;

/**
 * Test Category Controller
 * 
 * Handles CRUD operations for test categories
 */
class TestCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-test-categories');
    }

    /**
     * Display a listing of test categories
     */
    public function index(Request $request)
    {
        $query = TestCategory::query();
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Pagination
        $testCategories = $query->orderBy('created_at', 'desc')->paginate(12);
        
        // AJAX request - return partial view
        if ($request->ajax()) {
            return view('pages.laboratory.categories._categories_list', compact('testCategories'))->render();
        }
        
        return view('pages.laboratory.categories.index', compact('testCategories'));
    }

    /**
     * Store a newly created test category
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        $category = TestCategory::create($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category
            ], 201);
        }
        
        return redirect()->route('laboratory.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified test category
     */
    public function edit(Request $request, $id)
    {
        $category = TestCategory::findOrFail($id);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        }
        
        $testCategories = TestCategory::all();
        return view('pages.laboratory.categories.index', compact('testCategories', 'editCategory'));
    }

    /**
     * Update the specified test category
     */
    public function update(Request $request, $id)
    {
        $category = TestCategory::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255'
        ]);
        $category->update($data);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'category' => $category->fresh()
            ]);
        }
        
        return redirect()->route('laboratory.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified test category
     */
    public function destroy(Request $request, $id)
    {
        $category = TestCategory::findOrFail($id);
        $category->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.'
            ]);
        }

        return redirect()->route('laboratory.categories.index')->with('success', 'Category deleted successfully.');
    }
}
