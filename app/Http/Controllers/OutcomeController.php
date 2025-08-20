<?php

namespace App\Http\Controllers;

use App\Models\Outcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OutcomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Outcome::with(['medicine', 'patient', 'doctor', 'createdBy']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('medicine', function ($medicineQuery) use ($search) {
                    $medicineQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('patient', function ($patientQuery) use ($search) {
                    $patientQuery->where('name', 'like', "%{$search}%");
                })
                ->orWhere('batch_number', 'like', "%{$search}%")
                ->orWhere('reason', 'like', "%{$search}%");
            });
        }

        // Filter by outcome type
        if ($request->filled('outcome_type')) {
            $query->where('outcome_type', $request->outcome_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('outcome_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('outcome_date', '<=', $request->date_to);
        }

        // Sort functionality
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $outcomes = $query->paginate($perPage);

        return view('pages.outcomes.index', compact('outcomes'));
    }
}
