<?php

namespace App\Http\Controllers;

use App\Models\BloodUnitTest;
use App\Models\BloodUnit;
use App\Models\Department;
use App\Models\Patient;
use App\Services\BloodBankStockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodUnitController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $expiredArchivedCount = app(BloodBankStockService::class)->archiveExpiredUnits($branchId, auth()->id());

        $query = BloodUnit::where('branch_id', $branchId)
            ->with(['donation.donor.department', 'donation.donor.patient']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('blood_group')) {
            $query->where('blood_group', $request->blood_group);
        }

        if ($request->filled('rh')) {
            $query->where('rh', $request->rh);
        }

        if ($request->filled('component_type')) {
            $query->where('component_type', $request->component_type);
        }

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where('bag_number', 'like', '%'.$q.'%');
        }

        if ($request->filled('expires_within')) {
            $days = max(1, min(90, (int) $request->expires_within));
            $query->where('status', 'available')
                ->where('expires_at', '>', now())
                ->where('expires_at', '<=', now()->addDays($days));
        }

        $sort = $request->input('sort', 'created_at');
        if ($sort === 'expires_at') {
            $query->orderBy('expires_at');
        } else {
            $query->orderByDesc('created_at');
        }

        $units = $query->paginate(30)->withQueryString();

        $departments = Department::where('branch_id', $branchId)->orderBy('name')->get();
        $patientsForDonor = Patient::where('branch_id', $branchId)
            ->orderBy('name')
            ->orderBy('last_name')
            ->limit(500)
            ->get(['id', 'name', 'last_name', 'phone', 'nid']);

        return view('pages.blood_banks.inventory', compact('units', 'departments', 'patientsForDonor', 'expiredArchivedCount'));
    }

    public function show(BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $bloodUnit->load([
            'branch',
            'stockMovements.user',
            'test.testedBy',
            'tests.testedBy',
            'donation.donor.department',
            'donation.donor.patient',
            'donation.samples',
        ]);

        return view('pages.blood_banks.unit_show', compact('bloodUnit'));
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        app(\App\Services\BloodUnitReceiveService::class)->receive($request);

        return redirect()->back()->with('success', localize('global.blood_unit_received_success'));
    }

    public function saveTests(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('manage-blood-inventory') && ! $user->can('receive-blood-units')) {
            abort(403);
        }

        $validated = $request->validate([
            'abo_result' => ['nullable', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh_result' => ['nullable', 'string', Rule::in(['+', '-'])],
            'dct_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'ict_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hbs_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hcv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'hiv_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'vdrl_result' => ['required', Rule::in(BloodUnitTest::RESULT_VALUES)],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        app(\App\Services\BloodUnitManagementService::class)->saveTests($bloodUnit, $validated, (int) $request->user()->id);

        return redirect()
            ->back()
            ->with('success', localize('global.blood_unit_tests_saved'));
    }

    public function approveAfterTests(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('manage-blood-inventory') && ! $user->can('receive-blood-units')) {
            abort(403);
        }

        try {
            app(\App\Services\BloodUnitManagementService::class)->approveAfterTests($bloodUnit);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_unit_released_after_tests'));
    }

    public function discard(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate([
            'reason' => 'nullable|string|max:2000',
        ]);

        try {
            app(\App\Services\BloodUnitManagementService::class)->discard($bloodUnit, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('blood_banks.inventory')
            ->with('success', localize('global.blood_unit_discarded_success'));
    }

    public function quarantine(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(\App\Services\BloodUnitManagementService::class)->setQuarantine($bloodUnit, true, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_unit_quarantine_set'));
    }

    public function releaseQuarantine(Request $request, BloodUnit $bloodUnit)
    {
        $this->ensureBranchBloodUnit($bloodUnit);

        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }

        $request->validate(['reason' => 'nullable|string|max:2000']);

        try {
            app(\App\Services\BloodUnitManagementService::class)->setQuarantine($bloodUnit, false, $request->input('reason'));
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', localize('global.blood_unit_quarantine_released'));
    }

    protected function ensureBranchBloodUnit(BloodUnit $unit): void
    {
        if ((int) $unit->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
    }
}
