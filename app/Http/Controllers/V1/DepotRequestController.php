<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Http\Requests\Depot\UpdateDepotRequestRequest;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Services\DepotRequestService;
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
        'search', 'requesting_depot_id', 'source_depot_id', 'status', 'medicine_id', 'tool_id', 'date_from', 'date_to', 'per_page',
    ];

    public function __construct(
        private readonly DepotRequestService $requestService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorizeDepotRequestView();

        $query = DepotRequest::with([
            'requestingDepot:id,name',
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
                'medicines' => $options['medicines'],
                'tools' => $options['tools'],
                'statuses' => DepotRequest::statuses(),
            ],
            'permissions' => $this->depotRequestPermissions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.requests.index'),
                'create' => route('react.depots.requests.create'),
                'show' => url('/react/depots/requests'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeDepotPermission('depot.request.create');

        return Inertia::render('Depots/Requests/Create', [
            'defaults' => [
                'requesting_depot_id' => (string) $request->query('requesting_depot_id', ''),
                'source_depot_id' => (string) $request->query('source_depot_id', ''),
            ],
            'formData' => $this->depotFormOptions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.requests.index'),
                'store' => route('react.depots.requests.store'),
                'stockAvailable' => route('react.depots.stock.available'),
            ],
        ]);
    }

    public function store(StoreDepotRequestRequest $request): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.create');

        $data = $request->validated();

        $depotRequest = DB::transaction(function () use ($data) {
            $depotRequest = DepotRequest::create([
                'requesting_depot_id' => $data['requesting_depot_id'],
                'source_depot_id' => $data['source_depot_id'],
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
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_created_successfully.'));
    }

    public function show(DepotRequest $depotRequest): Response
    {
        $this->authorizeDepotRequestView();

        $depotRequest->load([
            'requestingDepot:id,name',
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.tool:id,name',
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
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.requests.index'),
                'edit' => route('react.depots.requests.edit', $depotRequest),
                'submit' => route('react.depots.requests.submit', $depotRequest),
                'approve' => route('react.depots.requests.approve', $depotRequest),
                'reject' => route('react.depots.requests.reject', $depotRequest),
                'fulfill' => route('react.depots.requests.fulfill', $depotRequest),
                'cancel' => route('react.depots.requests.cancel', $depotRequest),
                'transactions' => route('react.depots.transactions.index'),
                'transactionShow' => url('/react/depots/transactions'),
            ],
        ]);
    }

    public function edit(DepotRequest $depotRequest): Response
    {
        $this->authorizeDepotPermission('depot.request.create');

        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $depotRequest->load([
            'items.medicine:id,name',
            'items.tool:id,name',
            'items.unit:id,name',
        ]);

        return Inertia::render('Depots/Requests/Edit', [
            'request' => $this->transformDetail($depotRequest),
            'formData' => $this->depotFormOptions(),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.requests.index'),
                'show' => route('react.depots.requests.show', $depotRequest),
                'update' => route('react.depots.requests.update', $depotRequest),
                'stockAvailable' => route('react.depots.stock.available'),
            ],
        ]);
    }

    public function update(UpdateDepotRequestRequest $request, DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.create');

        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $data = $request->validated();

        DB::transaction(function () use ($depotRequest, $data) {
            $depotRequest->update([
                'requesting_depot_id' => $data['requesting_depot_id'],
                'source_depot_id' => $data['source_depot_id'],
                'notes' => $data['notes'] ?? null,
            ]);

            $this->requestService->syncItems($depotRequest, $data['items']);
        });

        return redirect()
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.updated_successfully.'));
    }

    public function submit(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.create');

        try {
            $this->requestService->submit($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_submitted_for_approval.'));
    }

    public function approve(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.approve');

        try {
            $this->requestService->approve($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_approved.'));
    }

    public function reject(Request $request, DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.approve');

        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:2000'],
        ]);

        try {
            $this->requestService->reject($depotRequest, $data['rejection_reason']);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_rejected.'));
    }

    public function fulfill(DepotRequest $depotRequest): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.fulfill');

        try {
            $this->requestService->fulfill($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.depots.requests.show', $depotRequest->fresh())
            ->with('success', localize('global.depot.request_fulfilled_and_transferred.'));
    }

    public function cancel(DepotRequest $depotRequest): RedirectResponse
    {
        if (! $this->userCan('depot.request.create') && ! $this->userCan('depot.request.approve')) {
            abort(403);
        }

        try {
            $this->requestService->cancel($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_cancelled.'));
    }

    private function authorizeDepotRequestView(): void
    {
        abort_unless($this->userCanAny(['depot.request.create', 'depot.request.approve', 'depot.request.fulfill']), 403);
    }

    /**
     * @return array{submit: bool, approve: bool, reject: bool, fulfill: bool, cancel: bool, edit: bool}
     */
    private function requestActionPermissions(DepotRequest $depotRequest): array
    {
        $status = $depotRequest->status;

        return [
            'edit' => $status === DepotRequest::STATUS_DRAFT && $this->userCan('depot.request.create'),
            'submit' => $status === DepotRequest::STATUS_DRAFT && $this->userCan('depot.request.create'),
            'approve' => $status === DepotRequest::STATUS_PENDING && $this->userCan('depot.request.approve'),
            'reject' => $status === DepotRequest::STATUS_PENDING && $this->userCan('depot.request.approve'),
            'fulfill' => $status === DepotRequest::STATUS_APPROVED && $this->userCan('depot.request.fulfill'),
            'cancel' => in_array($status, [DepotRequest::STATUS_DRAFT, DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED], true)
                && ($this->userCan('depot.request.create') || $this->userCan('depot.request.approve')),
        ];
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotRequest>  $query
     */
    private function applyRequestFilters($query, Request $request): void
    {
        if ($request->filled('requesting_depot_id')) {
            $query->where('requesting_depot_id', $request->requesting_depot_id);
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

    /**
     * @return array<string, mixed>
     */
    private function transformListItem(DepotRequest $item): array
    {
        return [
            'id' => $item->id,
            'request_number' => $item->request_number,
            'status' => $item->status,
            'items_count' => $item->items_count ?? $item->items->count(),
            'total_quantity' => $item->totalQuantity(),
            'items_summary' => $item->itemsSummary(),
            'requesting_depot_name' => $item->requestingDepot?->name,
            'source_depot_name' => $item->sourceDepot?->name,
            'requested_by_name' => $item->requestedBy ? trim("{$item->requestedBy->name} {$item->requestedBy->last_name}") : null,
            'created_at' => $item->created_at?->format('Y-m-d H:i'),
        ];
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
        return [
            ...$this->transformListItem($item),
            'requesting_depot_id' => $item->requesting_depot_id,
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
}
