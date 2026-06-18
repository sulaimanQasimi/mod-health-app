<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesBloodBankListing;
use App\Http\Controllers\V1\Concerns\ManagesBloodBankWorkflow;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Models\BloodBank;
use App\Models\BloodBranchTransfer;
use App\Models\BloodCheckRecord;
use App\Models\BloodCrossmatch;
use App\Models\BloodStockMovement;
use App\Models\BloodUnit;
use App\Models\Department;
use App\Models\Nurse;
use App\Services\BloodBankStockService;
use App\Support\PersianDateParser;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BloodBankController extends Controller
{
    use ManagesBloodBankListing;
    use ManagesBloodBankWorkflow;
    use PaginatesInertiaIndex;

    public function dashboard(Request $request): Response
    {
        $this->authorizeBloodBankMenu();

        $branchId = $this->bloodBankBranchId();
        $stockService = app(BloodBankStockService::class);
        $stockService->archiveExpiredUnits($branchId, $request->user()->id);

        $lowThreshold = config('blood_bank.low_stock_threshold', 5);
        $criticalDays = config('blood_bank.expiry_critical_days', 3);
        $warningDays = config('blood_bank.expiry_warning_days', 7);

        $statusCounts = BloodBank::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('status, count(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status');

        $availableByGroup = BloodUnit::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->selectRaw('blood_group, rh, component_type, count(*) as c')
            ->groupBy('blood_group', 'rh', 'component_type')
            ->get();

        $lowStockRows = $availableByGroup->filter(fn ($row) => (int) $row->c < $lowThreshold)->values();

        $expiringSoon = BloodUnit::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($warningDays))
            ->orderBy('expires_at')
            ->limit(25)
            ->get()
            ->map(fn (BloodUnit $unit) => $this->transformBloodUnitSummary($unit))
            ->values()
            ->all();

        $criticalExpiryCount = BloodUnit::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'available')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<=', now()->addDays($criticalDays))
            ->count();

        $pendingTransfersCount = BloodBranchTransfer::query()
            ->where('status', 'pending')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where(function ($inner) use ($branchId) {
                    $inner->where('requesting_branch_id', $branchId)
                        ->orWhere('supplying_branch_id', $branchId);
                });
            })
            ->count();

        $quarantineCount = BloodUnit::query()
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'quarantine')
            ->count();

        return Inertia::render('BloodBanks/Dashboard', [
            'stats' => [
                'critical_expiry_count' => $criticalExpiryCount,
                'pending_transfers_count' => $pendingTransfersCount,
                'quarantine_count' => $quarantineCount,
                'expiring_soon_count' => count($expiringSoon),
                'low_threshold' => $lowThreshold,
                'critical_days' => $criticalDays,
                'warning_days' => $warningDays,
                'status_counts' => [
                    'new' => (int) ($statusCounts['new'] ?? 0),
                    'approved' => (int) ($statusCounts['approved'] ?? 0),
                    'rejected' => (int) ($statusCounts['rejected'] ?? 0),
                    'delivered' => (int) ($statusCounts['delivered'] ?? 0),
                ],
            ],
            'lowStockRows' => $lowStockRows->map(fn ($row) => [
                'blood_group' => $row->blood_group,
                'rh' => $row->rh,
                'component_type' => $row->component_type,
                'count' => (int) $row->c,
            ])->values()->all(),
            'expiringSoon' => $expiringSoon,
            'urls' => $this->bloodBankListUrls(),
        ]);
    }

    public function new(Request $request): Response
    {
        return $this->renderRequestListPage($request, 'new', 'BloodBanks/New');
    }

    public function approved(Request $request): Response
    {
        return $this->renderRequestListPage($request, 'approved', 'BloodBanks/Approved');
    }

    public function rejected(Request $request): Response
    {
        return $this->renderRequestListPage($request, 'rejected', 'BloodBanks/Rejected');
    }

    public function delivered(Request $request): Response
    {
        return $this->renderRequestListPage($request, 'delivered', 'BloodBanks/Delivered');
    }

    public function movements(Request $request): Response
    {
        $this->authorizeBloodBankMenu();

        $branchId = $this->bloodBankBranchId();

        $query = BloodStockMovement::query()
            ->with(['bloodUnit:id,bag_number', 'user:id,name'])
            ->whereHas('bloodUnit', fn ($q) => $q->when($branchId, fn ($inner) => $inner->where('branch_id', $branchId)));

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        $this->applyPersianDateFromFilter($query, 'created_at', $request->input('from'));
        $this->applyPersianDateToFilter($query, 'created_at', $request->input('to'));

        if ($request->filled('bag_number')) {
            $query->whereHas('bloodUnit', fn ($q) => $q->where('bag_number', 'like', '%'.$request->bag_number.'%'));
        }

        $paginator = $this->paginateQuery($query->orderByDesc('created_at'), $request, 40);
        $from = $paginator->firstItem();

        return Inertia::render('BloodBanks/Movements', [
            'movements' => [
                'data' => collect($paginator->items())->map(function ($movement, int $index) use ($from) {
                    return [
                        'id' => $movement->id,
                        'row_number' => $from ? $from + $index : null,
                        'movement_type' => $movement->movement_type,
                        'bag_number' => $movement->bloodUnit?->bag_number,
                        'user_name' => $movement->user?->name,
                        'notes' => $movement->notes,
                        'created_at' => $movement->created_at ? verta($movement->created_at)->format('Y-m-d H:i') : null,
                    ];
                })->values()->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $this->collectFilters($request, ['movement_type', 'from', 'to', 'bag_number', 'per_page']),
            'urls' => [
                'current' => route('react.blood-banks.movements'),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    public function report(Request $request): Response
    {
        $this->authorizeBloodBankMenu();

        $items = [];
        if ($request->boolean('search')) {
            $items = $this->reportItems($request);
        }

        $branchId = $this->bloodBankBranchId();

        return Inertia::render('BloodBanks/Report', [
            'items' => $items,
            'filters' => $this->collectFilters($request, [
                'patient_name',
                'status',
                'group',
                'rh',
                'department_id',
                'from',
                'to',
            ]),
            'filterOptions' => [
                'departments' => Department::query()
                    ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                    ->orderBy('name')
                    ->get(['id', 'name'])
                    ->map(fn (Department $d) => ['id' => $d->id, 'name' => $d->name])
                    ->values()
                    ->all(),
                'bloodGroups' => ['A', 'B', 'AB', 'O'],
                'statuses' => ['new', 'approved', 'rejected', 'delivered'],
            ],
            'urls' => [
                'current' => route('react.blood-banks.report'),
                'export' => route('blood_banks.export-report'),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    public function show(Request $request, BloodBank $bloodBank): Response
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);

        app(BloodBankStockService::class)->archiveExpiredUnits((int) $bloodBank->branch_id, $request->user()->id);

        $bloodBank->load([
            'patient:id,name,father_name,id_card,phone',
            'department:id,name',
            'receiverDepartment:id,name',
            'receiverNurse:id,first_name,last_name',
            'createdBy:id,name',
            'bloodUnits',
            'appointment:id',
            'patientSamples.collectedBy:id,name',
            'crossmatches.bloodUnit:id,bag_number,expires_at,status',
            'crossmatches.patientSample:id,sample_id',
            'crossmatches.testedBy:id,name',
            'crossmatches.overriddenBy:id,name',
            'bloodCheckRecord.verifiedBy:id,name',
        ]);

        $reservedUnitIds = $bloodBank->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->reserved_at))
            ->pluck('id')
            ->values()
            ->all();

        $requestedQty = $bloodBank->orderedUnitsForWorkflow();
        $quantityInferredFromVolumeMl = $bloodBank->bloodCheckRecord === null
            && (int) $bloodBank->quantity > (int) config('blood_bank.max_unit_order_before_volume_assumption', 100)
            && BloodBank::normalizeRawQuantityToUnits((int) $bloodBank->quantity) !== (int) $bloodBank->quantity;

        $reservedCompatibleQty = $bloodBank->crossmatches
            ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
            ->filter(fn ($cx) => in_array($cx->blood_unit_id, $reservedUnitIds, true))
            ->count();

        $issuedQty = $bloodBank->bloodUnits
            ->filter(fn ($u) => ! is_null($u->pivot?->issued_at))
            ->count();

        $remainingQty = max(0, $requestedQty - $issuedQty);

        $orderedVolumeMl = $bloodBank->orderedVolumeMl();
        $issuedVolumeMl = $bloodBank->issuedVolumeMl();
        $remainingVolumeMl = $bloodBank->remainingVolumeMl();
        $reservedCompatibleVolumeMl = $bloodBank->reservedCompatibleVolumeMl($reservedUnitIds);

        $workflow = $this->buildWorkflowState($bloodBank, $remainingVolumeMl, $reservedCompatibleVolumeMl);

        $user = $request->user();
        $bloodCheck = $bloodBank->bloodCheck();
        $crossmatchesByUnit = $bloodBank->crossmatches->keyBy('blood_unit_id');

        $availableUnits = collect();
        $inventoryPreviewUnits = collect();

        if ($bloodBank->status === 'approved') {
            $stockService = app(BloodBankStockService::class);
            $availableUnits = $stockService->crossmatchCandidateUnits($bloodBank);

            $inventoryPreviewUnits = BloodUnit::query()
                ->where('branch_id', $bloodBank->branch_id)
                ->whereIn('status', ['available', 'reserved'])
                ->where('expires_at', '>', now())
                ->when(
                    ($bloodBank->bloodCheckRecord?->component_type ?: $bloodBank->type),
                    fn ($q, $type) => $q->where('component_type', $type),
                )
                ->where(function ($q) {
                    $q->whereHas('test', fn ($t) => $t->whereIn('overall_status', ['passed', 'pending']))
                        ->orWhereDoesntHave('test');
                })
                ->with('test')
                ->orderBy('expires_at')
                ->limit(12)
                ->get();
        }

        $hasCrossmatchFlow = $bloodBank->crossmatches->isNotEmpty();
        $deliverableUnitIds = $bloodBank->crossmatches
            ->filter(fn ($cx) => in_array($cx->status, ['compatible', 'overridden'], true))
            ->filter(fn ($cx) => in_array($cx->blood_unit_id, $reservedUnitIds, true))
            ->pluck('blood_unit_id')
            ->values()
            ->all();

        $bcr = $bloodBank->bloodCheckRecord;

        return Inertia::render('BloodBanks/Show', [
            'bloodRequest' => $this->transformBloodRequestDetail(
                $bloodBank,
                $requestedQty,
                $reservedCompatibleQty,
                $issuedQty,
                $remainingQty,
                $orderedVolumeMl,
                $issuedVolumeMl,
                $remainingVolumeMl,
                $reservedCompatibleVolumeMl,
                $quantityInferredFromVolumeMl,
                $workflow,
                $reservedUnitIds,
            ),
            'workflowData' => [
                'availableUnits' => $availableUnits
                    ->map(fn (BloodUnit $unit) => $this->transformWorkflowAvailableUnit(
                        $bloodBank,
                        $unit,
                        $bloodCheck,
                        $crossmatchesByUnit,
                        $reservedUnitIds,
                    ))
                    ->values()
                    ->all(),
                'inventoryPreviewUnits' => $inventoryPreviewUnits
                    ->map(fn (BloodUnit $unit) => $this->transformWorkflowInventoryPreviewUnit(
                        $unit,
                        $crossmatchesByUnit,
                    ))
                    ->values()
                    ->all(),
                'hasCrossmatchFlow' => $hasCrossmatchFlow,
                'deliverableUnitIds' => $deliverableUnitIds,
                'crossmatchResultValues' => BloodCrossmatch::RESULT_VALUES,
                'bloodComponentTypes' => BloodCheckRecord::COMPONENT_TYPES,
                'bloodCheckForm' => [
                    'abo_group' => $bcr?->abo_group ?? $bloodBank->group ?? 'O',
                    'rh' => $bcr?->rh ?? $bloodBank->rh ?? '+',
                    'component_type' => $bcr?->component_type ?? $bloodBank->type ?? 'RBC',
                    'quantity' => $bcr && (int) $bcr->quantity >= 1
                        ? BloodBank::normalizeRawQuantityToUnits((int) $bcr->quantity)
                        : $requestedQty,
                    'patient_typed_group' => $bcr?->patient_typed_group ?? '',
                    'patient_typed_rh' => $bcr?->patient_typed_rh ?? '',
                    'notes' => $bcr?->notes ?? '',
                ],
                'deliveryDefaults' => [
                    'receiver_department_id' => $bloodBank->receiver_department_id,
                    'receiver_nurse_id' => $bloodBank->receiver_nurse_id,
                ],
                'defaultUnitVolumeMl' => $bloodBank->defaultUnitVolumeMl(),
            ],
            'receiverDepartments' => $this->bloodRequestFilterOptions()['departments'],
            'permissions' => [
                'approve' => $bloodBank->status === 'new',
                'reject' => $bloodBank->status !== 'delivered' && $bloodBank->status !== 'rejected',
                'deliver' => $bloodBank->status === 'approved',
                'manageCrossmatch' => $user->can('receive-blood-units') || $user->can('manage-blood-inventory'),
                'manageInventory' => $user->can('manage-blood-inventory'),
            ],
            'urls' => [
                'back' => $this->backUrlForBloodRequest($bloodBank),
                'approve' => route('react.blood-banks.approve', $bloodBank),
                'reject' => route('react.blood-banks.reject', $bloodBank),
                'deliver' => route('react.blood-banks.deliver', $bloodBank),
                'bloodCheck' => route('react.blood-banks.blood-check.store', $bloodBank),
                'storeSample' => route('react.blood-banks.crossmatch.samples.store', $bloodBank),
                'inventory' => route('react.blood-banks.inventory', [
                    'status' => 'available',
                    'blood_group' => $bloodBank->group,
                    'rh' => $bloodBank->rh,
                    'component_type' => $bloodBank->type,
                ]),
                'legacyInventoryShow' => url('/blood_banks/inventory'),
                'nursesByDepartment' => route('react.blood-banks.nurses-by-department', ['department' => '__DEPARTMENT__']),
                ...$this->bloodBankListUrls(),
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function approve(BloodBank $bloodBank): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        abort_unless($bloodBank->status === 'new', 422);

        $bloodBank->approve();

        return redirect()
            ->route('react.blood-banks.show', $bloodBank)
            ->with('success', localize('global.approved'));
    }

    public function reject(Request $request, BloodBank $bloodBank): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        abort_if(in_array($bloodBank->status, ['delivered', 'rejected'], true), 422);

        $validated = $request->validate([
            'reject_reason' => 'nullable|string|max:2000',
        ]);

        $bloodBank->reject();
        $bloodBank->update(['reject_reason' => $validated['reject_reason'] ?? null]);

        return redirect()
            ->route('react.blood-banks.rejected')
            ->with('success', localize('global.rejected'));
    }

    public function deliver(Request $request, BloodBank $bloodBank): RedirectResponse
    {
        $this->authorizeBloodBankMenu();
        $this->ensureBloodRequestBranch($bloodBank);
        abort_unless($bloodBank->status === 'approved', 422);

        $validated = $request->validate([
            'receiver_department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')->where(fn ($q) => $q->where('branch_id', $request->user()->branch_id)),
            ],
            'receiver_nurse_id' => ['required', 'integer', 'exists:nurses,id'],
            'unit_ids' => 'nullable|array',
            'unit_ids.*' => 'integer|exists:blood_units,id',
            'mark_complete' => 'nullable|boolean',
        ]);

        $bloodBank->receiver_department_id = $validated['receiver_department_id'];
        $bloodBank->receiver_nurse_id = $validated['receiver_nurse_id'];

        if (! BloodUnit::where('branch_id', $bloodBank->branch_id)->exists()) {
            $bloodBank->deliver();

            return redirect()
                ->back()
                ->with('success', localize('global.blood_request_delivered_successfully'));
        }

        $unitIds = array_values(array_filter(array_map('intval', $validated['unit_ids'] ?? [])));

        try {
            app(BloodBankStockService::class)->deliverRequest(
                $bloodBank,
                count($unitIds) > 0 ? $unitIds : null,
                $request->boolean('mark_complete'),
            );
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->back()
            ->with('success', localize('global.blood_request_delivered_successfully'));
    }

    public function nursesByDepartment(Department $department): \Illuminate\Http\JsonResponse
    {
        $this->authorizeBloodBankMenu();

        if ((int) $department->branch_id !== (int) request()->user()->branch_id) {
            abort(404);
        }

        $nurses = Nurse::query()
            ->where('department_id', $department->id)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name']);

        return response()->json([
            'nurses' => $nurses->map(fn (Nurse $n) => [
                'id' => $n->id,
                'name' => trim($n->first_name.' '.$n->last_name),
            ])->values(),
        ]);
    }

    private function renderRequestListPage(Request $request, string $status, string $page): Response
    {
        $this->authorizeBloodBankMenu();

        $query = BloodBank::query()
            ->with(['patient:id,name,father_name,id_card', 'department:id,name'])
            ->when($this->bloodBankBranchId(), fn ($q, $branchId) => $q->where('branch_id', $branchId))
            ->where('status', $status);

        $this->applyBloodRequestListFilters($query, $request);

        $paginator = $this->paginateQuery($query, $request);
        $from = $paginator->firstItem();

        return Inertia::render($page, [
            'bloodRequests' => [
                'data' => collect($paginator->items())
                    ->map(fn (BloodBank $item, int $index) => $this->transformBloodRequestListItem(
                        $item,
                        $from ? $from + $index : null,
                    ))
                    ->values()
                    ->all(),
                'links' => $paginator->linkCollection()->toArray(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
            'filters' => $this->collectFilters($request, $this->bloodRequestListFilterKeys()),
            'filterOptions' => $this->bloodRequestFilterOptions(),
            'urls' => [
                'current' => $request->url(),
                ...$this->bloodBankListUrls(),
            ],
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function reportItems(Request $request): array
    {
        $branchId = $this->bloodBankBranchId();

        $query = DB::table('blood_banks as bb')
            ->leftJoin('patients as p', 'bb.patient_id', '=', 'p.id')
            ->leftJoin('departments as d', 'bb.department_id', '=', 'd.id')
            ->leftJoin('branches as b', 'bb.branch_id', '=', 'b.id')
            ->leftJoin('appointments as apt', 'bb.appointment_id', '=', 'apt.id')
            ->select(
                'bb.id',
                'p.name as patient_name',
                'd.name as department_name',
                'b.name as branch_name',
                'bb.status',
                'bb.group',
                'bb.rh',
                'apt.id as appointment_id',
            )
            ->when($branchId, fn ($q) => $q->where('bb.branch_id', $branchId));

        if ($request->filled('patient_name')) {
            $query->where('p.name', 'like', '%'.$request->patient_name.'%');
        }

        if ($request->filled('status')) {
            $query->where('bb.status', $request->status);
        }

        if ($request->filled('group')) {
            $query->where('bb.group', $request->group);
        }

        if ($request->filled('rh')) {
            $query->where('bb.rh', $request->rh);
        }

        if ($request->filled('department_id')) {
            $query->where('bb.department_id', $request->department_id);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $from = PersianDateParser::queryDate($request->from);
            $to = PersianDateParser::queryDate($request->to);
            if ($from !== null && $to !== null) {
                $query->whereBetween('bb.created_at', [
                    Carbon::instance($from)->startOfDay(),
                    Carbon::instance($to)->endOfDay(),
                ]);
            }
        }

        return $query->orderByDesc('bb.id')->get()->map(fn ($item) => [
            'id' => $item->id,
            'patient_name' => $item->patient_name,
            'department_name' => $item->department_name,
            'branch_name' => $item->branch_name,
            'status' => $item->status,
            'group' => $item->group,
            'rh' => $item->rh,
            'appointment_id' => $item->appointment_id,
        ])->values()->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function transformBloodUnitSummary(BloodUnit $unit): array
    {
        return [
            'id' => $unit->id,
            'bag_number' => $unit->bag_number,
            'blood_group' => $unit->blood_group,
            'rh' => $unit->rh,
            'component_type' => $unit->component_type,
            'expires_at' => $unit->expires_at ? verta($unit->expires_at)->format('Y-m-d') : null,
            'urls' => [
                'show' => route('react.blood-banks.inventory.show', $unit),
            ],
        ];
    }

    /**
     * @param  list<int>  $reservedUnitIds
     * @return array<string, mixed>
     */
    private function transformBloodRequestDetail(
        BloodBank $bloodBank,
        int $requestedQty,
        int $reservedCompatibleQty,
        int $issuedQty,
        int $remainingQty,
        int $orderedVolumeMl,
        int $issuedVolumeMl,
        int $remainingVolumeMl,
        int $reservedCompatibleVolumeMl,
        bool $quantityInferredFromVolumeMl,
        array $workflow,
        array $reservedUnitIds,
    ): array {
        $orderParts = $bloodBank->orderQuantityDisplayParts();

        return [
            'id' => $bloodBank->id,
            'status' => $bloodBank->status,
            'group' => $bloodBank->group,
            'rh' => $bloodBank->rh,
            'type' => $bloodBank->type,
            'quantity' => $bloodBank->quantity,
            'hemoglobin' => $bloodBank->hemoglobin,
            'hematocrit' => $bloodBank->hematocrit,
            'factor' => $bloodBank->factor,
            'reject_reason' => $bloodBank->reject_reason,
            'created_at' => $bloodBank->created_at ? verta($bloodBank->created_at)->format('Y-m-d H:i') : null,
            'patient' => [
                'name' => $bloodBank->patient?->name,
                'father_name' => $bloodBank->patient?->father_name,
                'id_card' => $bloodBank->patient?->id_card,
                'phone' => $bloodBank->patient?->phone,
            ],
            'department_name' => $bloodBank->department?->name,
            'receiver_department_name' => $bloodBank->receiverDepartment?->name,
            'receiver_nurse_name' => $bloodBank->receiverNurse?->full_name,
            'created_by_name' => $bloodBank->createdBy?->name,
            'appointment_id' => $bloodBank->appointment_id,
            'requested_qty' => $requestedQty,
            'reserved_compatible_qty' => $reservedCompatibleQty,
            'issued_qty' => $issuedQty,
            'remaining_qty' => $remainingQty,
            'ordered_volume_ml' => $orderedVolumeMl,
            'issued_volume_ml' => $issuedVolumeMl,
            'remaining_volume_ml' => $remainingVolumeMl,
            'reserved_compatible_volume_ml' => $reservedCompatibleVolumeMl,
            'uses_volume_ml_tracking' => $bloodBank->usesVolumeMlTracking(),
            'quantity_inferred_from_volume_ml' => $quantityInferredFromVolumeMl,
            'order_quantity_display' => $orderParts,
            'workflow' => $workflow,
            'blood_check' => $bloodBank->bloodCheckRecord ? [
                'abo_group' => $bloodBank->bloodCheckRecord->abo_group,
                'rh' => $bloodBank->bloodCheckRecord->rh,
                'component_type' => $bloodBank->bloodCheckRecord->component_type,
                'quantity' => $bloodBank->bloodCheckRecord->quantity,
                'notes' => $bloodBank->bloodCheckRecord->notes,
                'patient_typed_group' => $bloodBank->bloodCheckRecord->patient_typed_group,
                'patient_typed_rh' => $bloodBank->bloodCheckRecord->patient_typed_rh,
                'verified_at' => $bloodBank->bloodCheckRecord->verified_at?->format('Y-m-d H:i'),
                'verified_by_name' => $bloodBank->bloodCheckRecord->verifiedBy?->name,
            ] : null,
            'patient_samples' => $bloodBank->patientSamples->map(fn ($sample) => [
                'id' => $sample->id,
                'sample_id' => $sample->sample_id,
                'collected_at' => $sample->collected_at ? verta($sample->collected_at)->format('Y-m-d H:i') : null,
                'collected_by_name' => $sample->collectedBy?->name,
                'notes' => $sample->notes,
            ])->values()->all(),
            'crossmatches' => $bloodBank->crossmatches->map(fn ($cx) => [
                'id' => $cx->id,
                'blood_unit_id' => $cx->blood_unit_id,
                'bag_number' => $cx->bloodUnit?->bag_number,
                'major_result' => $cx->major_result,
                'minor_result' => $cx->minor_result,
                'status' => $cx->status,
                'is_reserved' => in_array($cx->blood_unit_id, $reservedUnitIds, true),
                'tested_at' => $cx->tested_at?->format('Y-m-d H:i'),
                'tested_by_name' => $cx->testedBy?->name,
            ])->values()->all(),
            'issued_units' => $bloodBank->bloodUnits
                ->filter(fn ($u) => ! is_null($u->pivot?->issued_at))
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'bag_number' => $u->bag_number,
                    'volume_ml' => $u->volume_ml,
                    'expires_at' => $u->expires_at ? verta($u->expires_at)->format('Y-m-d H:i') : null,
                    'issued_at' => $u->pivot->issued_at
                        ? verta($u->pivot->issued_at)->format('Y-m-d H:i')
                        : null,
                    'urls' => [
                        'show' => route('react.blood-banks.inventory.show', $u),
                    ],
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * @param  list<int>  $reservedUnitIds
     * @return array<string, mixed>
     */
    private function transformWorkflowAvailableUnit(
        BloodBank $bloodBank,
        BloodUnit $unit,
        \App\Blood\BloodCheck $bloodCheck,
        \Illuminate\Support\Collection $crossmatchesByUnit,
        array $reservedUnitIds,
    ): array {
        $cx = $crossmatchesByUnit->get($unit->id);

        return [
            'id' => $unit->id,
            'bag_number' => $unit->bag_number,
            'blood_group' => $unit->blood_group,
            'rh' => $unit->rh,
            'component_type' => $unit->component_type,
            'volume_ml' => $unit->volume_ml,
            'status' => $unit->status,
            'screening_status' => $unit->test?->overall_status ?? 'pending',
            'expires_at' => $unit->expires_at ? verta($unit->expires_at)->format('Y-m-d H:i') : null,
            'auto_abo_rh_compatible' => $bloodCheck->isAboRhAutoCompatibleWithBloodUnit($unit),
            'can_reserve' => $unit->status === 'available' && ($unit->test?->overall_status ?? 'pending') === 'passed',
            'is_reserved' => in_array($unit->id, $reservedUnitIds, true),
            'crossmatch' => $cx ? [
                'id' => $cx->id,
                'major_result' => $cx->major_result,
                'minor_result' => $cx->minor_result,
                'status' => $cx->status,
                'auto_reason' => $cx->auto_reason,
                'patient_sample_id' => $cx->patient_sample_id,
                'urls' => [
                    'reserve' => route('react.blood-banks.crossmatch.reserve', [$bloodBank, $cx]),
                    'override' => route('react.blood-banks.crossmatch.override', [$bloodBank, $cx]),
                ],
            ] : null,
            'urls' => [
                'saveCrossmatch' => route('react.blood-banks.crossmatch.save', [$bloodBank, $unit]),
                'unreserve' => route('react.blood-banks.crossmatch.unreserve', [$bloodBank, $unit]),
                'inventoryShow' => route('react.blood-banks.inventory.show', $unit),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformWorkflowInventoryPreviewUnit(
        BloodUnit $unit,
        \Illuminate\Support\Collection $crossmatchesByUnit,
    ): array {
        $cx = $crossmatchesByUnit->get($unit->id);

        return [
            'id' => $unit->id,
            'bag_number' => $unit->bag_number,
            'blood_group' => $unit->blood_group,
            'rh' => $unit->rh,
            'component_type' => $unit->component_type,
            'expires_at' => $unit->expires_at ? verta($unit->expires_at)->format('Y-m-d H:i') : null,
            'screening_status' => $unit->test?->overall_status ?? 'pending',
            'crossmatch_status' => $cx?->status,
            'urls' => [
                'show' => route('react.blood-banks.inventory.show', $unit),
            ],
        ];
    }

    /**
     * @return array{current_step: int|null, steps: list<array{number: int, done: bool, current: bool}>}
     */
    private function buildWorkflowState(BloodBank $bloodBank, int $remainingVolumeMl, int $reservedCompatibleVolumeMl): array
    {
        if ($bloodBank->status !== 'approved') {
            return ['current_step' => null, 'steps' => []];
        }

        $step1Done = true;
        $step2Done = $bloodBank->patientSamples->isNotEmpty();
        $step3Done = $remainingVolumeMl < 1 || $reservedCompatibleVolumeMl >= $remainingVolumeMl;
        $currentStep = null;

        if (! $step2Done) {
            $currentStep = 2;
        } elseif (! $step3Done) {
            $currentStep = 3;
        } else {
            $currentStep = 4;
        }

        $steps = [];
        foreach ([1, 2, 3, 4] as $sn) {
            $done = ($sn === 1 && $step1Done)
                || ($sn === 2 && $step2Done)
                || ($sn === 3 && $step3Done);
            $steps[] = [
                'number' => $sn,
                'done' => $done,
                'current' => $currentStep === $sn,
            ];
        }

        return [
            'current_step' => $currentStep,
            'steps' => $steps,
        ];
    }
}
