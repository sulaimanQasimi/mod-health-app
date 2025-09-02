<?php

namespace App\Http\Controllers;

use App\Models\PhysiotherapyProcedure;
use App\Models\PhysiotherapyProcedureReview;
use App\Models\PhysiotherapyType;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\Request;

class PhysiotherapyProcedureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PhysiotherapyProcedure::with([
            'appointment.patient',
            'physiotherapyType',
            'physiotherapist',
            'reviews'
        ]);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('appointment.patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('appointment_id')) {
            $query->where('appointment_id', $request->appointment_id);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Physiotherapy type filter
        if ($request->filled('physiotherapy_type_id')) {
            $query->where('physiotherapy_type_id', $request->physiotherapy_type_id);
        }

        // Physiotherapist filter
        if ($request->filled('physiotherapist_id')) {
            $query->where('physiotherapist_id', $request->physiotherapist_id);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->ajax() || $request->wantsJson()) {
            $physiotherapyProcedures = $query->get();
            $data = $physiotherapyProcedures->map(function ($p) {
                $percentage = $p->days_count > 0 ? ($p->counter / max(1, $p->days_count)) * 100 : 0;
                return [
                    'id' => $p->id,
                    'patient_name' => $p->appointment->patient->name ?? 'N/A',
                    'physiotherapy_type' => $p->physiotherapyType->name ?? 'N/A',
                    'physiotherapist' => $p->physiotherapist->name ?? 'N/A',
                    'type' => $p->type,
                    'duration' => $p->duration,
                    'progress_counter' => $p->counter,
                    'progress_total' => $p->days_count,
                    'progress_percentage' => round($percentage, 1),
                    'status' => $p->status,
                    'start_date' => optional($p->start_date)->format('Y-m-d'),
                    'reviews_count' => $p->reviews->count(),
                    'actions' => '', // Placeholder for DataTables rendering
                ];
            });
            return response()->json(['data' => $data]);
        }

        // For non-AJAX requests, get paginated results
        $physiotherapyProcedures = $query->paginate(15);
        $physiotherapyTypes = PhysiotherapyType::all();
        $physiotherapists = User::whereHas('roles', function($q) {
            $q->where('name', 'physiotherapist');
        })->get();

        return view('pages.physiotherapy.procedures.index', compact('physiotherapyProcedures', 'physiotherapyTypes', 'physiotherapists'));
    }

    /**
     * Display a listing of the user's own physiotherapy procedures.
     */
    public function myProcedures(Request $request)
    {
        $query = PhysiotherapyProcedure::with([
            'appointment.patient',
            'physiotherapyType',
            'physiotherapist',
            'reviews'
        ])->where('physiotherapist_id', auth()->id());

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('appointment.patient', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Physiotherapy type filter
        if ($request->filled('physiotherapy_type_id')) {
            $query->where('physiotherapy_type_id', $request->physiotherapy_type_id);
        }

        // Date range filter
        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('start_date', '<=', $request->end_date);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        if ($request->ajax() || $request->wantsJson()) {
            $physiotherapyProcedures = $query->get();
            $data = $physiotherapyProcedures->map(function ($p) {
                $percentage = $p->days_count > 0 ? ($p->counter / max(1, $p->days_count)) * 100 : 0;
                return [
                    'id' => $p->id,
                    'patient_name' => $p->appointment->patient->name ?? 'N/A',
                    'physiotherapy_type' => $p->physiotherapyType->name ?? 'N/A',
                    'type' => $p->type,
                    'duration' => $p->duration,
                    'progress_counter' => $p->counter,
                    'progress_total' => $p->days_count,
                    'progress_percentage' => round($percentage, 1),
                    'status' => $p->status,
                    'start_date' => optional($p->start_date)->format('Y-m-d'),
                    'reviews_count' => $p->reviews->count(),
                    'actions' => '', // Placeholder for DataTables rendering
                ];
            });
            return response()->json(['data' => $data]);
        }

        // For non-AJAX requests, get paginated results
        $physiotherapyProcedures = $query->paginate(15);
        $physiotherapyTypes = PhysiotherapyType::all();

        return view('pages.physiotherapy.procedures.my-procedures', compact('physiotherapyProcedures', 'physiotherapyTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $physiotherapyTypes = PhysiotherapyType::all();
        $physiotherapists = User::whereHas('roles', function ($query) {
            $query->where('name', 'physiotherapist');
        })->get();
        $appointments = Appointment::with('patient')->where('is_completed', false)->get();

        return view('pages.physiotherapy.procedures.create', compact('physiotherapyTypes', 'physiotherapists', 'appointments'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'physiotherapy_type_id' => 'required|exists:physiotherapy_types,id',
            'physiotherapist_id' => 'required|exists:users,id',
            'type' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'days_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $procedure = PhysiotherapyProcedure::create([
            'appointment_id' => $request->appointment_id,
            'physiotherapy_type_id' => $request->physiotherapy_type_id,
            'physiotherapist_id' => $request->physiotherapist_id,
            'type' => $request->type,
            'duration' => $request->duration,
            'counter' => 0,
            'days_count' => $request->days_count,
            'description' => $request->description,
            'notes' => $request->notes,
            'status' => 'pending',
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('physiotherapy-procedures.index')
            ->with('success', localize('global.physiotherapy_procedure_created_successfully'));
    }

    /**
     * Display the specified resource.
     */
    public function show(PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $physiotherapyProcedure->load([
            'appointment.patient',
            'physiotherapyType',
            'physiotherapist',
            'createdBy',
            'updatedBy'
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $physiotherapyProcedure->id,
                    'appointment_id' => $physiotherapyProcedure->appointment_id,
                    'physiotherapy_type_id' => $physiotherapyProcedure->physiotherapy_type_id,
                    'physiotherapist_id' => $physiotherapyProcedure->physiotherapist_id,
                    'type' => $physiotherapyProcedure->type,
                    'duration' => $physiotherapyProcedure->duration,
                    'days_count' => $physiotherapyProcedure->days_count,
                    'counter' => $physiotherapyProcedure->counter,
                    'description' => $physiotherapyProcedure->description,
                    'notes' => $physiotherapyProcedure->notes,
                    'status' => $physiotherapyProcedure->status,
                    'start_date' => optional($physiotherapyProcedure->start_date)->format('Y-m-d'),
                    'end_date' => optional($physiotherapyProcedure->end_date)->format('Y-m-d'),
                    'physiotherapy_type_name' => $physiotherapyProcedure->physiotherapyType->name ?? 'N/A',
                    'physiotherapist_name' => $physiotherapyProcedure->physiotherapist->name ?? 'N/A',
                    'created_by_name' => $physiotherapyProcedure->createdBy->name ?? 'N/A',
                    'created_at' => $physiotherapyProcedure->created_at->format('Y-m-d H:i'),
                ]
            ]);
        }

        return view('pages.physiotherapy.procedures.show', compact('physiotherapyProcedure'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $physiotherapyTypes = PhysiotherapyType::all();
        $physiotherapists = User::whereHas('roles', function ($query) {
            $query->where('name', 'physiotherapist');
        })->get();
        $appointments = Appointment::with('patient')->get();

        return view('pages.physiotherapy.procedures.edit', compact('physiotherapyProcedure', 'physiotherapyTypes', 'physiotherapists', 'appointments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'physiotherapy_type_id' => 'required|exists:physiotherapy_types,id',
            'physiotherapist_id' => 'required|exists:users,id',
            'type' => 'required|string|max:255',
            'duration' => 'required|integer|min:1',
            'days_count' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $physiotherapyProcedure->update([
            'appointment_id' => $request->appointment_id,
            'physiotherapy_type_id' => $request->physiotherapy_type_id,
            'physiotherapist_id' => $request->physiotherapist_id,
            'type' => $request->type,
            'duration' => $request->duration,
            'days_count' => $request->days_count,
            'description' => $request->description,
            'notes' => $request->notes,
            'status' => $request->status,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('physiotherapy-procedures.index')
            ->with('success', localize('global.physiotherapy_procedure_updated_successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $appointmentId = $physiotherapyProcedure->appointment_id;
        $physiotherapyProcedure->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('physiotherapy-procedures.index')
            ->with('success', localize('global.physiotherapy_procedure_deleted_successfully'));
    }

    /**
     * Update the counter for a physiotherapy procedure
     */
    public function updateCounter(Request $request, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $request->validate([
            'counter' => 'required|integer|min:0|max:' . $physiotherapyProcedure->days_count,
        ]);

        $physiotherapyProcedure->update([
            'counter' => $request->counter,
            'status' => $request->counter >= $physiotherapyProcedure->days_count ? 'completed' : 'in_progress',
        ]);

        return redirect()->back()
            ->with('success', localize('global.physiotherapy_procedure_counter_updated_successfully'));
    }

    /**
     * Get physiotherapy procedures by appointment
     */
    public function getByAppointment(Appointment $appointment)
    {
        $physiotherapyProcedures = $appointment->physiotherapyProcedures()
            ->with(['physiotherapyType', 'physiotherapist'])
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->wantsJson() || request()->ajax()) {
            $data = $physiotherapyProcedures->map(function ($p) {
                $percentage = $p->days_count > 0 ? ($p->counter / max(1, $p->days_count)) * 100 : 0;
                return [
                    'id' => $p->id,
                    'physiotherapy_type' => $p->physiotherapyType->name ?? 'N/A',
                    'physiotherapist' => $p->physiotherapist->name ?? 'N/A',
                    'type' => $p->type,
                    'duration' => $p->duration,
                    'progress_counter' => $p->counter,
                    'progress_total' => $p->days_count,
                    'progress_percentage' => round($percentage, 1),
                    'status' => $p->status,
                    'start_date' => optional($p->start_date)->format('Y-m-d'),
                ];
            });
            return response()->json(['success' => true, 'data' => $data]);
        }

        return view('pages.physiotherapy.procedures.by_appointment', compact('appointment', 'physiotherapyProcedures'));
    }

    /**
     * Get reviews for a specific physiotherapy procedure
     */
    public function getReviews(PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $reviews = $physiotherapyProcedure->reviews()
            ->with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->wantsJson() || request()->ajax()) {
            $data = $reviews->map(function ($review) {
                return [
                    'id' => $review->id,
                    'description' => $review->description,
                    'status' => $review->status,
                    'days_count' => $review->days_count,
                    'created_by_name' => $review->createdBy->name ?? 'N/A',
                    'updated_by_name' => $review->updatedBy->name ?? 'N/A',
                    'created_at' => $review->created_at->format('Y-m-d H:i'),
                    'updated_at' => $review->updated_at->format('Y-m-d H:i'),
                ];
            });
            return response()->json(['success' => true, 'data' => $data]);
        }
        return response()->json(['success' => true, 'data' => $reviews]);
    }

    /**
     * Store a new review for a physiotherapy procedure
     */
    public function storeReview(Request $request, PhysiotherapyProcedure $physiotherapyProcedure)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'days_count' => 'nullable|integer|min:0',
        ]);

        $review = $physiotherapyProcedure->reviews()->create([
            'description' => $request->description,
            'status' => $request->status,
            'days_count' => $request->days_count ?? 0,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Review created successfully',
                'data' => [
                    'id' => $review->id,
                    'description' => $review->description,
                    'status' => $review->status,
                    'created_by_name' => $review->createdBy->name ?? 'N/A',
                    'created_at' => $review->created_at->format('Y-m-d H:i'),
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Review created successfully');
    }

    /**
     * Update an existing review
     */
    public function updateReview(Request $request, PhysiotherapyProcedureReview $review)
    {
        $request->validate([
            'description' => 'required|string|max:1000',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'days_count' => 'nullable|integer|min:0',
        ]);

        $review->update([
            'description' => $request->description,
            'status' => $request->status,
            'days_count' => $request->days_count ?? 0,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully',
                'data' => [
                    'id' => $review->id,
                    'description' => $review->description,
                    'status' => $review->status,
                    'updated_by_name' => $review->updatedBy->name ?? 'N/A',
                    'updated_at' => $review->updated_at->format('Y-m-d H:i'),
                ]
            ]);
        }

        return redirect()->back()->with('success', 'Review updated successfully');
    }

    /**
     * Delete a review
     */
    public function destroyReview(PhysiotherapyProcedureReview $review)
    {
        $review->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully'
            ]);
        }

        return redirect()->back()->with('success', 'Review deleted successfully');
    }
}
