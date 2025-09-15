<?php

namespace App\Http\Controllers;

use App\Models\VitalSign;
use App\Models\VitalSignType;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use App\Http\Requests\StoreVitalSignRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VitalSignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', VitalSign::class);
        
        $query = VitalSign::with(['vitalSignType', 'morphable', 'schedules.nurse', 'createdBy']);

        // Apply filters
        if ($request->filled('morphable_type')) {
            $query->where('morphable_type', $request->morphable_type);
        }

        if ($request->filled('morphable_id')) {
            $query->where('morphable_id', $request->morphable_id);
        }

        if ($request->filled('vital_sign_type_id')) {
            $query->where('vital_sign_type_id', $request->vital_sign_type_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $vitalSigns = $query->orderBy('created_at', 'desc')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSigns->items(),
                'meta' => [
                    'current_page' => $vitalSigns->currentPage(),
                    'last_page' => $vitalSigns->lastPage(),
                    'per_page' => $vitalSigns->perPage(),
                    'total' => $vitalSigns->total(),
                ]
            ]);
        }

        $vitalSignTypes = VitalSignType::orderBy('name')->get();

        return view('pages.vital-signs.index', compact('vitalSigns', 'vitalSignTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', VitalSign::class);
        
        $vitalSignTypes = VitalSignType::orderBy('name')->get();
        $morphableType = $request->get('morphable_type');
        $morphableId = $request->get('morphable_id');

        return view('pages.vital-signs.create', compact('vitalSignTypes', 'morphableType', 'morphableId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVitalSignRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', VitalSign::class);

        $vitalSign = VitalSign::create($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign created successfully.',
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'createdBy'])
            ], 201);
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, VitalSign $vitalSign): View|JsonResponse
    {
        $this->authorize('view', $vitalSign);

        $vitalSign->load([
            'vitalSignType', 
            'morphable', 
            'schedules.nurse', 
            'createdBy', 
            'updatedBy'
        ]);

        // Load nurses for the modal
        $nurses = \App\Models\Nurse::orderBy('first_name')->get();
        $currentUserNurse = auth()->user()->nurse;

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSign
            ]);
        }

        return view('pages.vital-signs.show', compact('vitalSign', 'nurses', 'currentUserNurse'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VitalSign $vitalSign): View
    {
        $this->authorize('update', $vitalSign);
        
        $vitalSignTypes = VitalSignType::orderBy('name')->get();

        return view('pages.vital-signs.edit', compact('vitalSign', 'vitalSignTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreVitalSignRequest $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $vitalSign);

        $vitalSign->update($request->validated());

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign updated successfully.',
                'data' => $vitalSign->load(['vitalSignType', 'morphable', 'updatedBy'])
            ]);
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, VitalSign $vitalSign): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $vitalSign);

        // Check if vital sign has associated schedules
        if ($vitalSign->schedules()->count() > 0) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cannot delete vital sign with associated schedules.',
                ], 422);
            }
            
            return redirect()->back()
                ->with('error', 'Cannot delete vital sign with associated schedules.');
        }

        $vitalSign->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign deleted successfully.',
            ]);
        }

        return redirect()->route('vital-signs.index')
            ->with('success', 'Vital sign deleted successfully.');
    }
}
