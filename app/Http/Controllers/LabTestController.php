<?php

namespace App\Http\Controllers;

use App\Models\LabTest;
use App\Models\LabType;
use App\Models\LabTypeSection;
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
        $query = LabTest::with(['labType', 'labTypeSection', 'parameters']);
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('labType', function($typeQuery) use ($request) {
                      $typeQuery->where('name', 'like', '%' . $request->search . '%');
                  })
                  ->orWhereHas('labTypeSection', function($sectionQuery) use ($request) {
                      $sectionQuery->where('section', 'like', '%' . $request->search . '%');
                  });
            });
        }
        
        // Lab type filter
        if ($request->has('lab_type_id') && !empty($request->lab_type_id)) {
            $query->where('lab_type_id', $request->lab_type_id);
        }
        
        // Lab type section filter
        if ($request->has('lab_type_section_id') && !empty($request->lab_type_section_id)) {
            $query->where('lab_type_section_id', $request->lab_type_section_id);
        }
        
        // Pagination
        $labTests = $query->orderBy('created_at', 'desc')->paginate(15);
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        
        // AJAX request - return partial view
        if ($request->ajax()) {
            return view('pages.lab_types._tests_list', compact('labTests', 'labTypes', 'labTypeSections'))->render();
        }
        
        return view('pages.lab_types.tests', compact('labTests', 'labTypes', 'labTypeSections'));
    }

    /**
     * Store a newly created lab test
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type_id' => 'required|exists:lab_types,id',
            'lab_type_section_id' => 'required|exists:lab_type_sections,id',
            'has_parameters' => 'required|boolean',
            'parameters' => 'required_if:has_parameters,true|array|min:1',
            'parameters.*.parameter_name' => 'required_if:has_parameters,true|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
        ]);

        try {
            \DB::beginTransaction();
            
            // Create the lab test
            $labTest = LabTest::create($request->only('name', 'lab_type_id', 'lab_type_section_id', 'has_parameters'));
            
            // Create parameters only if has_parameters is true
            if ($request->has_parameters && $request->parameters) {
                foreach ($request->parameters as $parameter) {
                    $labTest->parameters()->create([
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
                    'message' => 'Lab test created successfully.',
                    'labTest' => $labTest->load(['labType', 'labTypeSection', 'parameters'])
                ], 201);
            }
            
            return redirect()->route('lab_types.tests')->with('success', 'Lab test created successfully.');
            
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
        $labTest = LabTest::with(['labType', 'labTypeSection', 'parameters'])->findOrFail($id);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'labTest' => $labTest
            ]);
        }
        
        $labTypes = LabType::all();
        $labTypeSections = LabTypeSection::all();
        return view('pages.lab_types.tests', compact('labTest', 'labTypes', 'labTypeSections'));
    }

    /**
     * Update the specified lab test
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type_id' => 'required|exists:lab_types,id',
            'lab_type_section_id' => 'required|exists:lab_type_sections,id',
            'has_parameters' => 'required|boolean',
            'parameters' => 'required_if:has_parameters,true|array|min:1',
            'parameters.*.parameter_name' => 'required_if:has_parameters,true|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
            'deleted_parameter_ids' => 'nullable|array',
            'deleted_parameter_ids.*' => 'integer|exists:lab_test_parameters,id',
        ]);

        try {
            \DB::beginTransaction();
            
            $labTest = LabTest::findOrFail($id);
            $labTest->update($request->only('name', 'lab_type_id', 'lab_type_section_id', 'has_parameters'));
            
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
                    'labTest' => $labTest->fresh(['labType', 'labTypeSection', 'parameters'])
                ]);
            }
            
            return redirect()->route('lab_types.tests')->with('success', 'Lab test updated successfully.');
            
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

    // API Methods
    /**
     * API: Display a listing of lab tests for a specific lab type
     */
    public function apiIndex(Request $request, LabType $labType)
    {
        $query = $labType->labTests()->with(['labType', 'labTypeSection', 'parameters']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Test type filter
        if ($request->has('test_type') && !empty($request->test_type)) {
            if ($request->test_type === 'parametered') {
                $query->where('has_parameters', true);
            } elseif ($request->test_type === 'text_based') {
                $query->where('has_parameters', false);
            }
        }

        $labTests = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $labTests
        ]);
    }

    /**
     * API: Store a newly created lab test
     */
    public function apiStore(Request $request, LabType $labType)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type_section_id' => 'required|exists:lab_type_sections,id',
            'has_parameters' => 'required|boolean',
            'parameters' => 'required_if:has_parameters,true|array|min:1',
            'parameters.*.parameter_name' => 'required_if:has_parameters,true|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
        ]);

        try {
            \DB::beginTransaction();
            
            // Create the lab test
            $labTest = $labType->labTests()->create([
                'name' => $request->name,
                'lab_type_section_id' => $request->lab_type_section_id,
                'has_parameters' => $request->has_parameters,
            ]);
            
            // Create parameters only if has_parameters is true
            if ($request->has_parameters && $request->parameters) {
                foreach ($request->parameters as $parameter) {
                    $labTest->parameters()->create([
                        'parameter_name' => $parameter['parameter_name'],
                        'unit' => $parameter['unit'] ?? null,
                        'normal_range' => $parameter['normal_range'] ?? null,
                    ]);
                }
            }
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lab test created successfully',
                'data' => $labTest->load(['labType', 'labTypeSection', 'parameters'])
            ], 201);
            
        } catch (\Exception $e) {
            \DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating lab test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Display the specified lab test
     */
    public function apiShow(LabTest $labTest)
    {
        $labTest->load(['labType', 'labTypeSection', 'parameters']);

        return response()->json([
            'success' => true,
            'data' => $labTest
        ]);
    }

    /**
     * API: Update the specified lab test
     */
    public function apiUpdate(Request $request, LabTest $labTest)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'lab_type_id' => 'required|exists:lab_types,id',
            'lab_type_section_id' => 'required|exists:lab_type_sections,id',
            'has_parameters' => 'required|boolean',
            'parameters' => 'required_if:has_parameters,true|array|min:1',
            'parameters.*.parameter_name' => 'required_if:has_parameters,true|string|max:255',
            'parameters.*.unit' => 'nullable|string|max:50',
            'parameters.*.normal_range' => 'nullable|string|max:100',
            'deleted_parameter_ids' => 'nullable|array',
            'deleted_parameter_ids.*' => 'integer|exists:lab_test_parameters,id',
        ]);

        try {
            \DB::beginTransaction();
            
            $labTest->update($request->only('name', 'lab_type_id', 'lab_type_section_id', 'has_parameters'));
            
            // Delete marked parameters
            if ($request->has('deleted_parameter_ids')) {
                $labTest->parameters()->whereIn('id', $request->deleted_parameter_ids)->delete();
            }
            
            // Update or create parameters
            if ($request->has_parameters && $request->parameters) {
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
                            'parameter_name' => $parameter['parameter_name'],
                            'unit' => $parameter['unit'] ?? null,
                            'normal_range' => $parameter['normal_range'] ?? null,
                        ]);
                    }
                }
            }
            
            \DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Lab test updated successfully',
                'data' => $labTest->fresh(['labType', 'labTypeSection', 'parameters'])
            ]);
            
        } catch (\Exception $e) {
            \DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating lab test: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Remove the specified lab test
     */
    public function apiDestroy(LabTest $labTest)
    {
        $labTest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lab test deleted successfully'
        ]);
    }
}
