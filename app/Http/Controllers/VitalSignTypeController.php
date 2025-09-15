<?php

namespace App\Http\Controllers;

use App\Models\VitalSignType;
use App\Http\Requests\StoreVitalSignTypeRequest;
use App\Http\Requests\UpdateVitalSignTypeRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VitalSignTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', VitalSignType::class);
        
        $query = VitalSignType::with(['vitalSigns', 'createdBy']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vitalSignTypes = $query->orderBy('name')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSignTypes->items(),
                'meta' => [
                    'current_page' => $vitalSignTypes->currentPage(),
                    'last_page' => $vitalSignTypes->lastPage(),
                    'per_page' => $vitalSignTypes->perPage(),
                    'total' => $vitalSignTypes->total(),
                ]
            ]);
        }

        return view('pages.vital-sign-types.index', compact('vitalSignTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', VitalSignType::class);
        
        return view('pages.vital-sign-types.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVitalSignTypeRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', VitalSignType::class);

        $vitalSignType = VitalSignType::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign type created successfully.',
                'data' => $vitalSignType->load(['createdBy'])
            ], 201);
        }

        return redirect()->route('vital-sign-types.index')
            ->with('success', 'Vital sign type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, VitalSignType $vitalSignType): View|JsonResponse
    {
        $this->authorize('view', $vitalSignType);

        $vitalSignType->load(['vitalSigns.morphable', 'createdBy', 'updatedBy']);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSignType
            ]);
        }

        return view('pages.vital-sign-types.show', compact('vitalSignType'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VitalSignType $vitalSignType): View
    {
        $this->authorize('update', $vitalSignType);
        
        return view('pages.vital-sign-types.edit', compact('vitalSignType'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVitalSignTypeRequest $request, VitalSignType $vitalSignType): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $vitalSignType);

        $vitalSignType->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign type updated successfully.',
                'data' => $vitalSignType->load(['updatedBy'])
            ]);
        }

        return redirect()->route('vital-sign-types.index')
            ->with('success', 'Vital sign type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, VitalSignType $vitalSignType): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $vitalSignType);

        // Check if vital sign type has associated vital signs
        if ($vitalSignType->vitalSigns()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete vital sign type with associated vital signs.',
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Cannot delete vital sign type with associated vital signs.');
        }

        $vitalSignType->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign type deleted successfully.',
            ]);
        }

        return redirect()->route('vital-sign-types.index')
            ->with('success', 'Vital sign type deleted successfully.');
    }
}
