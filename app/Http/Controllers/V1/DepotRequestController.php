<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Http\Requests\Depot\UpdateDepotRequestRequest;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Models\DepotTransaction;
use App\Services\DepotRequestService;
use App\Services\DepotRequestSourceResolver;
use App\Support\DepotRequestContext;
use App\Support\DepotRolePermissions;
use Hekmatinasser\Verta\Facades\Verta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class DepotRequestController extends Controller
{
    use ManagesDepotAccess;
    use PaginatesInertiaIndex;
    use ProvidesDepotFormData;

    private const FILTER_KEYS = [
        'search', 'requesting_depot_id', 'source_depot_id', 'pharmacy_id', 'destination_type', 'status', 'medicine_id', 'tool_id', 'date_from', 'date_to', 'per_page',
    ];

    public function __construct(
        private readonly DepotRequestService $requestService,
        private readonly DepotRequestSourceResolver $sourceResolver,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeDepotRequestView();

        $query = DepotRequest::with([
            'requestingDepot:id,name',
            'pharmacy:id,name',
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.tool:id,name',
            'requestedBy:id,name,last_name',
        ])->withCount('items');

        $this->applyRequestFilters($query, $request);

        $paginator = $this->paginateQuery($query->latest('id'), $request, 15);
        $options = $this->depotFormOptions();

        return Inertia::render('Depots/Requests/Index', [
            'requests' => $this->paginationPayload($paginator, fn (DepotRequest $item) => $this->transformListItem($item)),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'depots' => $options['activeDepots'],
                'pharmacies' => $options['pharmacies'],
                'medicines' => $options['medicines'],
                'tools' => $options['tools'],
                'statuses' => DepotRequest::statuses(),
                'destinationTypes' => ['depot', 'pharmacy'],
            ],
            'permissions' => $this->depotRequestPermissions(),
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.requests.index'),
                'create' => route('depots.requests.create'),
                'show' => url('/depots/requests'),
            ],
        ]);
    }

    public function create(Request $request): Response
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

        $requestingDepot = $requestingDepotId
            ? Depot::query()->find($requestingDepotId, ['id', 'name'])
            : null;

        return Inertia::render('Depots/Requests/Create', [
            'defaults' => [
                'destination_type' => $destination === 'pharmacy' ? 'pharmacy' : 'depot',
                'requesting_depot_id' => $defaultRequestingDepotId,
                'pharmacy_id' => $defaultPharmacyId,
                'source_depot_id' => $defaultSourceDepotId ? (string) $defaultSourceDepotId : '',
            ],
            'requestingDepot' => $requestingDepot ? [
                'id' => (int) $requestingDepot->id,
                'name' => $requestingDepot->name,
            ] : null,
            'sourceDepotOptions' => $sourceDepotOptions,
            'lockRequestingDepot' => $destination !== 'pharmacy' && $requestingDepot !== null,
            'currentUser' => $user ? [
                'id' => $user->id,
                'full_name' => DepotRequestContext::userDisplayName($user),
            ] : null,
            'formData' => $this->depotFormOptions(),
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'userPharmacies' => $user?->activePharmacies->map(fn ($pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all() ?? [],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.requests.index'),
                'store' => route('depots.requests.store'),
                'stockAvailable' => route('depots.stock.available'),
            ],
        ]);
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_created_successfully.'));
    }

    public function show(DepotRequest $depotRequest): Response
    {
        $this->authorizeDepotRequestView();
        abort_unless($this->userCanAccessDepotRequest($depotRequest), 403);

        $depotRequest->load([
            'requestingDepot.branch:id,name',
            'requestingDepot.department:id,name',
            'requestingDepot.pharmacy:id,name',
            'pharmacy:id,name',
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.tool:id,name,code',
            'items.unit:id,name',
            'items.depotTransaction:id,transaction_number',
            'requestedBy:id,name,last_name',
            'approvedBy:id,name,last_name',
            'fulfilledBy:id,name,last_name',
            'transactions:id,transaction_number,depot_request_id',
            'statusLogs.user:id,name,last_name',
        ]);

        return Inertia::render('Depots/Requests/Show', [
            'request' => $this->transformDetail($depotRequest),
            'workflowSteps' => DepotRequest::WORKFLOW_STEPS,
            'permissions' => $this->requestActionPermissions($depotRequest),
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.requests.index'),
                'edit' => route('depots.requests.edit', $depotRequest),
                'print' => route('depots.requests.print', $depotRequest),
                'submit' => route('depots.requests.submit', $depotRequest),
                'approve' => route('depots.requests.approve', $depotRequest),
                'reject' => route('depots.requests.reject', $depotRequest),
                'fulfill' => route('depots.requests.fulfill', $depotRequest),
                'cancel' => route('depots.requests.cancel', $depotRequest),
                'transactions' => route('depots.transactions.index'),
                'transactionShow' => url('/depots/transactions'),
            ],
        ]);
    }

    public function print(DepotRequest $depotRequest)
    {
        $this->authorizeDepotRequestView();
        abort_unless($this->userCanAccessDepotRequest($depotRequest), 403);

        $depotRequest->load([
            'requestingDepot.branch:id,name',
            'requestingDepot.department:id,name',
            'requestingDepot.pharmacy:id,name',
            'pharmacy:id,name',
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.tool:id,name,code',
            'items.unit:id,name',
            'requestedBy:id,name,last_name',
        ]);

        $context = DepotRequestContext::forRequest($depotRequest);
        $unitDepotId = $depotRequest->isPharmacyRequest()
            ? \App\Models\Depot::query()->where('pharmacy_id', $depotRequest->pharmacy_id)->value('id')
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

    public function edit(DepotRequest $depotRequest): Response
    {
        $this->authorizeDepotRequestCreate();
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $depotRequest->load([
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.tool:id,name',
            'items.unit:id,name',
        ]);

        return Inertia::render('Depots/Requests/Edit', [
            'request' => $this->transformDetail($depotRequest),
            'currentUser' => request()->user() ? [
                'id' => request()->user()->id,
                'full_name' => DepotRequestContext::userDisplayName(request()->user()),
            ] : null,
            'sourceDepot' => [
                'id' => (int) $depotRequest->source_depot_id,
                'name' => $depotRequest->sourceDepot?->name ?? '',
            ],
            'formData' => $this->depotFormOptions(),
            'viewContext' => $this->isPharmacyRequestContext() ? 'pharmacy' : 'depot',
            'userPharmacies' => request()->user()?->activePharmacies->map(fn ($pharmacy) => [
                'id' => $pharmacy->id,
                'name' => $pharmacy->name,
            ])->values()->all() ?? [],
            'navUrls' => $this->depotNavUrls(),
            'navPermissions' => $this->depotNavPermissions(),
            'urls' => [
                'index' => route('depots.requests.index'),
                'show' => route('depots.requests.show', $depotRequest),
                'update' => route('depots.requests.update', $depotRequest),
                'stockAvailable' => route('depots.stock.available'),
            ],
        ]);
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
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

        return redirect()
            ->route('depots.requests.show', $depotRequest->fresh())
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

        return redirect()
            ->route('depots.requests.show', $depotRequest)
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
            try {
                $query->whereDate('created_at', '>=', Verta::parse($request->date_from)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
        }
        if ($request->filled('date_to')) {
            try {
                $query->whereDate('created_at', '<=', Verta::parse($request->date_to)->datetime());
            } catch (\Throwable) {
                // Ignore invalid jalali date filter input.
            }
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

    /**
     * @return array<string, mixed>
     */
    private function transformListItem(DepotRequest $item): array
    {
        return [
            'id' => $item->id,
            'request_number' => $item->request_number,
            'status' => $item->status,
            'destination_type' => $item->isPharmacyRequest() ? 'pharmacy' : 'depot',
            'items_count' => $item->items_count ?? $item->items->count(),
            'total_quantity' => $item->totalQuantity(),
            'items_summary' => $item->itemsSummary(),
            'requesting_depot_name' => $item->requestingDepot?->name,
            'pharmacy_id' => $item->pharmacy_id,
            'pharmacy_name' => $item->pharmacy?->name,
            'destination_name' => $item->destinationLabel(),
            'source_depot_name' => $item->sourceDepot?->name,
            'requested_by_name' => $item->requestedBy ? trim("{$item->requestedBy->name} {$item->requestedBy->last_name}") : null,
            'created_at' => $this->formatDateTime($item->created_at),
        ];
    }

    private function formatDateTime(?\Illuminate\Support\Carbon $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return verta($value)->format('Y/m/d H:i');
        } catch (\Throwable) {
            return $value->format('Y-m-d H:i');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function transformItem(DepotRequestItem $item): array
    {
        return [
            'id' => $item->id,
            'medicine_id' => $item->medicine_id,
            'tool_id' => $item->tool_id,
            'unit_id' => $item->unit_id,
            'item_type' => $item->itemType(),
            'item_name' => $item->itemName(),
            'quantity' => $item->quantity,
            'unit_name' => $item->unit?->name,
            'batch_number' => $item->batch_number,
            'transaction_id' => $item->depot_transaction_id,
            'transaction_number' => $item->depotTransaction?->transaction_number,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(DepotRequest $item): array
    {
        $context = DepotRequestContext::forRequest($item);

        return [
            ...$this->transformListItem($item),
            ...$context,
            'requesting_depot_id' => $item->requesting_depot_id,
            'pharmacy_id' => $item->pharmacy_id,
            'source_depot_id' => $item->source_depot_id,
            'notes' => $item->notes,
            'workflow_rank' => $item->workflowRank(),
            'rejection_reason' => $item->rejection_reason,
            'approved_by_name' => $item->approvedBy ? trim("{$item->approvedBy->name} {$item->approvedBy->last_name}") : null,
            'fulfilled_by_name' => $item->fulfilledBy ? trim("{$item->fulfilledBy->name} {$item->fulfilledBy->last_name}") : null,
            'approved_at' => $item->approved_at?->format('Y-m-d H:i'),
            'fulfilled_at' => $item->fulfilled_at?->format('Y-m-d H:i'),
            'items' => $item->items->map(fn (DepotRequestItem $line) => $this->transformItem($line))->values()->all(),
            'transfers' => $item->transactions->map(fn ($tx) => [
                'id' => $tx->id,
                'transaction_number' => $tx->transaction_number,
            ])->values()->all(),
            'status_logs' => $item->statusLogs->map(fn ($log) => [
                'from_status' => $log->from_status,
                'to_status' => $log->to_status,
                'notes' => $log->notes,
                'user_name' => $log->user ? trim("{$log->user->name} {$log->user->last_name}") : null,
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
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
}
