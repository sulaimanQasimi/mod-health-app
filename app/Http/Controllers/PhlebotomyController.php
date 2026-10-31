<?php

namespace App\Http\Controllers;

use App\Models\BloodBank;
use App\Models\Department;
use App\Services\BloodBankStockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PhlebotomyController extends Controller
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
        $this->ensurePhlebotomyBranch($bloodBank);

        $bloodBank->load(['patient', 'department', 'createdBy', 'bloodUnits']);

        $departments = Department::where('branch_id', auth()->user()->branch_id)
            ->orderBy('name')
            ->get();

        return view('pages.blood_banks.phlebotomy.show', compact('bloodBank', 'departments'));
    }

    public function approve(BloodBank $bloodBank)
    {
        $this->ensurePhlebotomyBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.phlebotomy_invalid_status'));
        }

        $bloodBank->approve();

        return redirect()
            ->route('blood_banks.phlebotomy.show', $bloodBank)
            ->with('success', localize('global.phlebotomy_approved_successfully'));
    }

    public function reject(Request $request, BloodBank $bloodBank)
    {
        $this->ensurePhlebotomyBranch($bloodBank);

        if ($bloodBank->status !== 'new') {
            return redirect()->back()->with('error', localize('global.phlebotomy_invalid_status'));
        }

        $validated = $request->validate([
            'reject_reason' => ['required', 'string', 'max:2000'],
        ]);

        $bloodBank->reject();
        $bloodBank->update(['reject_reason' => $validated['reject_reason']]);

        return redirect()
            ->route('blood_banks.phlebotomy.show', $bloodBank)
            ->with('success', localize('global.phlebotomy_rejected_successfully'));
    }

    public function sendToStock(Request $request, BloodBank $bloodBank)
    {
        $this->ensurePhlebotomyBranch($bloodBank);
        $this->ensureCanReceiveStock($request);

        if ($bloodBank->status !== 'approved') {
            return redirect()->back()->with('error', localize('global.phlebotomy_must_be_approved_to_stock'));
        }

        if (! $bloodBank->group || ! $bloodBank->rh || ! $bloodBank->type) {
            return redirect()->back()->withInput()->with('error', localize('global.phlebotomy_missing_blood_data'));
        }

        foreach (['volume_ml', 'collected_at', 'notes'] as $optional) {
            if ($request->input($optional) === '' || $request->input($optional) === null) {
                $request->merge([$optional => null]);
            }
        }

        $validated = $request->validate([
            'bag_number' => ['required', 'string', 'max:100', Rule::unique('blood_units', 'bag_number')->whereNull('deleted_at')],
            'volume_ml' => ['nullable', 'integer', 'min:1', 'max:2000'],
            'collected_at' => ['nullable', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ], [
            'bag_number.required' => localize('global.phlebotomy_bag_number_required'),
            'bag_number.unique' => localize('global.blood_bag_already_registered'),
            'bag_number.max' => localize('global.phlebotomy_bag_number_invalid'),
            'volume_ml.integer' => localize('global.phlebotomy_volume_invalid'),
            'volume_ml.min' => localize('global.phlebotomy_volume_invalid'),
            'volume_ml.max' => localize('global.phlebotomy_volume_invalid'),
            'collected_at.date' => localize('global.phlebotomy_collected_at_invalid'),
            'expires_at.required' => localize('global.phlebotomy_expires_at_required'),
            'expires_at.date' => localize('global.phlebotomy_expires_at_invalid'),
            'expires_at.after_or_equal' => localize('global.phlebotomy_expires_at_must_be_today_or_later'),
        ]);

        $volumeMl = $validated['volume_ml']
            ?? (is_numeric($bloodBank->quantity) ? (int) $bloodBank->quantity : null);

        try {
            DB::transaction(function () use ($bloodBank, $validated, $volumeMl) {
                $unit = app(BloodBankStockService::class)->receiveUnit([
                    'branch_id' => $bloodBank->branch_id,
                    'blood_group' => $bloodBank->group,
                    'rh' => $bloodBank->rh,
                    'component_type' => $bloodBank->type,
                    'bag_number' => $validated['bag_number'],
                    'volume_ml' => $volumeMl,
                    'collected_at' => isset($validated['collected_at'])
                        ? Carbon::parse($validated['collected_at'])
                        : now(),
                    'expires_at' => Carbon::parse($validated['expires_at']),
                    'notes' => $validated['notes']
                        ?? localize('global.phlebotomy_stock_notes').' #'.$bloodBank->id,
                    'reference' => $bloodBank,
                ]);

                $bloodBank->bloodUnits()->syncWithoutDetaching([
                    $unit->id => [
                        'issued_at' => null,
                        'issued_by' => null,
                    ],
                ]);

                $bloodBank->deliver();
            });
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('blood_banks.phlebotomy.show', $bloodBank)
            ->with('success', localize('global.phlebotomy_sent_to_stock_successfully'));
    }

    protected function indexByStatus(Request $request, string $status)
    {
        $branchId = auth()->user()->branch_id;

        $query = BloodBank::query()
            ->with(['patient', 'department'])
            ->phlebotomy()
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

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
            'new' => localize('global.new_phlebotomy_requests'),
            'approved' => localize('global.approved_phlebotomy_requests'),
            'rejected' => localize('global.rejected_phlebotomy_requests'),
            'delivered' => localize('global.delivered_phlebotomy_requests'),
        ];

        return view('pages.blood_banks.phlebotomy.index', [
            'bloodRequests' => $bloodRequests,
            'departments' => $departments,
            'cardTitle' => $titles[$status] ?? localize('global.phlebotomy'),
            'status' => $status,
        ]);
    }

    protected function ensurePhlebotomyBranch(BloodBank $bloodBank): void
    {
        if (! $bloodBank->isPhlebotomy()) {
            abort(404);
        }

        if ((int) $bloodBank->branch_id !== (int) auth()->user()->branch_id) {
            abort(404);
        }
    }

    protected function ensureCanReceiveStock(Request $request): void
    {
        $user = $request->user();
        if (! $user->can('receive-blood-units') && ! $user->can('manage-blood-inventory')) {
            abort(403);
        }
    }
}
