<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ManagesDepotAccess;
use App\Http\Controllers\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Http\Requests\Depot\UpdateDepotRequestRequest;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Models\DepotTransaction;
use App\Models\Pharmacy;
use App\Services\DepotRequestService;
use App\Services\DepotRequestSourceResolver;
use App\Support\DepotRequestContext;
use App\Support\DepotRolePermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class DepotRequestController extends Controller
{
    use ManagesDepotAccess;
    use ProvidesDepotFormData;

    public function __construct(
        private readonly DepotRequestService $requestService,
        private readonly DepotRequestSourceResolver $sourceResolver,
    ) {
    }

    public function index(Request $request): View
    {
        $this->authorizeDepotRequestView();

        $query = DepotRequest::with([
            'requestingDepot',
            'pharmacy',
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'requestedBy',
            'approvedBy',
            'fulfilledBy',
        ])->withCount('items');

        $this->applyRequestFilters($query, $request);

        $requests = $query->latest('id')->paginate(15)->appends($request->query());

        return view('pages.depots.requests.index', array_merge($this->bladeFormOptions(), [
            'requests' => $requests,
            'statuses' => DepotRequest::statuses(),
            'permissions' => $this->depotRequestPermissions(),
        ]));
    }

    public function create(Request $request): View
    {
        $this->authorizeDepotRequestCreate();

        $user = $request->user();
        $destination = $request->query('destination', '');
        $defaultPharmacyId = (string) $request->query('pharmacy_id', '');

        if ($destination !== 'pharmacy' && $this->isPharmacyRequestContext()) {
            $destination = 'pharmacy';
        }

        if ($destination === 'pharmacy' && $defaultPharmacyId === '' && $user) {
            $defaultPharmacyId = (string) ($user->activePharmacies->first()?->id ?? '');
        }

        $defaultRequestingDepotId = (string) $request->query('requesting_depot_id', '');
        if ($destination !== 'pharmacy' && $defaultRequestingDepotId === '' && $user) {
            $defaultRequestingDepotId = (string) ($user->activeDepots->first()?->id ?? '');
        }

        $hintedSourceDepotId = $this->validatedSourceDepotHint($request);
        $requestingDepotId = $destination !== 'pharmacy' && $defaultRequestingDepotId !== ''
            ? (int) $defaultRequestingDepotId
            : null;

        $sourceDepotOptions = $requestingDepotId
            ? $this->sourceResolver->sourceOptionsFor($requestingDepotId, $user)
            : [];

        $defaultSourceDepotId = $hintedSourceDepotId
            ?? ($requestingDepotId ? $this->sourceResolver->defaultSourceDepotId($requestingDepotId, $user) : null);

        return view('pages.depots.requests.create', array_merge($this->bladeFormOptions(), [
            'defaults' => [
                'destination_type' => $destination === 'pharmacy' ? 'pharmacy' : 'depot',
                'requesting_depot_id' => $defaultRequestingDepotId,
                'pharmacy_id' => $defaultPharmacyId,
                'source_depot_id' => $defaultSourceDepotId ? (string) $defaultSourceDepotId : '',
            ],
            'sourceDepotOptions' => $sourceDepotOptions,
            'lockRequestingDepot' => $destination !== 'pharmacy' && $requestingDepotId !== null,
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'userPharmacies' => $user?->activePharmacies ?? collect(),
            'depotRequest' => null,
            'formItems' => old('items', [['medicine_id' => '', 'tool_id' => '', 'unit_id' => '', 'quantity' => 1, 'batch_number' => '']]),
        ]));
    }

    public function store(StoreDepotRequestRequest $request): RedirectResponse
    {
        $this->authorizeDepotRequestCreate();

        $data = $request->validated();
        $this->authorizeNewDepotRequestData($data);

        $user = $request->user();
        $hintedSourceDepotId = ! empty($data['source_depot_id'])
            ? (int) $data['source_depot_id']
            : session()->pull('depot_request_source_hint');
        $sourceDepotId = $this->sourceResolver->resolve($data, $user, $hintedSourceDepotId);

        $depotRequest = DB::transaction(function () use ($data, $sourceDepotId) {
            $depotRequest = DepotRequest::create([
                'requesting_depot_id' => $data['requesting_depot_id'] ?? null,
                'pharmacy_id' => $data['pharmacy_id'] ?? null,
                'source_depot_id' => $sourceDepotId,
                'notes' => $data['notes'] ?? null,
                'status' => DepotRequest::STATUS_DRAFT,
                'requested_by' => Auth::id(),
            ]);

            $this->requestService->syncItems($depotRequest, $data['items']);

            return $depotRequest;
        });

        if ($request->boolean('submit_now')) {
            $this->requestService->submit($depotRequest);
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_created_successfully.'));
    }

    public function show(DepotRequest $depotRequest): View
    {
        $this->authorizeDepotRequestView();
        abort_unless($this->userCanAccessDepotRequest($depotRequest), 403);

        $depotRequest->load([
            'requestingDepot.branch',
            'requestingDepot.department',
            'requestingDepot.pharmacy',
            'pharmacy',
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'items.unit',
            'items.depotTransaction',
            'requestedBy',
            'approvedBy',
            'fulfilledBy',
            'transactions',
            'statusLogs.user',
        ]);

        return view('pages.depots.requests.show', [
            'depotRequest' => $depotRequest,
            'permissions' => $this->requestActionPermissions($depotRequest),
            'workflowSteps' => DepotRequest::WORKFLOW_STEPS,
            'context' => DepotRequestContext::forRequest($depotRequest),
        ]);
    }

    public function print(DepotRequest $depotRequest): View
    {
        $this->authorizeDepotRequestView();
        abort_unless($this->userCanAccessDepotRequest($depotRequest), 403);

        $depotRequest->load([
            'requestingDepot.branch',
            'requestingDepot.department',
            'requestingDepot.pharmacy',
            'pharmacy',
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'items.unit',
            'requestedBy',
        ]);

        $context = DepotRequestContext::forRequest($depotRequest);
        $unitDepotId = $depotRequest->isPharmacyRequest()
            ? Depot::query()->where('pharmacy_id', $depotRequest->pharmacy_id)->value('id')
            : $depotRequest->requesting_depot_id;

        $lines = $depotRequest->items->map(function (DepotRequestItem $item) use ($depotRequest, $unitDepotId) {
            $available = null;
            if ($unitDepotId) {
                if ($item->medicine_id) {
                    $available = DepotTransaction::availableStock((int) $unitDepotId, (int) $item->medicine_id);
                } elseif ($item->tool_id) {
                    $available = DepotTransaction::availableToolStock((int) $unitDepotId, (int) $item->tool_id);
                }
            }

            $partNumber = $item->batch_number;
            if ($item->tool_id && $item->tool) {
                $partNumber = trim(($item->tool->code ?? '') . ($item->batch_number ? ' / '.$item->batch_number : ''));
            }

            $isFulfilled = $depotRequest->status === DepotRequest::STATUS_FULFILLED;

            return [
                'item_name' => $item->itemName(),
                'unit_name' => $item->unit?->name ?? '—',
                'available_quantity' => $available,
                'requested_quantity' => $item->quantity,
                'delivery_date' => $depotRequest->fulfilled_at?->format('Y-m-d') ?? $depotRequest->created_at?->format('Y-m-d'),
                'part_number' => $partNumber ?: '—',
                'document_number' => $depotRequest->request_number,
                'solar_date' => $depotRequest->created_at ? verta($depotRequest->created_at)->format('Y-m-d') : '—',
                'cssk_unfilled' => $isFulfilled ? '' : $item->quantity,
                'cssk_filled' => $isFulfilled ? $item->quantity : '',
                'fsd_unfilled' => '',
                'fsd_filled' => '',
                'nsd_unfilled' => '',
                'nsd_filled' => '',
            ];
        });

        $emptyRows = max(0, 8 - $lines->count());

        return view('pages.depots.requests.print_form14', [
            'depotRequest' => $depotRequest,
            'context' => $context,
            'lines' => $lines,
            'emptyRows' => $emptyRows,
            'supportedUnit' => $depotRequest->sourceDepot?->name ?? '—',
            'solarDate' => $depotRequest->created_at ? verta($depotRequest->created_at)->format('Y-m-d') : '—',
        ]);
    }

    public function edit(DepotRequest $depotRequest): View
    {
        $this->authorizeDepotRequestCreate();
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $depotRequest->load([
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'items.unit',
        ]);

        $user = request()->user();
        $sourceDepotOptions = $depotRequest->requesting_depot_id
            ? $this->sourceResolver->sourceOptionsFor((int) $depotRequest->requesting_depot_id, $user)
            : [];

        $formItems = old('items', $depotRequest->items->map(fn (DepotRequestItem $item) => [
            'medicine_id' => $item->medicine_id,
            'tool_id' => $item->tool_id,
            'unit_id' => $item->unit_id,
            'quantity' => $item->quantity,
            'batch_number' => $item->batch_number,
        ])->values()->all());

        if ($formItems === []) {
            $formItems = [['medicine_id' => '', 'tool_id' => '', 'unit_id' => '', 'quantity' => 1, 'batch_number' => '']];
        }

        return view('pages.depots.requests.edit', array_merge($this->bladeFormOptions(), [
            'depotRequest' => $depotRequest,
            'defaults' => [
                'destination_type' => $depotRequest->isPharmacyRequest() ? 'pharmacy' : 'depot',
                'requesting_depot_id' => (string) ($depotRequest->requesting_depot_id ?? ''),
                'pharmacy_id' => (string) ($depotRequest->pharmacy_id ?? ''),
                'source_depot_id' => (string) $depotRequest->source_depot_id,
            ],
            'sourceDepotOptions' => $sourceDepotOptions,
            'lockRequestingDepot' => false,
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'userPharmacies' => $user?->activePharmacies ?? collect(),
            'formItems' => $formItems,
        ]));
    }

    public function update(UpdateDepotRequestRequest $request, DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotRequestCreate();
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $data = $request->validated();
        $this->authorizeNewDepotRequestData($data);

        $sourceDepotId = $this->sourceResolver->resolve(
            $data,
            $request->user(),
            $depotRequest->source_depot_id,
        );

        DB::transaction(function () use ($depotRequest, $data, $sourceDepotId) {
            $depotRequest->update([
                'requesting_depot_id' => $data['requesting_depot_id'] ?? null,
                'pharmacy_id' => $data['pharmacy_id'] ?? null,
                'source_depot_id' => $sourceDepotId,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->requestService->syncItems($depotRequest, $data['items']);
        });

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.updated_successfully.'));
    }

    public function submit(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotRequestCreate();
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        try {
            $this->requestService->submit($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_submitted_for_approval.'));
    }

    public function approve(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE);

        try {
            $this->requestService->approve($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_approved.'));
    }

    public function reject(Request $request, DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE);

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->requestService->reject($depotRequest, $data['rejection_reason']);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_rejected.'));
    }

    public function fulfill(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_FULFILL);

        try {
            $this->requestService->fulfill($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest->fresh())
            ->with('success', localize('global.depot.request_fulfilled_and_transferred.'));
    }

    public function cancel(DepotRequest $depotRequest): RedirectResponse
    {
        $user = request()->user();
        $canCancel = match ($depotRequest->status) {
            DepotRequest::STATUS_DRAFT => $depotRequest->isPharmacyRequest()
                ? $this->canAccessPharmacyRequests($user)
                : $this->userCanDepotAction(
                    DepotRolePermissions::ACTION_REQUEST_CREATE,
                    $depotRequest->requesting_depot_id,
                ),
            DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED => $this->userCanProcessDepotRequest(
                $depotRequest,
                DepotRolePermissions::ACTION_REQUEST_APPROVE,
                $user,
            ),
            default => false,
        };

        abort_unless($canCancel, 403);
        abort_unless($this->userCanAccessDepotRequest($depotRequest, $user), 403);

        try {
            $this->requestService->cancel($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_cancelled.'));
    }

    private function authorizeNewDepotRequestData(array $data): void
    {
        $user = request()->user();

        if ($this->isDepotSystemAdmin($user) || $user?->hasSpatieDepotPermission(DepotRolePermissions::ACTION_REQUEST_CREATE)) {
            return;
        }

        if (! empty($data['pharmacy_id'])) {
            $allowedPharmacyIds = $this->allowedPharmacyIdsForRequests($user);
            abort_unless(in_array((int) $data['pharmacy_id'], $allowedPharmacyIds, true), 403);

            return;
        }

        $requestingDepotId = (int) $data['requesting_depot_id'];
        $allowedRequestingIds = $user->allowedDepotIdsForAction(DepotRolePermissions::ACTION_REQUEST_CREATE);
        abort_unless(in_array($requestingDepotId, $allowedRequestingIds, true), 403);

        abort_unless(
            $user->canPerformDepotAction(
                $requestingDepotId,
                DepotRolePermissions::ACTION_REQUEST_CREATE,
            ),
            403
        );
    }

    private function authorizeDepotRequestView(): void
    {
        abort_unless($this->canViewDepotRequests(), 403);
    }

    private function authorizeDepotRequestCreate(): void
    {
        abort_unless($this->canCreateDepotRequest(), 403);
    }

    /**
     * @return array{submit: bool, approve: bool, reject: bool, fulfill: bool, cancel: bool, edit: bool}
     */
    private function requestActionPermissions(DepotRequest $depotRequest): array
    {
        $status = $depotRequest->status;
        $canCreate = $depotRequest->isPharmacyRequest()
            ? $this->canAccessPharmacyRequests()
            : $this->userCanDepotAction(
                DepotRolePermissions::ACTION_REQUEST_CREATE,
                $depotRequest->requesting_depot_id,
            );

        return [
            'edit' => $status === DepotRequest::STATUS_DRAFT && $canCreate,
            'submit' => $status === DepotRequest::STATUS_DRAFT && $canCreate,
            'approve' => $status === DepotRequest::STATUS_PENDING
                && $this->userCanProcessDepotRequest($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE),
            'reject' => $status === DepotRequest::STATUS_PENDING
                && $this->userCanProcessDepotRequest($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE),
            'fulfill' => $status === DepotRequest::STATUS_APPROVED
                && $this->userCanProcessDepotRequest($depotRequest, DepotRolePermissions::ACTION_REQUEST_FULFILL),
            'cancel' => ($status === DepotRequest::STATUS_DRAFT && $canCreate)
                || (
                    in_array($status, [DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED], true)
                    && $this->userCanProcessDepotRequest($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE)
                ),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotRequest>  $query
     */
    private function applyRequestFilters($query, Request $request): void
    {
        $user = $request->user();

        if ($user && ! $this->isDepotSystemAdmin($user)) {
            if ($this->isPharmacyRequestContext($user)) {
                $query->whereIn('pharmacy_id', $this->allowedPharmacyIdsForRequests($user));
            } else {
                $allowedDepotIds = $this->allowedDepotIds($user);
                $allowedPharmacyIds = $this->allowedPharmacyIdsForRequests($user);

                $query->where(function ($scoped) use ($allowedDepotIds, $allowedPharmacyIds) {
                    $scoped->whereIn('source_depot_id', $allowedDepotIds)
                        ->orWhereIn('requesting_depot_id', $allowedDepotIds)
                        ->orWhereIn('pharmacy_id', $allowedPharmacyIds);
                });
            }
        }

        if ($request->filled('requesting_depot_id')) {
            $query->where('requesting_depot_id', $request->requesting_depot_id);
        }
        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }
        if ($request->filled('destination_type')) {
            if ($request->destination_type === 'pharmacy') {
                $query->whereNotNull('pharmacy_id');
            } elseif ($request->destination_type === 'depot') {
                $query->whereNotNull('requesting_depot_id');
            }
        }
        if ($request->filled('source_depot_id')) {
            $query->where('source_depot_id', $request->source_depot_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('medicine_id')) {
            $query->whereHas('items', fn ($q) => $q->where('medicine_id', $request->medicine_id));
        }
        if ($request->filled('tool_id')) {
            $query->whereHas('items', fn ($q) => $q->where('tool_id', $request->tool_id));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('request_number', 'like', "%{$search}%")
                    ->orWhereHas('items.medicine', fn ($m) => $m->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('items.tool', fn ($t) => $t->where('name', 'like', "%{$search}%"));
            });
        }
    }

    private function validatedSourceDepotHint(Request $request): ?int
    {
        if (! $request->filled('source_depot_id')) {
            return null;
        }

        $sourceDepotId = (int) $request->query('source_depot_id');
        $user = $request->user();

        if ($user && ! $this->isDepotSystemAdmin($user) && ! $user->hasAnySpatieDepotPermission()) {
            abort_unless($user->hasDepotAccess($sourceDepotId), 403);
        }

        session(['depot_request_source_hint' => $sourceDepotId]);

        return $sourceDepotId;
    }

    /**
     * Eloquent collections for Blade selects (select2 + parent_depot mapping).
     *
     * @return array<string, mixed>
     */
    private function bladeFormOptions(): array
    {
        $options = $this->depotFormOptions();
        $user = request()->user();

        $depotsQuery = Depot::query()->where('is_active', true)->orderBy('name');
        if ($user && ! $user->hasRole(['super_admin', 'admin']) && ! $user->hasAnySpatieDepotPermission()) {
            $allowedIds = $user->activeDepots->pluck('id')->all();
            $depotsQuery->whereIn('id', $allowedIds ?: [0]);
        }

        $pharmaciesQuery = Pharmacy::query()->orderBy('name');
        if ($user && $this->isPharmacyRequestContext($user)) {
            $pharmaciesQuery->whereIn('id', $this->allowedPharmacyIdsForRequests($user) ?: [0]);
        }

        return [
            'depots' => $depotsQuery->get(),
            'pharmacies' => $pharmaciesQuery->get(['id', 'name']),
            'medicines' => \App\Models\Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(['id', 'name']),
            'tools' => \App\Models\Tool::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => \App\Models\Unit::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'formData' => $options,
        ];
    }
}
