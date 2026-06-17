<?php

namespace App\Http\Controllers;

use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\Medicine;
use App\Models\Tool;
use App\Models\Unit;
use App\Services\DepotRequestService;
use App\Services\DepotRequestSourceResolver;
use App\Support\DepotRequestAuthorization;
use App\Support\DepotRolePermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DepotRequestController extends Controller
{
    public function __construct(
        private readonly DepotRequestService $requestService,
        private readonly DepotRequestSourceResolver $sourceResolver,
    ) {
    }

    public function index(Request $request)
    {
        $query = DepotRequest::with([
            'requestingDepot',
            'sourceDepot',
            'items.medicine',
            'items.tool',
            'items.unit',
            'requestedBy',
            'approvedBy',
            'fulfilledBy',
        ]);

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

        $requests = $query->latest('id')->paginate(15)->appends($request->query());

        return view('pages.depots.requests.index', array_merge($this->formData(), [
            'requests' => $requests,
            'statuses' => DepotRequest::statuses(),
        ]));
    }

    public function create()
    {
        return view('pages.depots.requests.create', $this->formData());
    }

    public function store(StoreDepotRequestRequest $request)
    {
        $data = $request->validated();
        $sourceDepotId = $this->sourceResolver->resolve($data, $request->user());

        $depotRequest = \Illuminate\Support\Facades\DB::transaction(function () use ($data, $sourceDepotId) {
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

    public function show(DepotRequest $depotRequest)
    {
        $depotRequest->load([
            'requestingDepot',
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

        return view('pages.depots.requests.show', compact('depotRequest'));
    }

    public function submit(DepotRequest $depotRequest)
    {
        $this->authorizeRequestingDepotAction($depotRequest, DepotRolePermissions::ACTION_REQUEST_CREATE);

        try {
            $this->requestService->submit($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_submitted_for_approval.'));
    }

    public function approve(DepotRequest $depotRequest)
    {
        $this->authorizeProcessingDepotAction($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE);

        try {
            $this->requestService->approve($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_approved.'));
    }

    public function reject(Request $request, DepotRequest $depotRequest)
    {
        $this->authorizeProcessingDepotAction($depotRequest, DepotRolePermissions::ACTION_REQUEST_APPROVE);

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

    public function fulfill(DepotRequest $depotRequest)
    {
        $this->authorizeProcessingDepotAction($depotRequest, DepotRolePermissions::ACTION_REQUEST_FULFILL);

        try {
            $this->requestService->fulfill($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_fulfilled_and_transferred.'));
    }

    public function cancel(DepotRequest $depotRequest)
    {
        $user = Auth::user();
        abort_unless($user, 403);

        $canCancel = match ($depotRequest->status) {
            DepotRequest::STATUS_DRAFT => $depotRequest->requesting_depot_id
                && $user->canPerformDepotAction((int) $depotRequest->requesting_depot_id, DepotRolePermissions::ACTION_REQUEST_CREATE),
            DepotRequest::STATUS_PENDING, DepotRequest::STATUS_APPROVED => DepotRequestAuthorization::canProcess(
                $user,
                $depotRequest,
                DepotRolePermissions::ACTION_REQUEST_APPROVE,
            ),
            default => false,
        };

        abort_unless($canCancel, 403);

        try {
            $this->requestService->cancel($depotRequest);
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors());
        }

        return redirect()->route('depots.requests.show', $depotRequest)
            ->with('success', localize('global.depot.request_cancelled.'));
    }

    private function formData(): array
    {
        return [
            'depots' => Depot::query()->where('is_active', true)->orderBy('name')->get(),
            'medicines' => Medicine::query()->whereNull('deleted_at')->orderBy('name')->get(),
            'tools' => Tool::query()->where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::query()->where('is_active', true)->orderBy('name')->get(),
        ];
    }

    private function authorizeProcessingDepotAction(DepotRequest $depotRequest, string $action): void
    {
        $user = Auth::user();
        abort_unless($user && DepotRequestAuthorization::canProcess($user, $depotRequest, $action), 403);
    }

    private function authorizeRequestingDepotAction(DepotRequest $depotRequest, string $action): void
    {
        $user = Auth::user();
        abort_unless($user, 403);

        if ($depotRequest->isPharmacyRequest()) {
            return;
        }

        abort_unless(
            $depotRequest->requesting_depot_id
                && $user->canPerformDepotAction((int) $depotRequest->requesting_depot_id, $action),
            403
        );
    }
}
