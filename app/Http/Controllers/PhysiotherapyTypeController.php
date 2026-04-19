<?php

namespace App\Http\Controllers;

use App\Models\PhysiotherapyType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PhysiotherapyTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $physiotherapyTypes = PhysiotherapyType::with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('pages.physiotherapy.types.index', compact('physiotherapyTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pages.physiotherapy.types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:physiotherapy_types,name',
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => localize('global.name_required'),
            'name.unique' => localize('global.name_already_exists'),
            'description.max' => localize('global.description_max_length'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $physiotherapyType = PhysiotherapyType::create([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('physiotherapy-types.index')
                ->with('success', localize('global.physiotherapy_type_created_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error creating physiotherapy type: ' . $e->getMessage());
            return back()->withErrors(['error' => localize('global.error_creating_physiotherapy_type')])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PhysiotherapyType $physiotherapyType)
    {
        $physiotherapyType->load(['createdBy', 'updatedBy', 'physiotherapyProcedures.doctor']);
        return view('pages.physiotherapy.types.show', compact('physiotherapyType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhysiotherapyType $physiotherapyType)
    {
        return view('pages.physiotherapy.types.edit', compact('physiotherapyType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhysiotherapyType $physiotherapyType)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:physiotherapy_types,name,' . $physiotherapyType->id,
            'description' => 'nullable|string|max:1000',
        ], [
            'name.required' => localize('global.name_required'),
            'name.unique' => localize('global.name_already_exists'),
            'description.max' => localize('global.description_max_length'),
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $physiotherapyType->update([
                'name' => $request->name,
                'description' => $request->description,
            ]);

            return redirect()->route('physiotherapy-types.index')
                ->with('success', localize('global.physiotherapy_type_updated_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error updating physiotherapy type: ' . $e->getMessage());
            return back()->withErrors(['error' => localize('global.error_updating_physiotherapy_type')])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhysiotherapyType $physiotherapyType)
    {
        try {
            // Check if physiotherapy type has related procedures
            if ($physiotherapyType->physiotherapyProcedures()->count() > 0) {
                return back()->withErrors(['error' => localize('global.cannot_delete_physiotherapy_type_with_procedures')]);
            }

            $physiotherapyType->delete();

            return redirect()->route('physiotherapy-types.index')
                ->with('success', localize('global.physiotherapy_type_deleted_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error deleting physiotherapy type: ' . $e->getMessage());
            return back()->withErrors(['error' => localize('global.error_deleting_physiotherapy_type')]);
        }
    }

    /**
     * Toggle the status of the physiotherapy type
     */
    public function toggleStatus(PhysiotherapyType $physiotherapyType)
    {
        try {
            $physiotherapyType->update([
                'status' => $physiotherapyType->status === 'active' ? 'inactive' : 'active'
            ]);

            $status = $physiotherapyType->status === 'active' ? 'activated' : 'deactivated';
            return back()->with('success', localize('global.physiotherapy_type_status_updated_successfully'));
        } catch (\Exception $e) {
            \Log::error('Error toggling physiotherapy type status: ' . $e->getMessage());
            return back()->withErrors(['error' => localize('global.error_updating_physiotherapy_type_status')]);
        }
    }
}
