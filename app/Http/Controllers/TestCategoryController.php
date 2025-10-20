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
    public function index()
    {
        $testCategories = TestCategory::all();
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

        TestCategory::create($data);
        return redirect()->route('laboratory.categories.index')->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified test category
     */
    public function edit($id)
    {
        $editCategory = TestCategory::findOrFail($id);
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
        return redirect()->route('laboratory.categories.index')->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified test category
     */
    public function destroy($id)
    {
        $category = TestCategory::findOrFail($id);
        $category->delete();

        return redirect()->route('laboratory.categories.index')->with('success', 'Category deleted successfully.');
    }
}
