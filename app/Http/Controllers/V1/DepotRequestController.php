<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Models\DepotRequest;
use App\Services\DepotRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'medicine:id,name',
            'tool:id,name',
            'unit:id,name',
            'requestedBy:id,name,last_name',
        ]);

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
            ],
        ]);
    }

    public function store(StoreDepotRequestRequest $request): RedirectResponse
    {
        $this->authorizeDepotPermission('depot.request.create');

        $data = $request->validated();

        $depotRequest = DepotRequest::create([
            ...$data,
            'status' => DepotRequest::STATUS_DRAFT,
            'requested_by' => Auth::id(),
        ]);

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
            'medicine:id,name',
            'tool:id,name',
            'unit:id,name',
            'requestedBy:id,name,last_name',
            'approvedBy:id,name,last_name',
            'fulfilledBy:id,name,last_name',
            'depotTransaction:id,transaction_number',
            'statusLogs.user:id,name,last_name',
        ]);

        return Inertia::render('Depots/Requests/Show', [
            'request' => $this->transformDetail($depotRequest),
            'permissions' => $this->requestActionPermissions($depotRequest),
            'navUrls' => $this->depotNavUrls(),
            'urls' => [
                'index' => route('react.depots.requests.index'),
                'submit' => route('react.depots.requests.submit', $depotRequest),
                'approve' => route('react.depots.requests.approve', $depotRequest),
                'reject' => route('react.depots.requests.reject', $depotRequest),
                'fulfill' => route('react.depots.requests.fulfill', $depotRequest),
                'cancel' => route('react.depots.requests.cancel', $depotRequest),
                'transaction' => $depotRequest->depot_transaction_id
                    ? route('react.depots.transactions.show', $depotRequest->depot_transaction_id)
                    : null,
            ],
        ]);
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
     * @return array{submit: bool, approve: bool, reject: bool, fulfill: bool, cancel: bool}
     */
    private function requestActionPermissions(DepotRequest $depotRequest): array
    {
        $status = $depotRequest->status;

        return [
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
            $query->where('medicine_id', $request->medicine_id);
        }
        if ($request->filled('tool_id')) {
            $query->where('tool_id', $request->tool_id);
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
                    ->orWhereHas('medicine', fn ($m) => $m->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('tool', fn ($t) => $t->where('name', 'like', "%{$search}%"));
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
            'quantity' => $item->quantity,
            'item_name' => $item->medicine?->name ?? $item->tool?->name,
            'requesting_depot_name' => $item->requestingDepot?->name,
            'source_depot_name' => $item->sourceDepot?->name,
            'requested_by_name' => $item->requestedBy ? trim("{$item->requestedBy->name} {$item->requestedBy->last_name}") : null,
            'created_at' => $item->created_at?->format('Y-m-d H:i'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(DepotRequest $item): array
    {
        return [
            ...$this->transformListItem($item),
            'batch_number' => $item->batch_number,
            'notes' => $item->notes,
            'unit_name' => $item->unit?->name,
            'rejection_reason' => $item->rejection_reason,
            'approved_by_name' => $item->approvedBy ? trim("{$item->approvedBy->name} {$item->approvedBy->last_name}") : null,
            'fulfilled_by_name' => $item->fulfilledBy ? trim("{$item->fulfilledBy->name} {$item->fulfilledBy->last_name}") : null,
            'approved_at' => $item->approved_at?->format('Y-m-d H:i'),
            'fulfilled_at' => $item->fulfilled_at?->format('Y-m-d H:i'),
            'transaction_number' => $item->depotTransaction?->transaction_number,
            'status_logs' => $item->statusLogs->map(fn ($log) => [
                'status' => $log->status,
                'notes' => $log->notes,
                'user_name' => $log->user ? trim("{$log->user->name} {$log->user->last_name}") : null,
                'created_at' => $log->created_at?->format('Y-m-d H:i'),
            ])->values()->all(),
        ];
    }
}
