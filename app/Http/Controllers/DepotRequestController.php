<?php

namespace App\Http\Controllers;

use App\Http\Requests\Depot\StoreDepotRequestRequest;
use App\Models\Depot;
use App\Models\DepotRequest;
use App\Models\Medicine;
use App\Models\Tool;
use App\Models\Unit;
use App\Services\DepotRequestService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class DepotRequestController extends Controller
{
    public function __construct(
        private readonly DepotRequestService $requestService
    ) {
    }

    public function index(Request $request)
    {
        $query = DepotRequest::with([
            'requestingDepot',
            'sourceDepot',
            'medicine',
            'tool',
            'unit',
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

        $depotRequest = DepotRequest::create([
            ...$data,
            'status' => DepotRequest::STATUS_DRAFT,
            'requested_by' => Auth::id(),
        ]);

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
            'medicine',
            'tool',
            'unit',
            'requestedBy',
            'approvedBy',
            'fulfilledBy',
            'depotTransaction',
            'statusLogs.user',
        ]);

        return view('pages.depots.requests.show', compact('depotRequest'));
    }

    public function submit(DepotRequest $depotRequest)
    {
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
}
