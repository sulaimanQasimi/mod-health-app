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
    public function index(Request $request)
    {
        $query = LabTest::with(['category', 'parameters']);
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('category', function($catQuery) use ($request) {
                      $catQuery->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        // Category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Pagination
        $labTests = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = TestCategory::all();
        
        // AJAX request - return partial view
        if ($request->ajax()) {
            return view('pages.laboratory.tests._tests_list', compact('labTests', 'categories'))->render();
        }
        
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
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
        ]);

        try {
            \DB::beginTransaction();
            
            // Create the lab test
            $labTest = LabTest::create($request->only('category_id', 'name'));
            
            // Create parameters
            foreach ($request->parameters as $parameter) {
                $labTest->parameters()->create([
                    'testcategory_id' => $request->category_id,
                    'parameter_name' => $parameter['parameter_name'],
                    'unit' => $parameter['unit'] ?? null,
                    'normal_range' => $parameter['normal_range'] ?? null,
                ]);
            }
            
            \DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lab test created successfully.',
                    'labTest' => $labTest->load(['category', 'parameters'])
                ], 201);
            }
            
            return redirect()->route('laboratory.tests.index')->with('success', 'Lab test created successfully.');
            
        } catch (\Exception $e) {
            \DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error creating lab test: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error creating lab test.');
        }
    }

    /**
     * Show the form for editing the specified lab test
     */
    public function edit(Request $request, $id)
    {
        $labTest = LabTest::with(['category', 'parameters'])->findOrFail($id);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'labTest' => $labTest
            ]);
        }
        
        $categories = TestCategory::all();
        return view('pages.laboratory.tests.index', compact('labTest', 'categories'));
    }

    /**
     * Update the specified lab test
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id' => 'required|exists:test_categories,id',
            'name' => 'required|string|max:255',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
            'deleted_parameter_ids' => 'nullable|array',
            'deleted_parameter_ids.*' => 'integer|exists:lab_test_parameters,id',
        ]);

        try {
            \DB::beginTransaction();
            
            $labTest = LabTest::findOrFail($id);
            $labTest->update($request->only('category_id', 'name'));
            
            // Delete marked parameters
            if ($request->has('deleted_parameter_ids')) {
                $labTest->parameters()->whereIn('id', $request->deleted_parameter_ids)->delete();
            }
            
            // Update or create parameters
            foreach ($request->parameters as $parameter) {
                if (isset($parameter['id'])) {
                    // Update existing parameter
                    $labTest->parameters()->where('id', $parameter['id'])->update([
                        'parameter_name' => $parameter['parameter_name'],
                        'unit' => $parameter['unit'] ?? null,
                        'normal_range' => $parameter['normal_range'] ?? null,
                    ]);
                } else {
                    // Create new parameter
                    $labTest->parameters()->create([
                        'testcategory_id' => $request->category_id,
                        'parameter_name' => $parameter['parameter_name'],
                        'unit' => $parameter['unit'] ?? null,
                        'normal_range' => $parameter['normal_range'] ?? null,
                    ]);
                }
            }
            
            \DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lab test updated successfully.',
                    'labTest' => $labTest->fresh(['category', 'parameters'])
                ]);
            }
            
            return redirect()->route('laboratory.tests.index')->with('success', 'Lab test updated successfully.');
            
        } catch (\Exception $e) {
            \DB::rollback();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating lab test: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()->with('error', 'Error updating lab test.');
        }
    }

    /**
     * Remove the specified lab test
     */
    public function destroy(Request $request, $id)
    {
        $labTest = LabTest::findOrFail($id);
        $labTest->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Lab test deleted successfully.'
            ]);
        }

        return redirect()->route('laboratory.tests.index')->with('success', 'Lab test deleted successfully.');
    }
}
