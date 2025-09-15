<?php

namespace App\Http\Controllers;

use App\Models\VitalSignSchedule;
use App\Models\VitalSign;
use App\Models\Nurse;
use App\Http\Requests\StoreVitalSignScheduleRequest;
use App\Http\Requests\UpdateVitalSignScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class VitalSignScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', VitalSignSchedule::class);
        
        $query = VitalSignSchedule::with(['vitalSign.vitalSignType', 'vitalSign.morphable', 'nurse', 'createdBy']);

        // Apply filters
        if ($request->filled('vital_sign_id')) {
            $query->where('vital_sign_id', $request->vital_sign_id);
        }

        if ($request->filled('nurse_id')) {
            $query->where('nurse_id', $request->nurse_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('day')) {
            $query->where('day', 'like', '%' . $request->day . '%');
        }

        $schedules = $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->paginate(15);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $schedules->items(),
                'meta' => [
                    'current_page' => $schedules->currentPage(),
                    'last_page' => $schedules->lastPage(),
                    'per_page' => $schedules->perPage(),
                    'total' => $schedules->total(),
                ]
            ]);
        }

        $vitalSigns = VitalSign::with(['vitalSignType', 'morphable'])->get();
        $nurses = Nurse::orderBy('first_name')->get();

        return view('pages.vital-sign-schedules.index', compact('schedules', 'vitalSigns', 'nurses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', VitalSignSchedule::class);
        
        $vitalSigns = VitalSign::with(['vitalSignType', 'morphable'])->get();
        $nurses = Nurse::orderBy('first_name')->get();
        $vitalSignId = $request->get('vital_sign_id');
        
        // Get the authenticated user's nurse profile
        $currentUserNurse = auth()->user()->nurse;

        return view('pages.vital-sign-schedules.create', compact('vitalSigns', 'nurses', 'vitalSignId', 'currentUserNurse'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVitalSignScheduleRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', VitalSignSchedule::class);

        $data = $request->validated();
        
        // Automatically assign the authenticated user's nurse profile if available
        if (auth()->user()->nurse && !isset($data['nurse_id'])) {
            $data['nurse_id'] = auth()->user()->nurse->id;
        }

        // Auto-generate the next day number for this vital sign
        $vitalSignId = $data['vital_sign_id'];
        $existingDays = VitalSignSchedule::where('vital_sign_id', $vitalSignId)
            ->whereNotNull('day')
            ->pluck('day')
            ->toArray();
        
        $nextDayNumber = 1;
        while (in_array("Day " . $nextDayNumber, $existingDays)) {
            $nextDayNumber++;
        }
        
        $data['day'] = "Day " . $nextDayNumber;

        $schedule = VitalSignSchedule::create($data);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'message' => 'Vital sign schedule created successfully.',
                'data' => $schedule->load(['vitalSign.vitalSignType', 'nurse', 'createdBy'])
            ], 201);
        }

        // Redirect to the vital sign show page
        $vitalSign = $schedule->vitalSign;
        return redirect()->route('vital-signs.show', $vitalSign)
            ->with('success', 'Vital sign schedule created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, VitalSignSchedule $vitalSignSchedule): View|JsonResponse
    {
        $this->authorize('view', $vitalSignSchedule);

        $vitalSignSchedule->load([
            'vitalSign.vitalSignType', 
            'vitalSign.morphable', 
            'nurse', 
            'createdBy', 
            'updatedBy'
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $vitalSignSchedule
            ]);
        }

        return view('pages.vital-sign-schedules.show', compact('vitalSignSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(VitalSignSchedule $vitalSignSchedule): View
    {
        $this->authorize('update', $vitalSignSchedule);
        
        $vitalSigns = VitalSign::with(['vitalSignType', 'morphable'])->get();
        $nurses = Nurse::orderBy('first_name')->get();
        
        // Get the authenticated user's nurse profile
        $currentUserNurse = auth()->user()->nurse;

        return view('pages.vital-sign-schedules.edit', compact('vitalSignSchedule', 'vitalSigns', 'nurses', 'currentUserNurse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVitalSignScheduleRequest $request, VitalSignSchedule $vitalSignSchedule): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $vitalSignSchedule);

        $data = $request->validated();
        
        // Automatically assign the authenticated user's nurse profile if available
        if (auth()->user()->nurse) {
            $data['nurse_id'] = auth()->user()->nurse->id;
        }

        $vitalSignSchedule->update($data);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign schedule updated successfully.',
                'data' => $vitalSignSchedule->load(['vitalSign.vitalSignType', 'nurse', 'updatedBy'])
            ]);
        }

        // Redirect to the vital sign show page
        $vitalSign = $vitalSignSchedule->vitalSign;
        return redirect()->route('vital-signs.show', $vitalSign)
            ->with('success', 'Vital sign schedule updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, VitalSignSchedule $vitalSignSchedule): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $vitalSignSchedule);

        $vitalSignSchedule->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Vital sign schedule deleted successfully.',
            ]);
        }

        // Redirect to the vital sign show page
        $vitalSign = $vitalSignSchedule->vitalSign;
        return redirect()->route('vital-signs.show', $vitalSign)
            ->with('success', 'Vital sign schedule deleted successfully.');
    }
}
