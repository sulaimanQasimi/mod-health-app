<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LabType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LabTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LabType::with(['category', 'directLabTestParameters']);
        
        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        
        // Category filter
        if ($request->has('category_id') && !empty($request->category_id)) {
            $query->where('category_id', $request->category_id);
        }
        
        // Pagination
        $perPage = $request->get('per_page', 15);
        $labTypes = $query->orderBy('name')->paginate($perPage);
        $categories = Category::all();
        
        return view('pages.lab_types.index', compact('labTypes', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $labTypes = LabType::all();
        $categories = Category::all();
        return view('pages.lab_types.create',compact('labTypes','categories'));
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

        LabType::create($data);

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_created_successfully.'));
    }

   /**
     * Display the specified resource.
     */
    public function show(LabType $labType)
    {
        return view('pages.lab_types.show', compact('labType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LabType $labType)
    {
        $labTypes = LabType::all();
        return view('pages.lab_types.edit', compact('labType', 'labTypes'));
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

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_updated_successfully.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LabType $labType)
    {
        $labType->delete();

        return redirect()->route('lab_types.index')->with('success', localize('global.lab_type_deleted_successfully.'));
    }

    // API Methods
    /**
     * API: Display a listing of lab types
     */
    public function apiIndex(Request $request)
    {
        try {
        $query = LabType::with(['category', 'directLabTestParameters']);

        // Search functionality
        if ($request->has('search') && !empty($request->search)) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

            $labTypes = $query->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => $labTypes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error loading lab types: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * API: Store a newly created lab type
     */
    public function apiStore(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $labType = LabType::create($data);

        return response()->json([
            'success' => true,
            'message' => localize('global.lab_type_created_successfully'),
            'data' => $labType->load(['category'])
        ], 201);
    }

    /**
     * API: Display the specified lab type
     */
    public function apiShow($id)
    {
        try {
            // First try to find the lab type without relationships
            $labType = LabType::find($id);
            
            if (!$labType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lab type not found',
                    'data' => null
                ], 404);
            }
            
            // Load relationships
            $labType->load(['category', 'directLabTestParameters']);

            return response()->json([
                'success' => true,
                'data' => $labType
            ]);
        } catch (\Exception $e) {
            Log::error('Error in apiShow: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading lab type: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * API: Update the specified lab type
     */
    public function apiUpdate(Request $request, $id)
    {
        try {
            $labType = LabType::findOrFail($id);
            
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'parameters' => 'nullable|string', // JSON string from frontend
                'deleted_parameter_ids' => 'nullable|string', // JSON string from frontend
            ]);

            DB::beginTransaction();
            
            // Update lab type basic information
            $labType->update([
                'name' => $data['name'],
            ]);

            // Handle parameters if provided
            if ($request->has('parameters') && !empty($request->parameters)) {
                $parameters = json_decode($request->parameters, true);
                
                if (is_array($parameters)) {
                    foreach ($parameters as $parameterData) {
                        if (isset($parameterData['id']) && !empty($parameterData['id'])) {
                            // Update existing parameter
                            $parameter = \App\Models\LabTestParameter::find($parameterData['id']);
                            if ($parameter) {
                                $parameter->update([
                                    'parameter_name' => $parameterData['parameter_name'],
                                    'unit' => $parameterData['unit'] ?? null,
                                    'normal_range' => $parameterData['normal_range'] ?? null,
                                ]);
                            }
                        } else {
                            // Create new parameter
                            \App\Models\LabTestParameter::create([
                                'test_id' => null, // Set to null since we're creating for lab type directly
                                'lab_type_id' => $labType->id,
                                'parameter_name' => $parameterData['parameter_name'],
                                'unit' => $parameterData['unit'] ?? null,
                                'normal_range' => $parameterData['normal_range'] ?? null,
                            ]);
                        }
                    }
                }
            }

            // Handle deleted parameters
            if ($request->has('deleted_parameter_ids') && !empty($request->deleted_parameter_ids)) {
                $deletedIds = json_decode($request->deleted_parameter_ids, true);
                
                if (is_array($deletedIds)) {
                    \App\Models\LabTestParameter::whereIn('id', $deletedIds)->delete();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => localize('global.lab_type_updated_successfully'),
                'data' => $labType->load(['directLabTestParameters'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error updating lab type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Remove the specified lab type
     */
    public function apiDestroy($id)
    {
        try {
            $labType = LabType::findOrFail($id);
            $labType->delete();

            return response()->json([
                'success' => true,
                'message' => localize('global.lab_type_deleted_successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting lab type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lab types for select dropdown
     */
    public function getLabTypesForSelect()
    {
        $labTypes = LabType::select('id', 'name')->orderBy('name')->get();
        return response()->json($labTypes);
    }

}
