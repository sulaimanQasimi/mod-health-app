@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0">{{ $depotRequest->request_number }}</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('depots.requests.print', $depotRequest) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                    <i class="bx bx-printer"></i> {{ localize('global.print') ?? 'Print' }}
                </a>
                <a href="{{ route('depots.requests.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.status') }}</div>
                    <span class="badge bg-primary">{{ ucfirst($depotRequest->status) }}</span>
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.depot.destination_type') ?? 'Destination' }}</div>
                    {{ $depotRequest->isPharmacyRequest() ? (localize('global.pharmacy') ?? 'Pharmacy') : (localize('global.depot.depot') ?? 'Depot') }}
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.depot.requesting_depot') }} / {{ localize('global.pharmacy') }}</div>
                    {{ $depotRequest->destinationLabel() }}
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.depot.source_depot') }}</div>
                    {{ $depotRequest->sourceDepot?->name ?? '-' }}
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.item') }}</div>
                    {{ $depotRequest->itemsSummary() }}
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.quantity') }}</div>
                    {{ $depotRequest->totalQuantity() }} ({{ $depotRequest->items->count() }} {{ localize('global.lines') ?? 'lines' }})
                </div>
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.requested_by') ?? 'Requested By' }}</div>
                    {{ trim(($depotRequest->requestedBy->name ?? '') . ' ' . ($depotRequest->requestedBy->last_name ?? '')) ?: '-' }}
                </div>
                @if($context['branch_name'] ?? null)
                <div class="col-md-3">
                    <div class="text-muted small">{{ localize('global.branch') ?? 'Branch' }}</div>
                    {{ $context['branch_name'] }}
                </div>
                @endif
                @if($depotRequest->rejection_reason)
                <div class="col-12">
                    <div class="text-muted small">{{ localize('global.rejection_reason') ?? 'Rejection Reason' }}</div>
                    {{ $depotRequest->rejection_reason }}
                </div>
                @endif
                @if($depotRequest->notes)
                <div class="col-12">
                    <div class="text-muted small">{{ localize('global.notes') }}</div>
                    {{ $depotRequest->notes }}
                </div>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                @if($permissions['edit'] ?? false)
                    <a href="{{ route('depots.requests.edit', $depotRequest) }}" class="btn btn-outline-primary">{{ localize('global.edit') }}</a>
                @endif
                @if($permissions['submit'] ?? false)
                    <form action="{{ route('depots.requests.submit', $depotRequest) }}" method="POST">@csrf<button class="btn btn-primary">{{ localize('global.submit') ?? 'Submit' }}</button></form>
                @endif
                @if($permissions['approve'] ?? false)
                    <form action="{{ route('depots.requests.approve', $depotRequest) }}" method="POST">@csrf<button class="btn btn-success">{{ localize('global.approve') ?? 'Approve' }}</button></form>
                @endif
                @if($permissions['reject'] ?? false)
                    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">{{ localize('global.reject') ?? 'Reject' }}</button>
                @endif
                @if($permissions['fulfill'] ?? false)
                    <form action="{{ route('depots.requests.fulfill', $depotRequest) }}" method="POST">@csrf<button class="btn btn-primary" onclick="return confirm('Fulfill and transfer stock?')">{{ localize('global.fulfill') ?? 'Fulfill' }}</button></form>
                @endif
                @if($permissions['cancel'] ?? false)
                    <form action="{{ route('depots.requests.cancel', $depotRequest) }}" method="POST">@csrf<button class="btn btn-outline-secondary" onclick="return confirm('Cancel request?')">{{ localize('global.cancel') ?? 'Cancel' }}</button></form>
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.request_items') ?? 'Request Items' }}</h6></div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ localize('global.item') }}</th>
                        <th>{{ localize('global.type') ?? 'Type' }}</th>
                        <th>{{ localize('global.quantity') }}</th>
                        <th>{{ localize('global.unit') }}</th>
                        <th>{{ localize('global.batch_number') }}</th>
                        <th>{{ localize('global.transaction') ?? 'Transaction' }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($depotRequest->items as $i => $item)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $item->itemName() }}</td>
                            <td>{{ ucfirst($item->itemType() ?? '-') }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit?->name ?? '-' }}</td>
                            <td>{{ $item->batch_number ?: '-' }}</td>
                            <td>
                                @if($item->depotTransaction)
                                    <a href="{{ route('depots.transactions.show', $item->depotTransaction) }}">{{ $item->depotTransaction->transaction_number }}</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">{{ localize('global.no_data_found') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($depotRequest->transactions->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.fulfillment_transactions') ?? 'Fulfillment Transactions' }}</h6></div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                @foreach($depotRequest->transactions as $tx)
                    <a href="{{ route('depots.transactions.show', $tx) }}" class="btn btn-sm btn-outline-primary">{{ $tx->transaction_number }}</a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.workflow_timeline') ?? 'Workflow Timeline' }}</h6></div>
        <div class="card-body">
            <ul class="list-group">
                @forelse($depotRequest->statusLogs as $log)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ ucfirst($log->from_status ?? 'new') }} → {{ ucfirst($log->to_status) }} @if($log->notes) — {{ $log->notes }} @endif</span>
                        <span class="text-muted">{{ $log->user?->name ?? '-' }} · {{ $log->created_at?->format('Y-m-d H:i') }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">{{ localize('global.depot.no_status_changes') ?? 'No status changes yet.' }}</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>

<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('depots.requests.reject', $depotRequest) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">{{ localize('global.reject') ?? 'Reject Request' }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea name="rejection_reason" class="form-control" rows="4" required placeholder="{{ localize('global.reason') ?? 'Reason' }}"></textarea>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger">{{ localize('global.reject') ?? 'Reject' }}</button>
            </div>
        </form>
    </div>
</div>
@endsection
