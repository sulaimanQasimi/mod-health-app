<?php

namespace App\Http\Controllers;

use App\Models\NurseNote;
use App\Models\Nurse;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use App\Http\Requests\StoreNurseNoteRequest;
use App\Http\Requests\UpdateNurseNoteRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class NurseNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', NurseNote::class);
        
        $query = NurseNote::with(['nurse', 'morphable', 'createdBy']);

        // Apply filters
        if ($request->filled('morphable_type')) {
            $query->forMorphableType($request->morphable_type);
        }

        if ($request->filled('morphable_id')) {
            $query->forMorphableId($request->morphable_id);
        }

        if ($request->filled('nurse_id')) {
            $query->byNurse($request->nurse_id);
        }

        if ($request->filled('date')) {
            $query->forDate($request->date);
        }

        if ($request->filled('shift')) {
            if ($request->shift === 'am') {
                $query->withAmTimes();
            } elseif ($request->shift === 'pm') {
                $query->withPmTimes();
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('time_am', 'like', "%{$search}%")
                  ->orWhere('time_pm', 'like', "%{$search}%")
                  ->orWhere('note', 'like', "%{$search}%")
                  ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                      $nurseQuery->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $nurseNotes = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get nurses for filter dropdown
        $nurses = Nurse::active()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $nurseNotes,
                'nurses' => $nurses
            ]);
        }

        return view('pages.nurse-notes.index', compact('nurseNotes', 'nurses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', NurseNote::class);
        
        $user = auth()->user();
        $nurse = $user->nurse;
        
        // Check if user has a nurse profile
        if (!$nurse) {
            return redirect()->back()->with('error', 'You must have a nurse profile to create nurse notes.');
        }
        
        $nurses = Nurse::active()->get();
        $morphableType = $request->get('morphable_type');
        $morphableId = $request->get('morphable_id');

        return view('pages.nurse-notes.create', compact('nurses', 'morphableType', 'morphableId', 'nurse'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNurseNoteRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', NurseNote::class);
        
        $user = auth()->user();
        $nurse = $user->nurse;
        
        // Check if user has a nurse profile
        if (!$nurse) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must have a nurse profile to create nurse notes.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You must have a nurse profile to create nurse notes.');
        }
        
        try {
            $data = $request->validated();
            $data['nurse_id'] = $nurse->id; // Automatically set nurse ID from authenticated user
            
            $nurseNote = NurseNote::create($data);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nurse note created successfully.',
                    'data' => $nurseNote->load(['nurse', 'morphable', 'createdBy'])
                ], 201);
            }

            // Redirect to the morphable show page if morphable_type and morphable_id are provided
            if ($request->filled('morphable_type') && $request->filled('morphable_id')) {
                $morphableType = $request->morphable_type;
                $morphableId = $request->morphable_id;
                
                if ($morphableType === 'App\\Models\\UnderReview') {
                    return redirect()->route('under-review.show', $morphableId)
                                   ->with('success', 'Nurse note created successfully.');
                } elseif ($morphableType === 'App\\Models\\Hospitalization') {
                    return redirect()->route('hospitalizations.show', $morphableId)
                                   ->with('success', 'Nurse note created successfully.');
                }
            }

            return redirect()->route('nurse-notes.index')
                           ->with('success', 'Nurse note created successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create nurse note.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create nurse note.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(NurseNote $nurseNote): View|JsonResponse
    {
        $this->authorize('view', $nurseNote);
        
        $nurseNote->load(['nurse', 'morphable', 'createdBy', 'updatedBy']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $nurseNote
            ]);
        }

        return view('pages.nurse-notes.show', compact('nurseNote'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NurseNote $nurseNote): View
    {
        $this->authorize('update', $nurseNote);
        
        $nurses = Nurse::active()->get();
        
        return view('pages.nurse-notes.edit', compact('nurseNote', 'nurses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNurseNoteRequest $request, NurseNote $nurseNote): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $nurseNote);
        
        try {
            $nurseNote->update($request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nurse note updated successfully.',
                    'data' => $nurseNote->load(['nurse', 'morphable', 'updatedBy'])
                ]);
            }

            return redirect()->route('nurse-notes.index')
                           ->with('success', 'Nurse note updated successfully.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update nurse note.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update nurse note.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NurseNote $nurseNote): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $nurseNote);
        
        try {
            $nurseNote->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Nurse note deleted successfully.'
                ]);
            }

            return redirect()->route('nurse-notes.index')
                           ->with('success', 'Nurse note deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete nurse note.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Failed to delete nurse note.');
        }
    }

    /**
     * Get nurse notes for a specific morphable record.
     */
    public function getNotesForRecord(Request $request): JsonResponse
    {
        $request->validate([
            'morphable_type' => 'required|string|in:App\\Models\\UnderReview,App\\Models\\Hospitalization',
            'morphable_id' => 'required|integer|min:1'
        ]);

        $notes = NurseNote::with(['nurse', 'createdBy'])
                         ->forMorphableType($request->morphable_type)
                         ->forMorphableId($request->morphable_id)
                         ->orderBy('date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    /**
     * Get nurse notes by date range.
     */
    public function getNotesByDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'morphable_type' => 'nullable|string|in:App\\Models\\UnderReview,App\\Models\\Hospitalization',
            'morphable_id' => 'nullable|integer|min:1'
        ]);

        $query = NurseNote::with(['nurse', 'morphable', 'createdBy'])
                         ->whereBetween('date', [$request->start_date, $request->end_date]);

        if ($request->filled('morphable_type')) {
            $query->forMorphableType($request->morphable_type);
        }

        if ($request->filled('morphable_id')) {
            $query->forMorphableId($request->morphable_id);
        }

        $notes = $query->orderBy('date', 'desc')
                      ->orderBy('created_at', 'desc')
                      ->get();

        return response()->json([
            'success' => true,
            'data' => $notes
        ]);
    }

    /**
     * Show the print page for nurse notes
     */
    public function print(Request $request)
    {
        $this->authorize('viewAny', NurseNote::class);
        
        $nurseNotes = collect();
        $patient = null;
        $morphableType = null;
        $morphableId = null;

        // If specific under_review or hospitalization is requested
        if ($request->filled('morphable_type') && $request->filled('morphable_id')) {
            $morphableType = $request->morphable_type;
            $morphableId = $request->morphable_id;

            // Load nurse notes for the specific record
            $nurseNotes = NurseNote::with(['nurse', 'createdBy'])
                ->where('morphable_type', $morphableType)
                ->where('morphable_id', $morphableId)
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get patient information based on morphable type
            if ($morphableType === 'App\\Models\\UnderReview') {
                $underReview = \App\Models\UnderReview::with(['patient'])->find($morphableId);
                if ($underReview && $underReview->patient) {
                    $patient = $underReview->patient;
                }
            } elseif ($morphableType === 'App\\Models\\Hospitalization') {
                $hospitalization = \App\Models\Hospitalization::with(['patient'])->find($morphableId);
                if ($hospitalization && $hospitalization->patient) {
                    $patient = $hospitalization->patient;
                }
            }
        } else {
            // Load all nurse notes if no specific record is requested
            $nurseNotes = NurseNote::with(['nurse', 'createdBy', 'morphable'])
                ->orderBy('date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(25)
                ->get();
        }

        return view('pages.nurse-notes.print', compact('nurseNotes', 'patient', 'morphableType', 'morphableId'));
    }
    public function section(Request $request)
    {
        $this->authorize('viewAny', NurseNote::class);
        $morphableType = $request->morphable_type;
        $morphableId = $request->morphable_id;
        $morphModel = null;
        $nurseNotes = NurseNote::with(['nurse', 'createdBy', 'morphable'])
            ->where('morphable_type', $morphableType)
            ->where('morphable_id', $morphableId)
            ->get();
        $morphModel = $morphableType::find($morphableId);
        return view('pages.nurse-notes.partials.section', compact('nurseNotes', 'morphableType', 'morphableId', 'morphModel'));
    }
}
