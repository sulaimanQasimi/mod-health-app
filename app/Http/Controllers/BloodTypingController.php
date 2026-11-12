<?php

namespace App\Http\Controllers;

use App\Models\BloodBank;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodTypingController extends Controller
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
        $this->ensureBloodTypingBranch($bloodBank);

        $bloodBank->load(['patient', 'department', 'createdBy']);

        $departments = Department::where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view('pages.blood_banks.blood_typing.show', compact('bloodBank', 'departments'));
    }

    public function approve(BloodBank $bloodBank)
    {
        $this->ensureBloodTypingBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.blood_typing_invalid_status'));
        }

        $bloodBank->approve();

        return redirect()
            ->route('blood_banks.blood_typing.show', $bloodBank)
            ->with('success', localize('global.blood_typing_approved_successfully'));
    }

    public function reject(Request $request, BloodBank $bloodBank)
    {
        $this->ensureBloodTypingBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.blood_typing_invalid_status'));
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:2000'],
        ]);

        $bloodBank->reject();
        $bloodBank->update(['reject_reason' => $validated['reject_reason']]);

        return redirect()
            ->route('blood_banks.blood_typing.show', $bloodBank)
            ->with('success', localize('global.blood_typing_rejected_successfully'));
    }

    public function complete(Request $request, BloodBank $bloodBank)
    {
        $this->ensureBloodTypingBranch($bloodBank);
        $this->ensureCanRecordTyping($request);

        if ($bloodBank->status !== 'approved') {
            return redirect()->back()->with('error', localize('global.blood_typing_must_be_approved_to_complete'));
        }

        $validated = $request->validate([
            'group' => ['required', 'string', Rule::in(['A', 'B', 'AB', 'O'])],
            'rh' => ['required', 'string', Rule::in(['+', '-'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ], [
            'group.required' => localize('global.blood_typing_group_required'),
            'rh.required' => localize('global.blood_typing_rh_required'),
        ]);

        $bloodBank->update([
            'group' => $validated['group'],
            'rh' => $validated['rh'],
            'factor' => $validated['notes'] ?? $bloodBank->factor,
        ]);
        $bloodBank->deliver();

        return redirect()
            ->route('blood_banks.blood_typing.show', $bloodBank)
            ->with('success', localize('global.blood_typing_completed_successfully'));
    }

    protected function indexByStatus(Request $request, string $status)
    {
        $branchId = auth()->user()->branch_id;

        $query = BloodBank::query()
            ->with(['patient', 'department'])
            ->bloodTyping()
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

        if ($request->filled('group')) {
            $query->where('group', $request->group);
        }

        if ($request->filled('rh')) {
            $query->where('rh', $request->rh);
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
            'new' => localize('global.new_blood_typing_requests'),
            'approved' => localize('global.approved_blood_typing_requests'),
            'rejected' => localize('global.rejected_blood_typing_requests'),
            'delivered' => localize('global.delivered_blood_typing_requests'),
        ];

        return view('pages.blood_banks.blood_typing.index', [
            'bloodRequests' => $bloodRequests,
            'departments' => $departments,
            'cardTitle' => $titles[$status] ?? localize('global.blood_typing'),
            'status' => $status,
        ]);
    }

    protected function ensureBloodTypingBranch(BloodBank $bloodBank): void
    {
        if (! $bloodBank->isBloodTyping()) {
            abort(404);
        }

        if ((int) $bloodBank->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
    }

    protected function ensureCanRecordTyping(Request $request): void
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }
    }
}
