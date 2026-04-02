<?php

namespace App\Http\Controllers;

use App\Models\ProstheticAssessment;
use App\Models\ProstheticCase;
use App\Models\ProstheticDelivery;
use App\Models\ProstheticEstimate;
use App\Models\ProstheticFittingSession;
use App\Models\ProstheticFollowUp;
use App\Models\ProstheticMeasurement;
use App\Models\ProstheticMeasurementSet;
use App\Models\ProstheticPrescription;
use App\Models\ProstheticPrescriptionLine;
use App\Models\ProstheticWorkOrder;
use App\Services\ProstheticsNumberService;
use App\Services\ProstheticsStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProstheticCaseController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;

        $query = ProstheticCase::query()
            ->with('patient')
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('case_number', 'like', '%'.$q.'%')
                    ->orWhereHas('patient', function ($p) use ($q) {
                        $p->where('name', 'like', '%'.$q.'%')
                            ->orWhere('phone', 'like', '%'.$q.'%')
                            ->orWhere('nid', 'like', '%'.$q.'%');
                    });
            });
        }

        $cases = $query->paginate(25)->withQueryString();

        return view('pages.prosthetics.cases.index', compact('cases'));
    }

    public function create(Request $request)
    {
        $referralId = $request->get('referral_id');
        $prefill = null;
        if ($referralId) {
            $prefill = \App\Models\ProstheticReferral::with('patient')->find($referralId);
        }

        return view('pages.prosthetics.cases.create', compact('prefill', 'referralId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'referral_id' => 'nullable|exists:prosthetic_referrals,id',
            'side' => 'required|string|max:32',
            'body_region' => 'nullable|string|max:255',
            'case_category' => 'required|string|max:64',
            'device_type' => 'nullable|string|max:255',
            'primary_diagnosis' => 'nullable|string',
            'secondary_diagnosis' => 'nullable|string',
            'cause_of_loss_notes' => 'nullable|string',
            'injury_surgery_onset_date' => 'nullable|date',
            'amputation_level' => 'nullable|string|max:255',
            'priority' => 'nullable|string|max:64',
            'first_time_or_replacement' => 'nullable|string|max:64',
            'prior_device_history' => 'nullable|string',
        ]);

        $case = new ProstheticCase($data);
        $case->case_number = ProstheticsNumberService::nextCaseNumber();
        $case->branch_id = auth()->user()->branch_id;
        $case->status = ProstheticCase::STATUS_NEW;
        if (! empty($data['referral_id'])) {
            $case->status = ProstheticCase::STATUS_REFERRED;
        }
        $case->created_by = Auth::id();
        $case->updated_by = Auth::id();
        $case->save();

        return redirect()->route('prosthetics.cases.show', $case)->with('success', __('global.success'));
    }

    public function show(ProstheticCase $prosthetic_case)
    {
        $prosthetic_case->load([
            'patient',
            'referral',
            'assessment',
            'measurementSets.measurements',
            'prescriptions.lines.catalogItem',
            'estimates',
            'workOrders',
            'fittingSessions',
            'deliveries',
            'followUps',
        ]);

        $catalog = \App\Models\ProstheticComponentCatalog::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $activePrescription = $prosthetic_case->prescriptions->sortByDesc('id')->first();
        $activeWorkOrder = $prosthetic_case->workOrders->sortByDesc('id')->first();
        $latestEstimate = $prosthetic_case->estimates->sortByDesc('id')->first();
        $latestMeasurementSet = $prosthetic_case->measurementSets->sortByDesc('version')->first();

        return view('pages.prosthetics.cases.show', compact(
            'prosthetic_case',
            'catalog',
            'activePrescription',
            'activeWorkOrder',
            'latestEstimate',
            'latestMeasurementSet'
        ));
    }

    public function saveAssessment(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'fit_outcome' => 'nullable|string|max:128',
            'history_present_condition' => 'nullable|string',
            'surgical_history' => 'nullable|string',
            'comorbidities' => 'nullable|string',
            'medications' => 'nullable|string',
            'allergies' => 'nullable|string',
            'skin_stump_notes' => 'nullable|string',
            'functional_goals' => 'nullable|string',
            'psychosocial_notes' => 'nullable|string',
        ]);

        $assessment = $prosthetic_case->assessment ?? new ProstheticAssessment(['prosthetic_case_id' => $prosthetic_case->id]);
        $assessment->fill($data);
        $assessment->created_by = $assessment->created_by ?? Auth::id();
        $assessment->updated_by = Auth::id();
        $assessment->save();

        if ($prosthetic_case->status === ProstheticCase::STATUS_NEW
            || $prosthetic_case->status === ProstheticCase::STATUS_REFERRED) {
            $prosthetic_case->status = ProstheticCase::STATUS_UNDER_ASSESSMENT;
            $prosthetic_case->updated_by = Auth::id();
            $prosthetic_case->save();
        }

        return back()->with('success', __('global.success'));
    }

    public function saveMeasurements(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'rows' => 'required|array',
            'rows.*.name' => 'nullable|string|max:255',
            'rows.*.value_numeric' => 'nullable|numeric',
            'rows.*.value_text' => 'nullable|string|max:255',
            'rows.*.unit' => 'nullable|string|max:32',
            'rows.*.notes' => 'nullable|string',
        ]);

        $rows = collect($data['rows'])->filter(fn ($r) => filled($r['name'] ?? null))->values()->all();
        if (count($rows) < 1) {
            return back()->withErrors(['rows' => __('global.prosthetics_measurement_required')])->withInput();
        }

        $set = $prosthetic_case->measurementSets()->where('is_locked', false)->orderByDesc('version')->first();
        if (! $set) {
            $maxVersion = (int) $prosthetic_case->measurementSets()->max('version');
            $set = new ProstheticMeasurementSet([
                'prosthetic_case_id' => $prosthetic_case->id,
                'version' => $maxVersion + 1,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);
            $set->save();
        }

        $set->measurements()->delete();
        foreach ($rows as $row) {
            $set->measurements()->create([
                'name' => $row['name'],
                'value_numeric' => $row['value_numeric'] ?? null,
                'value_text' => $row['value_text'] ?? null,
                'unit' => $row['unit'] ?? null,
                'notes' => $row['notes'] ?? null,
            ]);
        }

        $set->updated_by = Auth::id();
        $set->save();

        if (in_array($prosthetic_case->status, [
            ProstheticCase::STATUS_UNDER_ASSESSMENT,
            ProstheticCase::STATUS_REFERRED,
            ProstheticCase::STATUS_NEW,
        ], true)) {
            $prosthetic_case->status = ProstheticCase::STATUS_MEASUREMENT_COMPLETED;
            $prosthetic_case->updated_by = Auth::id();
            $prosthetic_case->save();
        }

        return back()->with('success', __('global.success'));
    }

    public function lockMeasurements(ProstheticCase $prosthetic_case)
    {
        $set = $prosthetic_case->measurementSets()->where('is_locked', false)->orderByDesc('version')->first();
        if ($set) {
            $set->is_locked = true;
            $set->locked_at = now();
            $set->locked_by = Auth::id();
            $set->save();
        }

        return back()->with('success', __('global.success'));
    }

    public function savePrescription(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'device_timing' => 'nullable|string|max:64',
            'target_functionality' => 'nullable|string',
            'suspension_notes' => 'nullable|string',
            'socket_type' => 'nullable|string|max:255',
            'liner_type' => 'nullable|string|max:255',
            'foot_type' => 'nullable|string|max:255',
            'special_instructions' => 'nullable|string',
            'clinical_justification' => 'nullable|string',
            'lines' => 'nullable|array',
            'lines.*.catalog_id' => 'nullable|exists:prosthetic_component_catalog,id',
            'lines.*.quantity' => 'nullable|numeric|min:0.001',
            'lines.*.notes' => 'nullable|string',
        ]);

        $lineRows = collect($data['lines'] ?? [])->filter(fn ($l) => ! empty($l['catalog_id']))->values()->all();
        if (count($lineRows) < 1) {
            return back()->withErrors(['lines' => __('global.prosthetics_prescription_lines_required')])->withInput();
        }

        $rx = $prosthetic_case->prescriptions()->where('status', 'draft')->orderByDesc('id')->first();
        if (! $rx) {
            $rx = new ProstheticPrescription(['prosthetic_case_id' => $prosthetic_case->id, 'status' => 'draft']);
            $rx->created_by = Auth::id();
        }
        $rx->fill([
            'device_timing' => $data['device_timing'] ?? 'definitive',
            'target_functionality' => $data['target_functionality'] ?? null,
            'suspension_notes' => $data['suspension_notes'] ?? null,
            'socket_type' => $data['socket_type'] ?? null,
            'liner_type' => $data['liner_type'] ?? null,
            'foot_type' => $data['foot_type'] ?? null,
            'special_instructions' => $data['special_instructions'] ?? null,
            'clinical_justification' => $data['clinical_justification'] ?? null,
        ]);
        $rx->updated_by = Auth::id();
        $rx->save();

        $rx->lines()->delete();
        foreach ($lineRows as $line) {
            $item = \App\Models\ProstheticComponentCatalog::findOrFail($line['catalog_id']);
            $rx->lines()->create([
                'prosthetic_component_catalog_id' => $item->id,
                'quantity' => $line['quantity'] ?? 1,
                'unit_cost_snapshot' => $item->standard_cost,
                'notes' => $line['notes'] ?? null,
            ]);
        }

        $rx->status = 'final';
        $rx->save();

        $prosthetic_case->status = ProstheticCase::STATUS_PRESCRIPTION_COMPLETED;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        $this->syncEstimateFromPrescription($prosthetic_case, $rx);

        return back()->with('success', __('global.success'));
    }

    protected function syncEstimateFromPrescription(ProstheticCase $case, ProstheticPrescription $rx): void
    {
        $rx->load('lines');
        $parts = (float) $rx->lines->sum(fn ($l) => (float) $l->quantity * (float) $l->unit_cost_snapshot);

        $estimate = $case->estimates()->where('prosthetic_prescription_id', $rx->id)->first();
        if (! $estimate) {
            $estimate = new ProstheticEstimate([
                'prosthetic_case_id' => $case->id,
                'prosthetic_prescription_id' => $rx->id,
                'currency' => 'AFN',
                'status' => 'draft',
                'created_by' => Auth::id(),
            ]);
        }
        $estimate->parts_total = $parts;
        $estimate->labor_total = $estimate->labor_total ?? 0;
        $estimate->discount = $estimate->discount ?? 0;
        $estimate->total = max(0, $estimate->parts_total + $estimate->labor_total - $estimate->discount);
        $estimate->updated_by = Auth::id();
        $estimate->save();
    }

    public function updateEstimate(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'estimate_id' => 'required|exists:prosthetic_estimates,id',
            'labor_total' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:8',
        ]);

        $estimate = ProstheticEstimate::where('prosthetic_case_id', $prosthetic_case->id)->findOrFail($data['estimate_id']);
        if (isset($data['labor_total'])) {
            $estimate->labor_total = $data['labor_total'];
        }
        if (isset($data['discount'])) {
            $estimate->discount = $data['discount'];
        }
        if (! empty($data['currency'])) {
            $estimate->currency = $data['currency'];
        }
        $estimate->total = max(0, (float) $estimate->parts_total + (float) $estimate->labor_total - (float) $estimate->discount);
        $estimate->updated_by = Auth::id();
        $estimate->save();

        return back()->with('success', __('global.success'));
    }

    public function submitForApproval(ProstheticCase $prosthetic_case)
    {
        $estimate = $prosthetic_case->estimates()->orderByDesc('id')->first();
        if (! $estimate) {
            return back()->with('error', __('global.prosthetics_need_estimate'));
        }

        $prosthetic_case->status = ProstheticCase::STATUS_WAITING_APPROVAL;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        $estimate->status = 'pending_approval';
        $estimate->save();

        return back()->with('success', __('global.success'));
    }

    public function approveCase(ProstheticCase $prosthetic_case)
    {
        $prosthetic_case->status = ProstheticCase::STATUS_APPROVED;
        $prosthetic_case->approved_at = now();
        $prosthetic_case->approved_by = Auth::id();
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        $est = $prosthetic_case->estimates()->orderByDesc('id')->first();
        if ($est) {
            $est->status = 'approved';
            $est->save();
        }

        return back()->with('success', __('global.success'));
    }

    public function createWorkOrder(Request $request, ProstheticCase $prosthetic_case)
    {
        $rx = $prosthetic_case->prescriptions()->where('status', 'final')->orderByDesc('id')->first();
        if (! $rx) {
            return back()->with('error', __('global.prosthetics_need_prescription'));
        }

        $data = $request->validate([
            'planned_start_date' => 'nullable|date',
            'planned_end_date' => 'nullable|date|after_or_equal:planned_start_date',
            'technician_user_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ]);

        $wo = new ProstheticWorkOrder([
            'work_order_number' => ProstheticsNumberService::nextWorkOrderNumber(),
            'prosthetic_case_id' => $prosthetic_case->id,
            'prosthetic_prescription_id' => $rx->id,
            'status' => 'issued',
            'production_stage' => 'materials_issued',
            'technician_user_id' => $data['technician_user_id'] ?? null,
            'planned_start_date' => $data['planned_start_date'] ?? null,
            'planned_end_date' => $data['planned_end_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
        $wo->save();

        $prosthetic_case->status = ProstheticCase::STATUS_IN_PRODUCTION;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        return back()->with('success', __('global.success'));
    }

    public function updateWorkOrder(Request $request, ProstheticWorkOrder $prosthetic_work_order)
    {
        $data = $request->validate([
            'production_stage' => 'nullable|string|max:64',
            'status' => 'nullable|string|max:64',
            'remarks' => 'nullable|string',
        ]);

        $prosthetic_work_order->fill(array_filter($data, fn ($v) => $v !== null));
        $prosthetic_work_order->updated_by = Auth::id();
        $prosthetic_work_order->save();

        return back()->with('success', __('global.success'));
    }

    public function issueStock(Request $request, ProstheticCase $prosthetic_case, ProstheticsStockService $stock)
    {
        $data = $request->validate([
            'prosthetic_work_order_id' => 'required|exists:prosthetic_work_orders,id',
        ]);

        $wo = ProstheticWorkOrder::where('prosthetic_case_id', $prosthetic_case->id)->findOrFail($data['prosthetic_work_order_id']);
        $rx = $wo->prescription;
        if (! $rx) {
            return back()->with('error', __('global.prosthetics_need_prescription'));
        }

        $rx->load('lines');
        $lines = $rx->lines->map(fn ($l) => [
            'catalog_id' => $l->prosthetic_component_catalog_id,
            'quantity' => (float) $l->quantity,
        ])->all();

        try {
            $stock->issueToWorkOrder($wo->id, auth()->user()->branch_id, $lines);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('global.success'));
    }

    public function storeFitting(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'session_date' => 'required|date',
            'prosthetic_work_order_id' => 'nullable|exists:prosthetic_work_orders,id',
            'outcome' => 'nullable|string|max:64',
            'comfort_score' => 'nullable|integer|min:1|max:10',
            'issues_identified' => 'nullable|string',
            'modifications_required' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['prosthetic_case_id'] = $prosthetic_case->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        ProstheticFittingSession::create($data);

        $prosthetic_case->status = ProstheticCase::STATUS_TRIAL_FIT;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        return back()->with('success', __('global.success'));
    }

    public function storeDelivery(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'delivered_at' => 'required|date',
            'received_by_name' => 'nullable|string|max:255',
            'device_serial_notes' => 'nullable|string',
            'instructions_explained' => 'nullable|string',
            'warranty_until' => 'nullable|date',
            'follow_up_scheduled_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data['handover_signed'] = $request->boolean('handover_signed');
        $data['prosthetic_case_id'] = $prosthetic_case->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        $data['handover_signed'] = $request->boolean('handover_signed');
        ProstheticDelivery::create($data);

        $prosthetic_case->status = ProstheticCase::STATUS_DELIVERED;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        return back()->with('success', __('global.success'));
    }

    public function storeFollowUp(Request $request, ProstheticCase $prosthetic_case)
    {
        $data = $request->validate([
            'follow_up_type' => 'nullable|string|max:64',
            'scheduled_at' => 'required|date',
            'completed_at' => 'nullable|date',
            'outcome' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $data['prosthetic_case_id'] = $prosthetic_case->id;
        $data['created_by'] = Auth::id();
        $data['updated_by'] = Auth::id();
        ProstheticFollowUp::create($data);

        $prosthetic_case->status = ProstheticCase::STATUS_UNDER_FOLLOW_UP;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        return back()->with('success', __('global.success'));
    }

    public function closeCase(ProstheticCase $prosthetic_case)
    {
        $prosthetic_case->status = ProstheticCase::STATUS_CLOSED;
        $prosthetic_case->updated_by = Auth::id();
        $prosthetic_case->save();

        return back()->with('success', __('global.success'));
    }
}
