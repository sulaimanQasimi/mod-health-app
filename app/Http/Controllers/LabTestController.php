<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Models\TestCategory;
use Illuminate\Http\Request;

/**
 * Lab Test Controller
 * 
 * Handles CRUD operations for lab tests
 */
class LabTestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-lab-tests');
    }

    /**
     * Display a listing of lab tests
     */
    public function index()
    {
        $labTests = LabTest::with('category')->get();
        $categories = TestCategory::all();

        return view('pages.laboratory.tests.index', compact('labTests', 'categories'));
    }

    /**
     * Store a newly created lab test
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'name' => 'required|string|max:255',
        ]);

        LabTest::create($request->only('category_id', 'name'));

        return redirect()->route('laboratory.tests.index')->with('success', 'Lab test created successfully.');
    }

    /**
     * Update the specified lab test
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'name' => 'required|string|max:255',
        ]);

        $labTest = LabTest::findOrFail($id);
        $labTest->update($request->only('category_id', 'name'));

        return redirect()->route('laboratory.tests.index')->with('success', 'Lab test updated successfully.');
    }

    /**
     * Remove the specified lab test
     */
    public function destroy($id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->delete();

        return redirect()->route('laboratory.tests.index')->with('success', 'Lab test deleted successfully.');
    }
}
