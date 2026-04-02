<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\ProstheticCase;
use App\Models\ProstheticReferral;
use App\Services\ProstheticsNumberService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProstheticReferralController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $query = ProstheticReferral::query()
            ->with('patient')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('referral_date');

        // General search (kept for backward compatibility)
        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where(function ($w) use ($q) {
                $w->where('referral_number', 'like', '%' . $q . '%')
                    ->orWhereHas('patient', function ($p) use ($q) {
                        $p->where('name', 'like', '%' . $q . '%')
                            ->orWhere('phone', 'like', '%' . $q . '%')
                            ->orWhere('nid', 'like', '%' . $q . '%')
                            ->orWhere('id_card', 'like', '%' . $q . '%');
                    });
            });
        }

        // Full filters
        if ($request->filled('referral_number')) {
            $query->where('referral_number', 'like', '%' . trim((string) $request->referral_number) . '%');
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', (int) $request->patient_id);
        }

        if ($request->filled('patient_name')) {
            $query->whereHas('patient', fn ($p) => $p->where('name', 'like', '%' . trim((string) $request->patient_name) . '%'));
        }

        if ($request->filled('phone')) {
            $query->whereHas('patient', fn ($p) => $p->where('phone', 'like', '%' . trim((string) $request->phone) . '%'));
        }

        if ($request->filled('nid')) {
            $query->whereHas('patient', fn ($p) => $p->where('nid', 'like', '%' . trim((string) $request->nid) . '%'));
        }

        if ($request->filled('id_card')) {
            $query->whereHas('patient', fn ($p) => $p->where('id_card', 'like', '%' . trim((string) $request->id_card) . '%'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('urgency')) {
            $query->where('urgency', $request->urgency);
        }

        if ($request->filled('requested_service_type')) {
            $query->where('requested_service_type', 'like', '%' . trim((string) $request->requested_service_type) . '%');
        }

        if ($request->filled('from')) {
            $query->whereDate('referral_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('referral_date', '<=', $request->to);
        }

        $referrals = $query->paginate(25)->withQueryString();

        return view('pages.prosthetics.referrals.index', compact('referrals'));
    }

    public function create()
    {
        return view('pages.prosthetics.referrals.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'referral_date' => 'required|date',
            'referring_facility' => 'nullable|string|max:255',
            'referring_doctor' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'diagnosis_summary' => 'nullable|string',
            'urgency' => 'nullable|string|max:64',
            'requested_service_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $referral = new ProstheticReferral($data);
        $referral->referral_number = ProstheticsNumberService::nextReferralNumber();
        $referral->branch_id = auth()->user()->branch_id;
        $referral->status = 'submitted';
        $referral->created_by = Auth::id();
        $referral->updated_by = Auth::id();
        $referral->save();

        return redirect()->route('prosthetics.referrals.show', $referral)->with('success', __('global.success'));
    }

    public function show(ProstheticReferral $referral)
    {
        $referral->load(['patient', 'convertedCase']);

        return view('pages.prosthetics.referrals.show', compact('referral'));
    }

    public function edit(ProstheticReferral $referral)
    {
        return view('pages.prosthetics.referrals.edit', compact('referral'));
    }

    public function update(Request $request, ProstheticReferral $referral)
    {
        $data = $request->validate([
            'referral_date' => 'required|date',
            'referring_facility' => 'nullable|string|max:255',
            'referring_doctor' => 'nullable|string|max:255',
            'reason' => 'nullable|string',
            'diagnosis_summary' => 'nullable|string',
            'urgency' => 'nullable|string|max:64',
            'requested_service_type' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:64',
        ]);

        $referral->fill($data);
        $referral->updated_by = Auth::id();
        $referral->save();

        return redirect()->route('prosthetics.referrals.show', $referral)->with('success', __('global.success'));
    }

    public function accept(ProstheticReferral $referral)
    {
        $referral->status = 'accepted';
        $referral->updated_by = Auth::id();
        $referral->save();

        return back()->with('success', __('global.success'));
    }

    public function reject(Request $request, ProstheticReferral $referral)
    {
        $request->validate(['notes' => 'nullable|string']);
        $referral->status = 'rejected';
        if ($request->filled('notes')) {
            $referral->notes = ($referral->notes ? $referral->notes."\n\n" : '').$request->notes;
        }
        $referral->updated_by = Auth::id();
        $referral->save();

        return back()->with('success', __('global.success'));
    }

    public function convertToCase(Request $request, ProstheticReferral $referral)
    {
        if ($referral->converted_case_id) {
            return redirect()->route('prosthetics.cases.show', $referral->converted_case_id);
        }

        $case = DB::transaction(function () use ($referral, $request) {
            $case = new ProstheticCase([
                'patient_id' => $referral->patient_id,
                'referral_id' => $referral->id,
                'branch_id' => $referral->branch_id ?? auth()->user()->branch_id,
                'case_number' => ProstheticsNumberService::nextCaseNumber(),
                'status' => ProstheticCase::STATUS_REFERRED,
                'primary_diagnosis' => $referral->diagnosis_summary,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $case->save();

            $referral->converted_case_id = $case->id;
            $referral->status = 'converted_to_case';
            $referral->updated_by = Auth::id();
            $referral->save();

            return $case;
        });

        return redirect()->route('prosthetics.cases.show', $case)->with('success', __('global.success'));
    }

    public function searchPatients(Request $request)
    {
        $q = $request->get('q', '');
        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $patients = Patient::query()
            ->where(function ($w) use ($q) {
                $w->where('name', 'like', '%'.$q.'%')
                    ->orWhere('phone', 'like', '%'.$q.'%')
                    ->orWhere('nid', 'like', '%'.$q.'%')
                    ->orWhere('id', $q);
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'father_name', 'phone', 'nid']);

        return response()->json($patients);
    }
}
