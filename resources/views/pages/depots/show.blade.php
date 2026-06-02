@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0"><i class="bx bx-show-alt me-1"></i>{{ $depot->name }}</h5>
            <div class="d-flex flex-wrap gap-2">
                @can('depot.request.create')
                <a href="{{ route('depots.requests.create') }}?requesting_depot_id={{ $depot->id }}" class="btn btn-outline-primary btn-sm">Request Items</a>
                @endcan
                @can('depot.transaction.create')
                <a href="{{ route('depots.transactions.create') }}?depot_id={{ $depot->id }}" class="btn btn-outline-primary btn-sm">Stock In/Out</a>
                @endcan
                @if($depot->pharmacy_id)
                @can('depot.movement.depot_to_pharmacy')
                <a href="{{ route('depots.movements.depot_to_pharmacy') }}?from_depot_id={{ $depot->id }}" class="btn btn-outline-primary btn-sm">To Pharmacy</a>
                @endcan
                @endif
                <a href="{{ route('depots.stock.index', $depot) }}" class="btn btn-outline-secondary btn-sm">Full Stock</a>
                @can('depot.update')
                <a href="{{ route('depots.edit', $depot) }}" class="btn btn-primary btn-sm"><i class="bx bx-edit me-1"></i>Edit</a>
                @endcan
                <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary btn-sm">{{ localize('global.back') }}</a>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4"><div class="text-muted small">{{ localize('global.depot.branch') }}</div><div>{{ $depot->branch?->name ?? '-' }}</div></div>
                <div class="col-md-4"><div class="text-muted small">{{ localize('global.depot.pharmacy') }}</div><div>{{ $depot->pharmacy?->name ?? '-' }}</div></div>
                <div class="col-md-4"><div class="text-muted small">{{ localize('global.depot.parent_depot') }}</div><div>{{ $depot->parentDepot?->name ?? '-' }}</div></div>
                <div class="col-md-4"><div class="text-muted small">{{ localize('global.depot.is_active') }}</div><span class="badge bg-{{ $depot->is_active ? 'success' : 'secondary' }}">{{ $depot->is_active ? 'Active' : 'Inactive' }}</span></div>
                <div class="col-md-4"><div class="text-muted small">{{ localize('global.depot.is_base') }}</div><span class="badge bg-{{ $depot->is_base ? 'primary' : 'secondary' }}">{{ $depot->is_base ? 'Base' : 'Child' }}</span></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between"><h6 class="mb-0">{{ localize('global.depot.stock_summary') }}</h6><a href="{{ route('depots.stock.index', $depot) }}">View all</a></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Type</th><th>Item</th><th>Qty</th></tr></thead>
                        <tbody>
                            @forelse($stockItems as $item)
                                <tr>
                                    <td>{{ ucfirst($item['item_type']) }}</td>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ number_format($item['available']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.recent_transactions') }}</h6></div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Type</th><th>Item</th><th>Qty</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($recentTransactions as $txn)
                                <tr>
                                    <td>{{ str_replace('_', ' ', $txn->type) }}</td>
                                    <td>{{ $txn->medicine?->name ?? $txn->tool?->name ?? '-' }}</td>
                                    <td>{{ $txn->quantity }}</td>
                                    <td>{{ optional($txn->transaction_date)->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center">{{ localize('global.no_data_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.my_requests') }}</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse($pendingOutgoingRequests as $req)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $req->itemName() }} ({{ $req->quantity }})</span>
                            <a href="{{ route('depots.requests.show', $req) }}">{{ ucfirst($req->status) }}</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ localize('global.no_data_found') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><h6 class="mb-0">{{ localize('global.depot.incoming_requests') }}</h6></div>
                <ul class="list-group list-group-flush">
                    @forelse($pendingIncomingRequests as $req)
                        <li class="list-group-item d-flex justify-content-between">
                            <span>{{ $req->requestingDepot?->name }} — {{ $req->itemName() }}</span>
                            <a href="{{ route('depots.requests.show', $req) }}">{{ ucfirst($req->status) }}</a>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">{{ localize('global.no_data_found') }}</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
