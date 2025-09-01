<?php

namespace App\Http\Controllers;

use App\Models\PhysiotherapyType;
use Illuminate\Http\Request;

class PhysiotherapyTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PhysiotherapyType::with('createdBy');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $physiotherapyTypes = $query->paginate($perPage);

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
        $request->validate([
            'name' => 'required|string|max:255|unique:physiotherapy_types,name',
            'description' => 'nullable|string',
        ]);

        PhysiotherapyType::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('physiotherapy-types.index')
                        ->with('success', localize('global.physiotherapy_type_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PhysiotherapyType $physiotherapyType)
    {
        $physiotherapyType->load(['physiotherapyProcedures.appointment.patient', 'physiotherapyProcedures.physiotherapist']);
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
        $request->validate([
            'name' => 'required|string|max:255|unique:physiotherapy_types,name,' . $physiotherapyType->id,
            'description' => 'nullable|string',
        ]);

        $physiotherapyType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('physiotherapy-types.index')
                        ->with('success', localize('global.physiotherapy_type_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhysiotherapyType $physiotherapyType)
    {
        $physiotherapyType->delete();
        return redirect()->route('physiotherapy-types.index')
                        ->with('success', localize('global.physiotherapy_type_deleted_successfully'));
    }
}
