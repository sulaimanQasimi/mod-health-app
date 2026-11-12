<?php

namespace App\Http\Controllers;

use App\Models\BloodBank;
use App\Models\BloodBankTest;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodTestController extends Controller
{
    public function new(Request $request)
    {
        return $this->indexByStatus($request, 'new');
    }

    public function approved(Request $request)
    {
        return $this->indexByStatus($request, 'approved');
    }

    public function rejected(Request $request)
    {
        return $this->indexByStatus($request, 'rejected');
    }

    public function delivered(Request $request)
    {
        return $this->indexByStatus($request, 'delivered');
    }

    public function show(BloodBank $bloodBank)
    {
        $this->ensureBloodTestBranch($bloodBank);

        $bloodBank->load(['patient', 'department', 'createdBy', 'tests.filledTestBy']);

        $departments = Department::where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view('pages.blood_banks.blood_tests.show', compact('bloodBank', 'departments'));
    }

    public function approve(BloodBank $bloodBank)
    {
        $this->ensureBloodTestBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.blood_test_invalid_status'));
        }

        $bloodBank->approve();

        return redirect()
            ->route('blood_banks.blood_tests.show', $bloodBank)
            ->with('success', localize('global.blood_test_approved_successfully'));
    }

    public function reject(Request $request, BloodBank $bloodBank)
    {
        $this->ensureBloodTestBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.blood_test_invalid_status'));
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:2000'],
        ]);

        $bloodBank->reject();
        $bloodBank->update(['reject_reason' => $validated['reject_reason']]);

        return redirect()
            ->route('blood_banks.blood_tests.show', $bloodBank)
            ->with('success', localize('global.blood_test_rejected_successfully'));
    }

    public function fillTest(Request $request, BloodBank $bloodBank, BloodBankTest $bloodBankTest)
    {
        $this->ensureBloodTestBranch($bloodBank);
        $this->ensureCanRecordTests($request);

        if ((int) $bloodBankTest->blood_bank_id !== (int) $bloodBank->id) {
            abort(404);
        }

        if ($bloodBank->status !== 'approved') {
            return redirect()
                ->route('blood_banks.blood_tests.show', $bloodBank)
                ->with('error', localize('global.blood_bank_tests_only_when_approved'));
        }

        $validated = $request->validate([
            'result' => ['required', Rule::in(BloodBankTest::RESULT_VALUES)],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $bloodBankTest->update([
            'result' => $validated['result'],
            'notes' => $validated['notes'] ?? null,
            'filled_test_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('blood_banks.blood_tests.show', $bloodBank)
            ->with('success', localize('global.blood_bank_test_saved'));
    }

    public function complete(Request $request, BloodBank $bloodBank)
    {
        $this->ensureBloodTestBranch($bloodBank);
        $this->ensureCanRecordTests($request);

        if ($bloodBank->status !== 'approved') {
            return redirect()->back()->with('error', localize('global.blood_test_must_be_approved_to_complete'));
        }

        $bloodBank->loadMissing('tests');

        if ($bloodBank->tests->isEmpty() || $bloodBank->tests->contains(fn ($t) => $t->result === null)) {
            return redirect()->back()->with('error', localize('global.blood_test_all_results_required'));
        }

        $bloodBank->deliver();

        return redirect()
            ->route('blood_banks.blood_tests.show', $bloodBank)
            ->with('success', localize('global.blood_test_completed_successfully'));
    }

    protected function indexByStatus(Request $request, string $status)
    {
        $branchId = auth()->user()->branch_id;

        $query = BloodBank::query()
            ->with(['patient', 'department', 'tests'])
            ->bloodTest()
            ->where('branch_id', $branchId)
            ->where('status', $status);

        if ($request->filled('q')) {
            $term = $request->q;
            $query->whereHas('patient', function ($p) use ($term) {
                $p->where('name', 'like', '%'.$term.'%')
                    ->orWhere('id_card', 'like', '%'.$term.'%')
                    ->orWhere('father_name', 'like', '%'.$term.'%')
                    ->orWhere('phone', 'like', '%'.$term.'%');
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        $bloodRequests = $query->orderByDesc('created_at')->paginate(15)->withQueryString();
        $departments = Department::where('branch_id', $branchId)->orderBy('name')->get();

        $titles = [
            'new' => localize('global.new_blood_test_requests'),
            'approved' => localize('global.approved_blood_test_requests'),
            'rejected' => localize('global.rejected_blood_test_requests'),
            'delivered' => localize('global.delivered_blood_test_requests'),
        ];

        return view('pages.blood_banks.blood_tests.index', [
            'bloodRequests' => $bloodRequests,
            'departments' => $departments,
            'cardTitle' => $titles[$status] ?? localize('global.blood_tests'),
            'status' => $status,
        ]);
    }

    protected function ensureBloodTestBranch(BloodBank $bloodBank): void
    {
        if (! $bloodBank->isBloodTest()) {
            abort(404);
        }

        if ((int) $bloodBank->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
    }

    protected function ensureCanRecordTests(Request $request): void
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }
    }
}
