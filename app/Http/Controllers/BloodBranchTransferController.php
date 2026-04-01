<?php

namespace App\Http\Controllers;

use App\Models\BloodBranchTransfer;
use App\Models\BloodUnit;
use App\Models\Branch;
use App\Services\BloodBankStockService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BloodBranchTransferController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $outgoing = BloodBranchTransfer::where('requesting_branch_id', $branchId)
            ->with(['supplyingBranch', 'createdBy'])
            ->latest()
            ->paginate(15, ['*'], 'out_page');

        $incoming = BloodBranchTransfer::where('supplying_branch_id', $branchId)
            ->with(['requestingBranch', 'createdBy'])
            ->latest()
            ->paginate(15, ['*'], 'in_page');

        return view('pages.blood_banks.branch_transfers.index', compact('outgoing', 'incoming'));
    }

    public function create()
    {
        $branches = Branch::where('id', '!=', auth()->user()->branch_id)->orderBy('name')->get();

        return view('pages.blood_banks.branch_transfers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplying_branch_id' => ['required', 'exists:branches,id', Rule::notIn([(int) auth()->user()->branch_id])],
            'blood_group' => 'required|string|in:A,B,AB,O',
            'rh' => 'required|string|in:+,-',
            'component_type' => 'required|string|in:Fresh,RBC,PRBC,Platelets,Plasma,Whole Blood',
            'quantity' => 'required|integer|min:1|max:500',
            'notes' => 'nullable|string|max:2000',
        ]);

        $requestingId = (int) auth()->user()->branch_id;
        if ((int) $validated['supplying_branch_id'] === $requestingId) {
            return redirect()->back()->withInput()->with('error', localize('global.blood_branch_transfer_same_branch'));
        }

        BloodBranchTransfer::create([
            'requesting_branch_id' => $requestingId,
            'supplying_branch_id' => $validated['supplying_branch_id'],
            'blood_group' => $validated['blood_group'],
            'rh' => $validated['rh'],
            'component_type' => $validated['component_type'],
            'quantity' => $validated['quantity'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('blood_banks.branch_transfers.index')
            ->with('success', localize('global.blood_branch_transfer_created'));
    }

    public function show(BloodBranchTransfer $branchTransfer)
    {
        $this->authorizeView($branchTransfer);

        $branchTransfer->load(['requestingBranch', 'supplyingBranch', 'createdBy', 'fulfilledByUser']);

        $availableUnits = collect();
        if (
            $branchTransfer->status === 'pending'
            && (int) auth()->user()->branch_id === (int) $branchTransfer->supplying_branch_id
        ) {
            $availableUnits = BloodUnit::where('branch_id', $branchTransfer->supplying_branch_id)
                ->where('blood_group', $branchTransfer->blood_group)
                ->where('rh', $branchTransfer->rh)
                ->where('component_type', $branchTransfer->component_type)
                ->where('status', 'available')
                ->where('expires_at', '>', now())
                ->orderBy('expires_at')
                ->get();
        }

        return view('pages.blood_banks.branch_transfers.show', compact('branchTransfer', 'availableUnits'));
    }

    public function reject(Request $request, BloodBranchTransfer $branchTransfer)
    {
        if ((int) auth()->user()->branch_id !== (int) $branchTransfer->supplying_branch_id) {
            abort(403);
        }

        if ($branchTransfer->status !== 'pending') {
            return redirect()->back()->with('error', localize('global.blood_branch_transfer_not_pending'));
        }

        $validated = $request->validate([
            'reject_reason' => 'nullable|string|max:2000',
        ]);

        $branchTransfer->update([
            'status' => 'rejected',
            'reject_reason' => $validated['reject_reason'] ?? null,
        ]);

        return redirect()->route('blood_banks.branch_transfers.index')->with('success', localize('global.blood_branch_transfer_rejected'));
    }

    public function fulfill(Request $request, BloodBranchTransfer $branchTransfer)
    {
        if ((int) auth()->user()->branch_id !== (int) $branchTransfer->supplying_branch_id) {
            abort(403);
        }

        $unitIds = $request->input('unit_ids', []);
        $unitIds = is_array($unitIds) ? array_values(array_filter(array_map('intval', $unitIds))) : [];

        try {
            app(BloodBankStockService::class)->fulfillBranchTransfer(
                $branchTransfer,
                count($unitIds) > 0 ? $unitIds : null
            );
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('blood_banks.branch_transfers.show', $branchTransfer)->with('success', localize('global.blood_branch_transfer_completed'));
    }

    public function cancel(BloodBranchTransfer $branchTransfer)
    {
        if ((int) auth()->user()->branch_id !== (int) $branchTransfer->requesting_branch_id) {
            abort(403);
        }

        if ($branchTransfer->status !== 'pending') {
            return redirect()->back()->with('error', localize('global.blood_branch_transfer_not_pending'));
        }

        $branchTransfer->update(['status' => 'cancelled']);

        return redirect()->route('blood_banks.branch_transfers.index')->with('success', localize('global.blood_branch_transfer_cancelled'));
    }

    protected function authorizeView(BloodBranchTransfer $branchTransfer): void
    {
        $uid = (int) auth()->user()->branch_id;
        if (
            $uid !== (int) $branchTransfer->requesting_branch_id
            && $uid !== (int) $branchTransfer->supplying_branch_id
        ) {
            abort(403);
        }
    }
}
