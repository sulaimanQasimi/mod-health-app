<?php

namespace App\Http\Controllers;

use App\Models\MedicationAdministrationRecord;
use App\Models\MedicationAdministrationTime;
use App\Models\Medicine;
use App\Models\Nurse;
use App\Models\UnderReview;
use App\Models\Hospitalization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class MedicationAdministrationRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', MedicationAdministrationRecord::class);
        
        $query = MedicationAdministrationRecord::with(['medicine', 'nurse', 'morphable', 'administrationTimes', 'createdBy']);

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

        if ($request->filled('medicine_id')) {
            $query->forMedicine($request->medicine_id);
        }

        if ($request->filled('order_date')) {
            $query->whereDate('order_date', $request->order_date);
        }

        if ($request->filled('date_signature')) {
            $query->whereDate('date_signature', $request->date_signature);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('nurse', function ($nurseQuery) use ($search) {
                    $nurseQuery->where('first_name', 'like', "%{$search}%")
                              ->orWhere('last_name', 'like', "%{$search}%");
                })
                ->orWhereHas('morphable', function ($morphableQuery) use ($search) {
                    $morphableQuery->whereHas('patient', function ($patientQuery) use ($search) {
                        $patientQuery->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%");
                    });
                });
            });
        }

        $medicationAdministrationRecords = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get related data for filters
        $medicines = Medicine::all();
        $nurses = Nurse::active()->get();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $medicationAdministrationRecords,
                'medicines' => $medicines,
                'nurses' => $nurses
            ]);
        }

        return view('pages.medication-administration-records.index', compact('medicationAdministrationRecords', 'medicines', 'nurses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View|RedirectResponse
    {
        $this->authorize('create', MedicationAdministrationRecord::class);
        
        $user = auth()->user();
        $nurse = $user->nurse;
        
        // Check if user has a nurse profile
        if (!$nurse) {
            return redirect()->back()->with('error', 'You must have a nurse profile to create medication administration records.');
        }
        
        $morphableType = $request->get('morphable_type');
        $morphableId = $request->get('morphable_id');
        
        // Validate morphable context - user must come from a valid morphable record
        if (!$morphableType || !$morphableId) {
            return redirect()->back()->with('error', 'You must access this page from a patient record (Under Review or Hospitalization) to create medication administration records.');
        }
        
        // Validate morphable type
        if (!in_array($morphableType, ['App\\Models\\UnderReview', 'App\\Models\\Hospitalization'])) {
            return redirect()->back()->with('error', 'Invalid record type. You can only create MARs from Under Review or Hospitalization records.');
        }
        
        // Validate that the morphable record exists
        $morphableClass = $morphableType;
        $morphableRecord = $morphableClass::find($morphableId);
        
        if (!$morphableRecord) {
            return redirect()->back()->with('error', 'The referenced patient record was not found.');
        }
        
        $medicines = Medicine::all();

        return view('pages.medication-administration-records.create', compact('medicines', 'nurse', 'morphableType', 'morphableId', 'morphableRecord'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', MedicationAdministrationRecord::class);
        
        $user = auth()->user();
        $nurse = $user->nurse;
        
        // Check if user has a nurse profile
        if (!$nurse) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must have a nurse profile to create medication administration records.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You must have a nurse profile to create medication administration records.');
        }
        
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'order_date' => 'nullable|date',
            'morphable_id' => 'required|integer|min:1',
            'morphable_type' => 'required|string|in:App\\Models\\UnderReview,App\\Models\\Hospitalization',
            'administration_times' => 'nullable|array',
            'administration_times.*' => 'nullable|date_format:H:i'
        ]);
        
        // Validate that the morphable record exists
        $morphableClass = $request->morphable_type;
        $morphableRecord = $morphableClass::find($request->morphable_id);
        
        if (!$morphableRecord) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'The referenced patient record was not found.'
                ], 404);
            }
            return redirect()->back()->with('error', 'The referenced patient record was not found.');
        }
        
        try {
            DB::beginTransaction();
            
            $data = $request->only(['medicine_id', 'order_date', 'morphable_id', 'morphable_type']);
            $data['nurse_id'] = $nurse->id; // Automatically set nurse ID from authenticated user
            $data['date_signature'] = now()->toDateString(); // Automatically set signature date to current date
            
            $medicationAdministrationRecord = MedicationAdministrationRecord::create($data);

            // Create administration times if provided
            if ($request->has('administration_times')) {
                foreach ($request->administration_times as $time) {
                    if (!empty($time)) {
                        MedicationAdministrationTime::create([
                            'medication_administration_record_id' => $medicationAdministrationRecord->id,
                            'time' => $time
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medication Administration Record created successfully.',
                    'data' => $medicationAdministrationRecord->load(['medicine', 'nurse', 'morphable', 'administrationTimes', 'createdBy'])
                ], 201);
            }

            return redirect()->route('medication-administration-records.index')
                           ->with('success', 'Medication Administration Record created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create Medication Administration Record.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to create Medication Administration Record.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicationAdministrationRecord $medicationAdministrationRecord): View|JsonResponse
    {
        $this->authorize('view', $medicationAdministrationRecord);
        
        $medicationAdministrationRecord->load(['medicine', 'nurse', 'morphable', 'administrationTimes', 'createdBy', 'updatedBy']);

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $medicationAdministrationRecord
            ]);
        }

        return view('pages.medication-administration-records.show', compact('medicationAdministrationRecord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicationAdministrationRecord $medicationAdministrationRecord): View
    {
        $this->authorize('update', $medicationAdministrationRecord);
        
        $medicines = Medicine::all();
        $medicationAdministrationRecord->load('administrationTimes');
        
        return view('pages.medication-administration-records.edit', compact('medicationAdministrationRecord', 'medicines'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicationAdministrationRecord $medicationAdministrationRecord): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $medicationAdministrationRecord);
        
        $user = auth()->user();
        $nurse = $user->nurse;
        
        // Check if user has a nurse profile
        if (!$nurse) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must have a nurse profile to update medication administration records.'
                ], 403);
            }
            return redirect()->back()->with('error', 'You must have a nurse profile to update medication administration records.');
        }
        
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'order_date' => 'nullable|date',
            'administration_times' => 'nullable|array',
            'administration_times.*' => 'nullable|date_format:H:i'
        ]);
        
        try {
            DB::beginTransaction();
            
            $data = $request->only(['medicine_id', 'order_date']);
            $data['nurse_id'] = $nurse->id; // Automatically set nurse ID from authenticated user
            $data['date_signature'] = now()->toDateString(); // Automatically set signature date to current date
            $medicationAdministrationRecord->update($data);

            // Update administration times if provided
            if ($request->has('administration_times')) {
                // Delete existing times
                $medicationAdministrationRecord->administrationTimes()->delete();
                
                // Create new times
                foreach ($request->administration_times as $time) {
                    if (!empty($time)) {
                        MedicationAdministrationTime::create([
                            'medication_administration_record_id' => $medicationAdministrationRecord->id,
                            'time' => $time
                        ]);
                    }
                }
            }

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medication Administration Record updated successfully.',
                    'data' => $medicationAdministrationRecord->load(['medicine', 'nurse', 'morphable', 'administrationTimes', 'updatedBy'])
                ]);
            }

            return redirect()->route('medication-administration-records.index')
                           ->with('success', 'Medication Administration Record updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update Medication Administration Record.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to update Medication Administration Record.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicationAdministrationRecord $medicationAdministrationRecord): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $medicationAdministrationRecord);
        
        try {
            $medicationAdministrationRecord->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Medication Administration Record deleted successfully.'
                ]);
            }

            return redirect()->route('medication-administration-records.index')
                           ->with('success', 'Medication Administration Record deleted successfully.');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete Medication Administration Record.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Failed to delete Medication Administration Record.');
        }
    }

    /**
     * Get MARs for a specific morphable record.
     */
    public function getRecordsForMorphable(Request $request): JsonResponse
    {
        $request->validate([
            'morphable_type' => 'required|string|in:App\\Models\\UnderReview,App\\Models\\Hospitalization',
            'morphable_id' => 'required|integer|min:1'
        ]);

        $records = MedicationAdministrationRecord::with(['medicine', 'nurse', 'administrationTimes', 'createdBy'])
                         ->forMorphableType($request->morphable_type)
                         ->forMorphableId($request->morphable_id)
                         ->orderBy('order_date', 'desc')
                         ->orderBy('created_at', 'desc')
                         ->get();

        return response()->json([
            'success' => true,
            'data' => $records
        ]);
    }

    /**
     * Add administration time to a MAR.
     */
    public function addAdministrationTime(Request $request, MedicationAdministrationRecord $medicationAdministrationRecord): JsonResponse
    {
        $this->authorize('update', $medicationAdministrationRecord);
        
        $request->validate([
            'time' => 'required|date_format:H:i'
        ]);

        try {
            $administrationTime = MedicationAdministrationTime::create([
                'medication_administration_record_id' => $medicationAdministrationRecord->id,
                'time' => $request->time
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Administration time added successfully.',
                'data' => $administrationTime
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add administration time.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove administration time from a MAR.
     */
    public function removeAdministrationTime(MedicationAdministrationTime $administrationTime): JsonResponse
    {
        $this->authorize('update', $administrationTime->medicationAdministrationRecord);
        
        try {
            $administrationTime->delete();

            return response()->json([
                'success' => true,
                'message' => 'Administration time removed successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove administration time.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the print page for MARs
     */
    public function print(Request $request)
    {
        $this->authorize('viewAny', MedicationAdministrationRecord::class);
        
        $medicationAdministrationRecords = collect();
        $patient = null;
        $morphableRecord = null;
        $morphableType = null;
        $morphableId = null;

        // If specific under_review or hospitalization is requested
        if ($request->filled('morphable_type') && $request->filled('morphable_id')) {
            $morphableType = $request->morphable_type;
            $morphableId = $request->morphable_id;

            // Load MARs for the specific record
            $medicationAdministrationRecords = MedicationAdministrationRecord::with(['medicine', 'nurse', 'administrationTimes', 'createdBy'])
                ->where('morphable_type', $morphableType)
                ->where('morphable_id', $morphableId)
                ->orderBy('order_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get();

            // Get patient and record information based on morphable type
            if ($morphableType === 'App\\Models\\UnderReview') {
                $morphableRecord = UnderReview::with(['patient', 'room', 'bed'])->find($morphableId);
                if ($morphableRecord && $morphableRecord->patient) {
                    $patient = $morphableRecord->patient;
                }
            } elseif ($morphableType === 'App\\Models\\Hospitalization') {
                $morphableRecord = Hospitalization::with(['patient', 'room', 'bed'])->find($morphableId);
                if ($morphableRecord && $morphableRecord->patient) {
                    $patient = $morphableRecord->patient;
                }
            }
        } else {
            // Load all MARs if no specific record is requested
            $medicationAdministrationRecords = MedicationAdministrationRecord::with(['medicine', 'nurse', 'administrationTimes', 'morphable', 'createdBy'])
                ->orderBy('order_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(25)
                ->get();
        }

        return view('pages.medication-administration-records.print', compact('medicationAdministrationRecords', 'patient', 'morphableRecord', 'morphableType', 'morphableId'));
    }
}
