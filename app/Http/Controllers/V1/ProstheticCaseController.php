<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ProstheticAttachmentController as LegacyProstheticAttachmentController;
use App\Http\Controllers\ProstheticCaseController as LegacyProstheticCaseController;
use App\Http\Controllers\V1\Concerns\ManagesProstheticsAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\ProstheticCase;
use App\Models\ProstheticComponentCatalog;
use App\Models\ProstheticReferral;
use App\Models\ProstheticWorkOrder;
use App\Services\ProstheticsNumberService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProstheticCaseController extends Controller
{
    use ManagesProstheticsAccess;
    use PaginatesInertiaIndex;

    public function index(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $branchId = $this->userBranchId();

        $query = ProstheticCase::query()
            ->with('patient:id,name,last_name,phone,nid')
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

        $paginator = $this->paginateQuery($query, $request, 25, [10, 15, 25, 50]);
        $cases = $this->paginationPayload($paginator, fn (ProstheticCase $case) => [
            'id' => $case->id,
            'case_number' => $case->case_number,
            'status' => $case->status,
            'updated_at' => $case->updated_at?->toIso8601String(),
            'patient' => $case->patient ? [
                'id' => $case->patient->id,
                'name' => $case->patient->name,
                'last_name' => $case->patient->last_name,
                'phone' => $case->patient->phone,
                'nid' => $case->patient->nid,
            ] : null,
        ]);

        return Inertia::render('Prosthetics/Cases/Index', [
            'cases' => $cases,
            'filters' => array_merge(['q' => '', 'status' => ''], $request->only(['q', 'status'])),
            'statusOptions' => ProstheticCase::statusList(),
            'urls' => [
                'current' => route('prosthetics.cases.index'),
                'create' => route('prosthetics.cases.create'),
                'show' => url('/prosthetics/cases'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeProstheticsMenu();

        $prefill = null;
        if ($request->filled('referral_id')) {
            $referral = ProstheticReferral::with('patient:id,name,last_name')->find($request->referral_id);
            if ($referral) {
                $prefill = [
                    'referral_id' => $referral->id,
                    'patient_id' => $referral->patient_id,
                    'patient_name' => $referral->patient
                        ? trim($referral->patient->name.' '.$referral->patient->last_name)
                        : null,
                    'primary_diagnosis' => $referral->diagnosis_summary,
                ];
            }
        }

        return Inertia::render('Prosthetics/Cases/Create', [
            'prefill' => $prefill,
            'formOptions' => $this->caseFormOptions(),
            'urls' => [
                'index' => route('prosthetics.cases.index'),
                'store' => route('prosthetics.cases.store'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeProstheticsMenu();

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
        $case->branch_id = $this->userBranchId();
        $case->status = ProstheticCase::STATUS_NEW;
        if (! empty($data['referral_id'])) {
            $case->status = ProstheticCase::STATUS_REFERRED;
        }
        $case->created_by = Auth::id();
        $case->updated_by = Auth::id();
        $case->save();

        return redirect()
            ->route('prosthetics.cases.show', $case)
            ->with('success', __('global.success'));
    }

    public function show(ProstheticCase $prosthetic_case): Response
    {
        $this->authorizeCase($prosthetic_case);

        $prosthetic_case->load([
            'patient:id,name,last_name',
            'referral:id,referral_number',
            'assessment',
            'measurementSets.measurements',
            'prescriptions.lines.catalogItem:id,item_code,name',
            'estimates',
            'workOrders',
            'fittingSessions',
            'deliveries',
            'followUps',
            'attachments',
        ]);

        $catalog = ProstheticComponentCatalog::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get(['id', 'item_code', 'name', 'category']);

        $activePrescription = $prosthetic_case->prescriptions->sortByDesc('id')->first();
        $activeWorkOrder = $prosthetic_case->workOrders->sortByDesc('id')->first();
        $latestEstimate = $prosthetic_case->estimates->sortByDesc('id')->first();
        $latestMeasurementSet = $prosthetic_case->measurementSets->sortByDesc('version')->first();

        $permissions = $this->caseWorkflowPermissions($prosthetic_case, $activeWorkOrder);

        return Inertia::render('Prosthetics/Cases/Show', [
            'prostheticCase' => $this->transformCaseDetail(
                $prosthetic_case,
                $activePrescription,
                $activeWorkOrder,
                $latestEstimate,
                $latestMeasurementSet
            ),
            'catalog' => $catalog,
            'formOptions' => $this->caseWorkflowOptions(),
            'permissions' => $permissions,
            'workflowSteps' => [
                ProstheticCase::STATUS_NEW,
                ProstheticCase::STATUS_REFERRED,
                ProstheticCase::STATUS_UNDER_ASSESSMENT,
                ProstheticCase::STATUS_MEASUREMENT_COMPLETED,
                ProstheticCase::STATUS_PRESCRIPTION_COMPLETED,
                ProstheticCase::STATUS_WAITING_APPROVAL,
                ProstheticCase::STATUS_APPROVED,
                ProstheticCase::STATUS_IN_PRODUCTION,
                ProstheticCase::STATUS_TRIAL_FIT,
                ProstheticCase::STATUS_DELIVERED,
                ProstheticCase::STATUS_UNDER_FOLLOW_UP,
            ],
            'urls' => $this->caseUrls($prosthetic_case, $activeWorkOrder),
        ]);
    }

    public function saveAssessment(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->saveAssessment($request, $prosthetic_case);
    }

    public function saveMeasurements(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->saveMeasurements($request, $prosthetic_case);
    }

    public function lockMeasurements(ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->lockMeasurements($prosthetic_case);
    }

    public function savePrescription(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->savePrescription($request, $prosthetic_case);
    }

    public function updateEstimate(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->updateEstimate($request, $prosthetic_case);
    }

    public function submitForApproval(ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->submitForApproval($prosthetic_case);
    }

    public function approveCase(ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->approveCase($prosthetic_case);
    }

    public function createWorkOrder(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->createWorkOrder($request, $prosthetic_case);
    }

    public function updateWorkOrder(Request $request, ProstheticWorkOrder $prosthetic_work_order): RedirectResponse
    {
        $prosthetic_case = $prosthetic_work_order->prostheticCase;
        if ($prosthetic_case) {
            $this->authorizeCase($prosthetic_case);
        }

        return app(LegacyProstheticCaseController::class)->updateWorkOrder($request, $prosthetic_work_order);
    }

    public function issueStock(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->issueStock($request, $prosthetic_case);
    }

    public function storeFitting(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->storeFitting($request, $prosthetic_case);
    }

    public function storeDelivery(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->storeDelivery($request, $prosthetic_case);
    }

    public function storeFollowUp(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->storeFollowUp($request, $prosthetic_case);
    }

    public function closeCase(ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticCaseController::class)->closeCase($prosthetic_case);
    }

    public function uploadAttachments(Request $request, ProstheticCase $prosthetic_case): RedirectResponse
    {
        $this->authorizeCase($prosthetic_case);

        return app(LegacyProstheticAttachmentController::class)->upload($request, $prosthetic_case);
    }

    public function deleteAttachment(int $attachment): RedirectResponse
    {
        return app(LegacyProstheticAttachmentController::class)->delete(
            \App\Models\ProstheticAttachment::findOrFail($attachment)
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function transformCaseDetail(
        ProstheticCase $case,
        $activePrescription,
        $activeWorkOrder,
        $latestEstimate,
        $latestMeasurementSet
    ): array {
        $measRows = ($latestMeasurementSet?->measurements ?? collect())->values();
        $rxLines = ($activePrescription?->lines ?? collect())->values();

        return [
            'id' => $case->id,
            'case_number' => $case->case_number,
            'status' => $case->status,
            'patient_id' => $case->patient_id,
            'patient_name' => $case->patient
                ? trim($case->patient->name.' '.$case->patient->last_name)
                : null,
            'referral' => $case->referral ? [
                'id' => $case->referral->id,
                'referral_number' => $case->referral->referral_number,
            ] : null,
            'assessment' => $case->assessment ? [
                'fit_outcome' => $case->assessment->fit_outcome ?? 'pending',
                'history_present_condition' => $case->assessment->history_present_condition,
                'skin_stump_notes' => $case->assessment->skin_stump_notes,
                'functional_goals' => $case->assessment->functional_goals,
            ] : null,
            'measurement_set' => $latestMeasurementSet ? [
                'id' => $latestMeasurementSet->id,
                'version' => $latestMeasurementSet->version,
                'is_locked' => (bool) $latestMeasurementSet->is_locked,
                'rows' => collect(range(0, 7))->map(fn (int $idx) => [
                    'name' => $measRows->get($idx)?->name ?? '',
                    'value_numeric' => $measRows->get($idx)?->value_numeric ?? '',
                    'unit' => $measRows->get($idx)?->unit ?? '',
                    'notes' => $measRows->get($idx)?->notes ?? '',
                ])->all(),
            ] : [
                'id' => null,
                'version' => null,
                'is_locked' => false,
                'rows' => collect(range(0, 7))->map(fn () => [
                    'name' => '', 'value_numeric' => '', 'unit' => '', 'notes' => '',
                ])->all(),
            ],
            'prescription' => $activePrescription ? [
                'device_timing' => $activePrescription->device_timing ?: 'definitive',
                'special_instructions' => $activePrescription->special_instructions,
                'lines' => collect(range(0, 7))->map(fn (int $idx) => [
                    'catalog_id' => $rxLines->get($idx)?->prosthetic_component_catalog_id ?? '',
                    'quantity' => $rxLines->get($idx)?->quantity ?? '1',
                    'notes' => $rxLines->get($idx)?->notes ?? '',
                ])->all(),
            ] : null,
            'estimate' => $latestEstimate ? [
                'id' => $latestEstimate->id,
                'parts_total' => (float) $latestEstimate->parts_total,
                'labor_total' => (float) $latestEstimate->labor_total,
                'discount' => (float) $latestEstimate->discount,
                'total' => (float) $latestEstimate->total,
                'currency' => $latestEstimate->currency,
                'status' => $latestEstimate->status,
            ] : null,
            'work_order' => $activeWorkOrder ? [
                'id' => $activeWorkOrder->id,
                'work_order_number' => $activeWorkOrder->work_order_number,
                'status' => $activeWorkOrder->status,
                'production_stage' => $activeWorkOrder->production_stage,
            ] : null,
            'attachments' => $case->attachments->sortByDesc('created_at')->values()->map(fn ($att) => [
                'id' => $att->id,
                'original_name' => $att->original_name ?? basename($att->path),
                'category' => $att->category ?? 'general',
                'file_url' => $att->file_url,
                'created_at' => $att->created_at?->format('Y-m-d'),
            ])->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseFormOptions(): array
    {
        return [
            'sides' => ['left', 'right', 'bilateral'],
            'categories' => ['prosthetic', 'orthotic', 'assistive'],
            'priorities' => ['low', 'normal', 'high', 'urgent'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseWorkflowOptions(): array
    {
        return [
            'fit_outcomes' => ['pending', 'fit_for_device', 'delay', 'not_suitable', 'temporary_device', 'permanent_device'],
            'device_timings' => ['definitive', 'temporary', 'preparatory'],
            'work_order_stages' => [
                'pending', 'materials_issued', 'socket_fabrication', 'assembly',
                'trial_fit_ready', 'quality_control', 'ready_for_delivery', 'completed',
            ],
            'fitting_outcomes' => ['pending', 'passed', 'minor_adjustment', 'major_rework', 'remake'],
            'follow_up_types' => ['1_week', '1_month', '3_month', '6_month', 'annual', 'unscheduled'],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function caseUrls(ProstheticCase $case, $activeWorkOrder): array
    {
        $urls = [
            'index' => route('prosthetics.cases.index'),
            'print' => route('prosthetics.cases.print', $case),
            'assessment' => route('prosthetics.cases.assessment', $case),
            'measurements' => route('prosthetics.cases.measurements', $case),
            'measurements_lock' => route('prosthetics.cases.measurements.lock', $case),
            'prescription' => route('prosthetics.cases.prescription', $case),
            'estimate' => route('prosthetics.cases.estimate', $case),
            'submit_approval' => route('prosthetics.cases.submit_approval', $case),
            'approve' => route('prosthetics.cases.approve', $case),
            'work_order' => route('prosthetics.cases.work_order', $case),
            'issue_stock' => route('prosthetics.cases.issue_stock', $case),
            'fitting' => route('prosthetics.cases.fitting', $case),
            'delivery' => route('prosthetics.cases.delivery', $case),
            'follow_up' => route('prosthetics.cases.follow_up', $case),
            'close' => route('prosthetics.cases.close', $case),
            'attachments_upload' => route('prosthetics.cases.attachments.upload', $case),
            'attachment_delete' => url('/prosthetics/attachments'),
        ];

        if ($activeWorkOrder) {
            $urls['work_order_update'] = route('prosthetics.work_orders.update', $activeWorkOrder);
        }

        return $urls;
    }
}
