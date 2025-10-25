<?php

namespace App\Http\Controllers;

use App\Models\LabTestParameter;
use App\Models\LabTest;
use App\Models\LabType;
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
        $parameters = LabTestParameter::with(['directLabType'])->get();
        return view('pages.laboratory.parameters.index', compact('parameters'));
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
        $labTypes = LabType::all();

        return view('pages.laboratory.parameters.edit', compact('parameter', 'labTypes'));
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

    // API Methods
    /**
     * API: Display a listing of lab test parameters for a specific lab test
     */
    public function apiIndex(Request $request, LabTest $labTest)
    {
        $query = $labTest->parameters();

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('parameter_name', 'like', '%' . $request->search . '%');
        }

        $parameters = $query->orderBy('parameter_name')->get();

        return response()->json([
            'success' => true,
            'data' => $parameters
        ]);
    }

    /**
     * API: Store a newly created lab test parameter
     */
    public function apiStore(Request $request, LabTest $labTest)
    {
        $request->validate([
            'parameter_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'normal_range' => 'nullable|string|max:100',
            'critical_low' => 'nullable|string|max:50',
            'critical_high' => 'nullable|string|max:50',
            'panic_low' => 'nullable|string|max:50',
            'panic_high' => 'nullable|string|max:50',
            'delta_check_enabled' => 'nullable|boolean',
            'delta_check_threshold' => 'nullable|string|max:50',
            'critical_comment' => 'nullable|string',
            'panic_comment' => 'nullable|string',
            'requires_verification' => 'nullable|boolean',
            'verification_level' => 'nullable|string|max:50',
        ]);

        $data = $request->all();
        $data['lab_type_id'] = $labTest->lab_type_id; // Add direct relationship
        $parameter = $labTest->parameters()->create($data);

        return response()->json([
            'success' => true,
            'message' => localize('global.lab_test_parameter_created_successfully'),
            'data' => $parameter
        ], 201);
    }

    /**
     * API: Display the specified lab test parameter
     */
    public function apiShow(LabTestParameter $parameter)
    {
        $parameter->load(['labTest.labType']);

        return response()->json([
            'success' => true,
            'data' => $parameter
        ]);
    }

    /**
     * API: Update the specified lab test parameter
     */
    public function apiUpdate(Request $request, LabTestParameter $parameter)
    {
        $request->validate([
            'parameter_name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:50',
            'normal_range' => 'nullable|string|max:100',
            'critical_low' => 'nullable|string|max:50',
            'critical_high' => 'nullable|string|max:50',
            'panic_low' => 'nullable|string|max:50',
            'panic_high' => 'nullable|string|max:50',
            'delta_check_enabled' => 'nullable|boolean',
            'delta_check_threshold' => 'nullable|string|max:50',
            'critical_comment' => 'nullable|string',
            'panic_comment' => 'nullable|string',
            'requires_verification' => 'nullable|boolean',
            'verification_level' => 'nullable|string|max:50',
        ]);

        $parameter->update($request->all());

        return response()->json([
            'success' => true,
            'message' => localize('global.lab_test_parameter_updated_successfully'),
            'data' => $parameter->fresh()
        ]);
    }

    /**
     * API: Remove the specified lab test parameter
     */
    public function apiDestroy(LabTestParameter $parameter)
    {
        $parameter->delete();

        return response()->json([
            'success' => true,
            'message' => localize('global.lab_test_parameter_deleted_successfully')
        ]);
    }

    /**
     * API: Display parameters for a specific lab type
     */
    public function apiIndexByLabType(Request $request, $id)
    {
        try {
            $labType = LabType::findOrFail($id);
            $query = $labType->directLabTestParameters();

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $query->where('parameter_name', 'like', '%' . $request->search . '%');
            }

            $parameters = $query->orderBy('parameter_name')->get();

            return response()->json([
                'success' => true,
                'data' => $parameters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading parameters: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * API: Store a parameter directly for a lab type
     */
    public function apiStoreByLabType(Request $request, $id)
    {
        try {
            $labType = LabType::findOrFail($id);
            
            $request->validate([
                'parameter_name' => 'required|string|max:255',
                'unit' => 'nullable|string|max:50',
                'normal_range' => 'nullable|string|max:100',
                'critical_low' => 'nullable|string|max:50',
                'critical_high' => 'nullable|string|max:50',
                'panic_low' => 'nullable|string|max:50',
                'panic_high' => 'nullable|string|max:50',
                'delta_check_enabled' => 'nullable|boolean',
                'delta_check_threshold' => 'nullable|string|max:50',
                'critical_comment' => 'nullable|string',
                'panic_comment' => 'nullable|string',
                'requires_verification' => 'nullable|boolean',
                'verification_level' => 'nullable|string|max:50',
            ]);

            $data = $request->all();
            $data['lab_type_id'] = $labType->id;
            $parameter = LabTestParameter::create($data);

            return response()->json([
                'success' => true,
                'message' => localize('global.lab_test_parameter_created_successfully'),
                'data' => $parameter
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating parameter: ' . $e->getMessage()
            ], 500);
        }
    }
}
