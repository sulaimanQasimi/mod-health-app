<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\LabTest;
use App\Models\TestCategory;
use Illuminate\Http\Request;

/**
 * Lab Test Parameter Controller
 * 
 * Handles CRUD operations for lab test parameters
 */
class LabTestParameterController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:manage-test-parameters');
    }

    /**
     * Display a listing of lab test parameters
     */
    public function index()
    {
        $categories = TestCategory::all();
        $parameters = LabTestParameter::with(['labTest', 'testCategory'])->get();
        return view('pages.laboratory.parameters.index', compact('categories', 'parameters'));
    }

    /**
     * Get tests by category (AJAX)
     */
    public function getTestsByCategory($categoryId)
    {
        $tests = LabTest::where('category_id', $categoryId)->get();
        return response()->json($tests);
    }

    /**
     * Store a newly created lab test parameter
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'test_id' => 'required|exists:lab_tests,id',
            'parameter_name.*' => 'required|string|max:255',
            'unit.*' => 'nullable|string|max:50',
            'normal_range.*' => 'nullable|string|max:50',
        ]);

        foreach ($request->parameter_name as $i => $param) {
            LabTestParameter::updateOrCreate(
                [
                    'testcategory_id' => $request->category_id,
                    'test_id' => $request->test_id,
                    'parameter_name' => $param,
                ],
                [
                    'unit' => $request->unit[$i] ?? null,
                    'normal_range' => $request->normal_range[$i] ?? null,
                    'result' => 0,
                ]
            );
        }

        return redirect()->route('laboratory.parameters.index')->with('success', 'Parameters saved successfully.');
    }

    /**
     * Show the form for editing the specified parameter
     */
    public function edit($id)
    {
        $parameter = LabTestParameter::findOrFail($id);
        $categories = TestCategory::all();
        $tests = LabTest::where('category_id', $parameter->testcategory_id)->get();

        return view('pages.laboratory.parameters.edit', compact('parameter', 'categories', 'tests'));
    }

    /**
     * Update the specified parameter
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'test_id' => 'required|exists:lab_tests,id',
            'parameter_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'normal_range' => 'nullable|string|max:50',
            'result' => 'nullable|numeric',
        ]);

        $parameter = LabTestParameter::findOrFail($id);
        $parameter->update([
            'testcategory_id' => $request->category_id,
            'test_id' => $request->test_id,
            'parameter_name' => $request->parameter_name,
            'unit' => $request->unit,
            'normal_range' => $request->normal_range,
            'result' => $request->result ?? 0,
        ]);

        return redirect()->route('laboratory.parameters.index')->with('success', 'Parameter updated successfully.');
    }
}
