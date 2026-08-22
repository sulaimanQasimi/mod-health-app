<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Controllers\V1\Concerns\AuthorizesPharmacyStockAccess;
use App\Http\Controllers\V1\Concerns\ManagesDepotAccess;
use App\Http\Controllers\V1\Concerns\PaginatesInertiaIndex;
use App\Http\Controllers\V1\Concerns\ProvidesDepotFormData;
use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Http\Requests\Depot\UpdateDepotRequestRequest;
use App\Models\DepotRequest;
use App\Models\DepotRequestItem;
use App\Models\Pharmacy;
use App\Models\User;
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

class PharmacyTransferRequestController extends Controller
{
    use AuthorizesPharmacyStockAccess;
    use ManagesDepotAccess;
    use PaginatesInertiaIndex;
    use ProvidesDepotFormData;

    private const FILTER_KEYS = [
        'search', 'pharmacy_id', 'status', 'medicine_id', 'date_from', 'date_to', 'per_page',
    ];

    public function __construct(
        private readonly DepotRequestService $requestService,
        private readonly DepotRequestSourceResolver $sourceResolver,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);

        $query = DepotRequest::query()
            ->whereNotNull('pharmacy_id')
            ->with([
                'pharmacy:id,name',
                'sourceDepot:id,name',
                'items.medicine:id,name',
                'requestedBy:id,name,last_name',
            ])
            ->withCount('items');

        $this->applyPharmacyRequestFilters($query, $request, $user);

        $paginator = $this->paginateQuery($query->latest('id'), $request, 15);
        $userPharmacies = $this->userPharmacyOptions($user);

