@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">{{ $depotRequest->request_number }}</h5>
            <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3"><div class="text-muted small">Status</div><span class="badge bg-primary">{{ ucfirst($depotRequest->status) }}</span></div>
                <div class="col-md-3"><div class="text-muted small">Requesting Depot</div>{{ $depotRequest->requestingDepot?->name }}</div>
                <div class="col-md-3"><div class="text-muted small">Source Depot</div>{{ $depotRequest->sourceDepot?->name }}</div>
                <div class="col-md-3"><div class="text-muted small">Item</div>{{ $depotRequest->itemName() }}</div>
                <div class="col-md-3"><div class="text-muted small">Quantity</div>{{ $depotRequest->quantity }}</div>
                <div class="col-md-3"><div class="text-muted small">Requested By</div>{{ $depotRequest->requestedBy?->name ?? '-' }}</div>
                @if($depotRequest->rejection_reason)
                <div class="col-12"><div class="text-muted small">Rejection Reason</div>{{ $depotRequest->rejection_reason }}</div>
                @endif
                @if($depotRequest->depotTransaction)
                <div class="col-12">
                    <a href="{{ route('depots.transactions.show', $depotRequest->depotTransaction) }}" class="btn btn-sm btn-outline-primary">View Fulfillment Transaction</a>
                </div>
                @endif
            </div>
            <div class="d-flex flex-wrap gap-2 mt-4">
                @if($depotRequest->status === 'draft')
                    @can('depot.request.create')
                    <form action="{{ route('depots.requests.submit', $depotRequest) }}" method="POST">@csrf<button class="btn btn-primary">Submit</button></form>
                    @endcan
                @endif
                @if($depotRequest->status === 'pending')
                    @can('depot.request.approve')
                    <form action="{{ route('depots.requests.approve', $depotRequest) }}" method="POST">@csrf<button class="btn btn-success">Approve</button></form>
                    @endcan
                @endif
                @if($depotRequest->status === 'approved')
                    @can('depot.request.fulfill')
                    <form action="{{ route('depots.requests.fulfill', $depotRequest) }}" method="POST">@csrf<button class="btn btn-primary" onclick="return confirm('Fulfill and transfer stock?')">Fulfill</button></form>
                    @endcan
                @endif
                @if($depotRequest->status === 'pending')
                    @can('depot.request.approve')
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">Reject</button>
                    @endcan
                @endif
                @if(in_array($depotRequest->status, ['draft','pending','approved']))
                    <form action="{{ route('depots.requests.cancel', $depotRequest) }}" method="POST">@csrf<button class="btn btn-outline-secondary" onclick="return confirm('Cancel request?')">Cancel</button></form>
                @endif
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-header"><h6 class="mb-0">Workflow Timeline</h6></div>
        <div class="card-body">
            <ul class="list-group">
                @forelse($depotRequest->statusLogs as $log)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ ucfirst($log->from_status ?? 'new') }} → {{ ucfirst($log->to_status) }} @if($log->notes) — {{ $log->notes }} @endif</span>
                        <span class="text-muted">{{ $log->user?->name ?? '-' }} · {{ $log->created_at?->format('Y-m-d H:i') }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">No status changes yet.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('depots.requests.reject', $depotRequest) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header"><h5 class="modal-title">Reject Request</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="Reason"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Reject</button>
            </div>
        </form>
    </div>
</div>
@endsection
