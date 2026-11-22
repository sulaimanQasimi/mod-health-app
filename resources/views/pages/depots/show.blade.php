@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <x-depot.nav-tabs active="index" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ $depot->name }}</h5>
                <small class="text-muted">{{ $depot->address ?: localize('global.depot.address') . ': -' }}</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($permissions['request_create'] ?? true)
                    <a href="{{ route('depots.requests.create') }}?requesting_depot_id={{ $depot->id }}" class="btn btn-sm btn-success">
                        <i class="bx bx-plus-circle me-1"></i>{{ localize('global.depot.new_request') }}
                    </a>
                @endif
                @if($permissions['transaction_create'] ?? true)
                    <a href="{{ route('depots.transactions.create') }}?depot_id={{ $depot->id }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-slider-alt me-1"></i>{{ localize('global.depot.stock_adjustment') }}
                    </a>
                @endif
                @if(($permissions['movement_pharmacy'] ?? true) && $depot->pharmacy_id)
                    <a href="{{ route('depots.movements.depot_to_pharmacy', ['from_depot_id' => $depot->id]) }}" class="btn btn-sm btn-outline-danger">
                        <i class="bx bx-clinic me-1"></i>{{ localize('global.depot.depot_to_pharmacy') }}
                    </a>
                @endif
                <a href="{{ route('depots.stock', $depot) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-list-check me-1"></i>{{ localize('global.depot.full_stock') }}
                </a>
                @if($permissions['edit'] ?? true)
                    <a href="{{ route('depots.edit', $depot) }}" class="btn btn-sm btn-warning">
                        <i class="bx bx-edit me-1"></i>{{ localize('global.edit') }}
                    </a>
                @endif
                @if($permissions['delete'] ?? false)
                    <form action="{{ route('depots.destroy', $depot) }}" method="POST" class="d-inline-block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.confirm_delete') }}')">
                            <i class="bx bx-trash me-1"></i>{{ localize('global.delete') }}
                        </button>
                    </form>
                @endif
                <a href="{{ route('depots.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2">
                <span @class(['badge', 'bg-success' => $depot->is_active, 'bg-secondary' => ! $depot->is_active])>
                    {{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}
                </span>
                <span @class(['badge', 'bg-warning text-dark' => $depot->is_base, 'bg-secondary' => ! $depot->is_base])>
                    {{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}
                </span>
                <span class="badge bg-info">{{ $depot->branch?->name ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.depot.stock_summary') }}</div>
                    <h4 class="mb-0">{{ number_format($metrics['stock_items']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.quantity') }}</div>
                    <h4 class="mb-0">{{ number_format($metrics['total_quantity']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.depot.recent_transactions') }}</div>
                    <h4 class="mb-0">{{ number_format($metrics['recent_transactions']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.depot.requests') }}</div>
                    <h4 class="mb-0">{{ number_format($metrics['pending_requests']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">{{ localize('global.details') }}</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.branch') }}</div>
                    <div class="fw-semibold">{{ $depot->branch?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.department') }}</div>
                    <div class="fw-semibold">{{ $depot->department?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.pharmacy') }}</div>
                    <div class="fw-semibold">{{ $depot->pharmacy?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.parent_depot') }}</div>
                    <div class="fw-semibold">{{ $depot->parentDepot?->name ?? '-' }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.is_active') }}</div>
                    <div class="fw-semibold">{{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}</div>
                </div>
                <div class="col-md-4">
                    <div class="text-muted small">{{ localize('global.depot.is_base') }}</div>
                    <div class="fw-semibold">{{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ localize('global.depot.stock_summary') }}</h6>
                        <a href="{{ route('depots.stock', $depot) }}">
                            {{ localize('global.view') }} <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.item') }}</th>
                                    <th class="text-center">{{ localize('global.quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockItems as $item)
                                    <tr>
                                        <td>
                                            <span @class(['badge', 'bg-info' => $item['item_type'] === 'tool', 'bg-primary' => $item['item_type'] !== 'tool'])>{{ ucfirst($item['item_type']) }}</span>
                                        </td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-center">{{ number_format($item['available']) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted text-center">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ localize('global.depot.recent_transactions') }}</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.item') }}</th>
                                    <th class="text-center">{{ localize('global.quantity') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactionRows as $row)
                                    <tr>
                                        <td>
                                            <span @class([
                                                'badge',
                                                'bg-success' => $row['icon_class'] === 'is-in',
                                                'bg-danger' => $row['icon_class'] === 'is-out',
                                                'bg-warning text-dark' => $row['icon_class'] === 'is-adjustment',
                                                'bg-info' => $row['icon_class'] === 'is-transfer',
                                            ])>
                                                {{ $row['type_label'] }}
                                            </span>
                                        </td>
                                        <td>{{ $row['item_name'] }}</td>
                                        <td class="text-center">{{ number_format($row['quantity']) }}</td>
                                        <td>{{ $row['date'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-muted text-center">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ localize('global.depot.my_requests') }}</h6>
                    </div>
                    @forelse($pendingOutgoingRequests as $req)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong class="d-block">{{ $req->itemName() }}</strong>
                                <small class="text-muted">{{ localize('global.quantity') }}: {{ number_format($req->quantity) }}</small>
                            </div>
                            <a href="{{ route('depots.requests.show', $req) }}" class="btn btn-sm btn-outline-secondary">
                                <x-depot.status-badge :status="$req->status" :badge="false" />
                            </a>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">{{ localize('global.no_data_found') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">{{ localize('global.depot.incoming_requests') }}</h6>
                    </div>
                    @forelse($pendingIncomingRequests as $req)
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <strong class="d-block">{{ $req->requestingDepot?->name ?? '-' }}</strong>
                                <small class="text-muted">{{ $req->itemName() }} - {{ localize('global.quantity') }}: {{ number_format($req->quantity) }}</small>
                            </div>
                            <a href="{{ route('depots.requests.show', $req) }}" class="btn btn-sm btn-outline-secondary">
                                <x-depot.status-badge :status="$req->status" :badge="false" />
                            </a>
                        </div>
                    @empty
                        <div class="text-muted text-center py-3">{{ localize('global.no_data_found') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