        return Inertia::render('PharmacyTransferRequests/Index', [
            'requests' => $this->paginationPayload($paginator, fn (DepotRequest $item) => $this->transformListItem($item)),
            'filters' => $this->collectFilters($request, self::FILTER_KEYS),
            'filterOptions' => [
                'pharmacies' => $userPharmacies,
                'medicines' => $this->depotFormOptions()['medicines'],
                'statuses' => DepotRequest::statuses(),
            ],
            'permissions' => ['create' => true, 'view' => true],
            'urls' => $this->pageUrls(),
        ]);
    }

    public function create(Request $request): Response
    {
        $user = $request->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);

        $userPharmacies = $this->userPharmacyOptions($user);
        $defaultPharmacyId = (string) ($request->query('pharmacy_id')
            ?: ($userPharmacies[0]['id'] ?? ''));
        $sourceDepotOptions = $this->sourceResolver->sourceOptionsForPharmacyRequest($user);
        $defaultSourceDepotId = (string) ($request->query('source_depot_id')
            ?: ($sourceDepotOptions[0]['id'] ?? ''));

        return Inertia::render('PharmacyTransferRequests/Create', [
            'defaultPharmacyId' => $defaultPharmacyId,
            'userPharmacies' => $userPharmacies,
            'sourceDepotOptions' => $sourceDepotOptions,
            'defaultSourceDepotId' => $defaultSourceDepotId,
            'formData' => $this->depotFormOptions(),
            'urls' => [
                ...$this->pageUrls(),
                'store' => route('react.pharmacy.transfer-requests.store'),
                'stockAvailable' => route('react.depots.stock.available'),
            ],
        ]);
    }

    public function store(StoreDepotRequestRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);

        $data = $request->validated();
        abort_unless(! empty($data['pharmacy_id']), 422);
        $this->authorizePharmacyRequestData($data, $user);

        $sourceDepotId = $this->sourceResolver->resolve(
            $data,
            $user,
            ! empty($data['source_depot_id']) ? (int) $data['source_depot_id'] : null,
        );

        $depotRequest = DB::transaction(function () use ($data, $sourceDepotId) {
            $depotRequest = DepotRequest::create([
                'pharmacy_id' => $data['pharmacy_id'],
                'source_depot_id' => $sourceDepotId,
                'notes' => $data['notes'] ?? null,
                'status' => DepotRequest::STATUS_DRAFT,
                'requested_by' => Auth::id(),
            ]);

            $this->requestService->syncItems($depotRequest, $data['items']);

            return $depotRequest;
        });

        if ($request->boolean('submit_now', true)) {
            $this->requestService->submit($depotRequest);
        }

        return redirect()
            ->route('react.pharmacy.transfer-requests.show', $depotRequest)
            ->with('success', localize('global.pharmacy_transfer_request_created.'));
    }

    public function show(DepotRequest $depotRequest): Response
    {
        $user = request()->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);
        $this->assertPharmacyTransferRequest($depotRequest);
        abort_unless($this->userCanAccessDepotRequest($depotRequest, $user), 403);

        $depotRequest->load([
            'pharmacy:id,name',
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.unit:id,name',
            'requestedBy:id,name,last_name',
            'approvedBy:id,name,last_name',
            'fulfilledBy:id,name,last_name',
            'statusLogs.user:id,name,last_name',
        ]);

        return Inertia::render('PharmacyTransferRequests/Show', [
            'request' => $this->transformDetail($depotRequest),
            'workflowSteps' => DepotRequest::WORKFLOW_STEPS,
            'permissions' => $this->requestActionPermissions($depotRequest),
            'urls' => [
                ...$this->pageUrls(),
                'edit' => route('react.pharmacy.transfer-requests.edit', $depotRequest),
                'print' => route('react.depots.requests.print', $depotRequest),
                'submit' => route('react.pharmacy.transfer-requests.submit', $depotRequest),
                'cancel' => route('react.pharmacy.transfer-requests.cancel', $depotRequest),
            ],
        ]);
    }

    public function edit(DepotRequest $depotRequest): Response
    {
        $user = request()->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);
        $this->assertPharmacyTransferRequest($depotRequest);
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);
        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $depotRequest->load([
            'sourceDepot:id,name',
            'items.medicine:id,name',
            'items.unit:id,name',
        ]);

        $sourceDepotOptions = $this->sourceResolver->sourceOptionsForPharmacyRequest($user);

        return Inertia::render('PharmacyTransferRequests/Edit', [
            'request' => $this->transformDetail($depotRequest),
            'userPharmacies' => $this->userPharmacyOptions($user),
            'sourceDepotOptions' => $sourceDepotOptions,
            'defaultSourceDepotId' => (string) $depotRequest->source_depot_id,
            'formData' => $this->depotFormOptions(),
            'urls' => [
                ...$this->pageUrls(),
                'show' => route('react.pharmacy.transfer-requests.show', $depotRequest),
                'update' => route('react.pharmacy.transfer-requests.update', $depotRequest),
                'stockAvailable' => route('react.depots.stock.available'),
            ],
        ]);
    }

    public function update(UpdateDepotRequestRequest $request, DepotRequest $depotRequest): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);
        $this->assertPharmacyTransferRequest($depotRequest);
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);
        abort_unless($depotRequest->status === DepotRequest::STATUS_DRAFT, 403);

        $data = $request->validated();
        abort_unless(! empty($data['pharmacy_id']), 422);
        $this->authorizePharmacyRequestData($data, $user);

        $sourceDepotId = $this->sourceResolver->resolve(
            $data,
            $user,
            ! empty($data['source_depot_id']) ? (int) $data['source_depot_id'] : $depotRequest->source_depot_id,
        );

        DB::transaction(function () use ($depotRequest, $data, $sourceDepotId) {
            $depotRequest->update([
                'pharmacy_id' => $data['pharmacy_id'],
                'source_depot_id' => $sourceDepotId,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->requestService->syncItems($depotRequest, $data['items']);
        });

        return redirect()
            ->route('react.pharmacy.transfer-requests.show', $depotRequest)
            ->with('success', localize('global.updated_successfully.'));
    }

    public function submit(DepotRequest $depotRequest): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);
        $this->assertPharmacyTransferRequest($depotRequest);
        $this->authorizeDepotRequestRecord($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        try {
            $this->requestService->submit($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.pharmacy.transfer-requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_submitted_for_approval.'));
    }

    public function cancel(DepotRequest $depotRequest): RedirectResponse
    {
        $user = request()->user();
        abort_unless($user, 403);
        $this->authorizePharmacyFulfillment($user);
        $this->assertPharmacyTransferRequest($depotRequest);
        abort_unless($this->userCanAccessDepotRequest($depotRequest, $user), 403);

        $canCancel = $depotRequest->status === DepotRequest::STATUS_DRAFT
            && $this->canAccessPharmacyRequests($user);

        abort_unless($canCancel, 403);

        try {
            $this->requestService->cancel($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()
            ->route('react.pharmacy.transfer-requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_cancelled.'));
    }

    /**
     * @return array<string, string>
     */
    private function pageUrls(): array
    {
        return [
            'index' => route('react.pharmacy.transfer-requests.index'),
            'create' => route('react.pharmacy.transfer-requests.create'),
            'show' => url('/react/pharmacy/transfer-requests'),
        ];
    }

    /**
     * @return list<array{id: int, name: string}>
     */
    private function userPharmacyOptions(User $user): array
    {
        $allowedIds = $this->allowedPharmacyIdsForRequests($user);

        if ($this->isPharmacyAdmin($user)) {
            return Pharmacy::query()
                ->when($allowedIds !== [], fn ($query) => $query->whereIn('id', $allowedIds))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Pharmacy $pharmacy) => [
                    'id' => (int) $pharmacy->id,
                    'name' => $pharmacy->name,
                ])
                ->values()
                ->all();
        }

        return $user->activePharmacies
            ->whereIn('id', $allowedIds)
            ->map(fn (Pharmacy $pharmacy) => [
                'id' => (int) $pharmacy->id,
                'name' => $pharmacy->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function authorizePharmacyRequestData(array $data, User $user): void
    {
        if ($this->isDepotSystemAdmin($user)) {
            return;
        }

        $allowedPharmacyIds = $this->allowedPharmacyIdsForRequests($user);
        abort_unless(in_array((int) $data['pharmacy_id'], $allowedPharmacyIds, true), 403);
    }

    private function assertPharmacyTransferRequest(DepotRequest $depotRequest): void
    {
        abort_unless($depotRequest->isPharmacyRequest(), 404);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<DepotRequest>  $query
     */
    private function applyPharmacyRequestFilters($query, Request $request, User $user): void
    {
        if (! $this->isDepotSystemAdmin($user)) {
            $query->whereIn('pharmacy_id', $this->allowedPharmacyIdsForRequests($user));
        }

        if ($request->filled('pharmacy_id')) {
            $query->where('pharmacy_id', $request->pharmacy_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('medicine_id')) {
            $query->whereHas('items', fn ($q) => $q->where('medicine_id', $request->medicine_id));
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
                    ->orWhereHas('items.medicine', fn ($m) => $m->where('name', 'like', "%{$search}%"));
            });
        }
    }

    /**
     * @return array{edit: bool, submit: bool, cancel: bool}
     */
    private function requestActionPermissions(DepotRequest $depotRequest): array
    {
        $canManage = $this->canAccessPharmacyRequests();
        $status = $depotRequest->status;

        return [
            'edit' => $status === DepotRequest::STATUS_DRAFT && $canManage,
            'submit' => $status === DepotRequest::STATUS_DRAFT && $canManage,
            'cancel' => $status === DepotRequest::STATUS_DRAFT && $canManage,
        ];
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
            'pharmacy_id' => $item->pharmacy_id,
            'pharmacy_name' => $item->pharmacy?->name,
            'source_depot_name' => $item->sourceDepot?->name,
            'requested_by_name' => $item->requestedBy
                ? trim("{$item->requestedBy->name} {$item->requestedBy->last_name}")
                : null,
            'created_at' => $this->formatDateTime($item->created_at),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function transformDetail(DepotRequest $item): array
    {
        return [
            ...$this->transformListItem($item),
            'pharmacy_id' => $item->pharmacy_id,
            'source_depot_id' => $item->source_depot_id,
            'notes' => $item->notes,
            'workflow_rank' => $item->workflowRank(),
            'rejection_reason' => $item->rejection_reason,
            'approved_by_name' => $item->approvedBy
                ? trim("{$item->approvedBy->name} {$item->approvedBy->last_name}")
                : null,
            'fulfilled_by_name' => $item->fulfilledBy
                ? trim("{$item->fulfilledBy->name} {$item->fulfilledBy->last_name}")
                : null,
            'approved_at' => $item->approved_at?->format('Y-m-d H:i'),
            'fulfilled_at' => $item->fulfilled_at?->format('Y-m-d H:i'),
            'items' => $item->items->map(fn (DepotRequestItem $line) => [
                'id' => $line->id,
                'medicine_id' => $line->medicine_id,
                'unit_id' => $line->unit_id,
                'item_name' => $line->itemName(),
                'quantity' => $line->quantity,
                'unit_name' => $line->unit?->name,
                'batch_number' => $line->batch_number,
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
}
