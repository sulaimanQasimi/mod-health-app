@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
    @endif

    <x-depot.nav-tabs active="transactions" :urls="$navUrls ?? []" :permissions="$navPermissions ?? null" />

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <h5 class="mb-0">{{ localize('global.depot.depot_transactions') }}</h5>
            <div class="d-flex flex-wrap gap-2">
                @if($navPermissions['reports'] ?? true)
                <a href="{{ route('depots.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-export me-1"></i>{{ localize('global.depot.reports') }}
                </a>
                @endif
                @if($navPermissions['requests'] ?? true)
                <a href="{{ route('depots.requests.create') }}" class="btn btn-outline-primary">
                    <i class="bx bx-transfer me-1"></i>{{ localize('global.depot.depot_to_depot') }}
                </a>
                <a href="{{ route('depots.requests.create', ['destination' => 'pharmacy']) }}" class="btn btn-outline-primary">
                    <i class="bx bx-clinic me-1"></i>{{ localize('global.depot.depot_to_pharmacy') }}
                </a>
                @endif
                @if($permissions['create'] ?? true)
                <a href="{{ route('depots.transactions.create') }}" class="btn btn-primary">
                    <i class="bx bx-plus me-1"></i>{{ localize('global.depot.new') }}
                </a>
                @endif
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('depots.transactions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="{{ localize('global.search') }}">
                </div>
                <div class="col-md-2">
                    <select name="depot_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.depots') }}</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" @selected(request('depot_id') == $depot->id)>{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="pharmacy_id" class="form-select select2">
                        <option value="">{{ localize('global.pharmacy') }}</option>
                        @foreach($pharmacies as $pharmacy)
                            <option value="{{ $pharmacy->id }}" @selected(request('pharmacy_id') == $pharmacy->id)>{{ $pharmacy->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="item_type" class="form-select">
                        <option value="">{{ localize('global.all') }}</option>
                        <option value="medicine" @selected(request('item_type') === 'medicine')>{{ localize('global.medicine') }}</option>
                        <option value="tool" @selected(request('item_type') === 'tool')>{{ localize('global.tool') }}</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="medicine_id" class="form-select select2">
                        <option value="">{{ localize('global.medicine') }}</option>
                        @foreach($medicines as $medicine)
                            <option value="{{ $medicine->id }}" @selected(request('medicine_id') == $medicine->id)>{{ $medicine->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="tool_id" class="form-select select2">
                        <option value="">{{ localize('global.depot.tool') }}</option>
                        @foreach($tools as $tool)
                            <option value="{{ $tool->id }}" @selected(request('tool_id') == $tool->id)>{{ $tool->displayName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="type" class="form-select">
                        <option value="">{{ localize('global.type') }}</option>
                        @foreach($typeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="status" class="form-select">
                        <option value="">{{ localize('global.status') }}</option>
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <select name="per_page" class="form-select" title="{{ localize('global.per_page') }}">
                        @foreach([10, 15, 25, 50] as $size)
                            <option value="{{ $size }}" @selected((int) request('per_page', 15) === $size)>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-secondary" type="submit"><i class="bx bx-search"></i></button>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <a href="{{ route('depots.transactions.index') }}" class="btn btn-outline-secondary w-100">{{ localize('global.reset') }}</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>{{ localize('global.number') }}</th>
                        <th>{{ localize('global.type') }}</th>
                        <th>{{ localize('global.source') }}</th>
                        <th>{{ localize('global.destination') }}</th>
                        <th>{{ localize('global.item') }}</th>
                        <th>{{ localize('global.tool') }}</th>
                        <th>{{ localize('global.quantity') }}</th>
                        <th>{{ localize('global.status') }}</th>
                        <th>{{ localize('global.created_by') }}</th>
                        <th>{{ localize('global.date') }}</th>
                        <th>{{ localize('global.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                        <tr>
                            <td>{{ $transaction->transaction_number }}</td>
                            <td><x-depot.type-label :type="$transaction->type" /></td>
                            <td>{{ $transaction->fromDepot?->name ?? $transaction->depot?->name ?? '-' }}</td>
                            <td>{{ $transaction->toDepot?->name ?? $transaction->pharmacy?->name ?? '-' }}</td>
                            <td>{{ $transaction->medicine?->name ?? '-' }}</td>
                            <td>{{ $transaction->tool?->displayName() ?? '-' }}</td>
                            <td>{{ number_format($transaction->quantity) }} {{ $transaction->unit?->symbol ?? $transaction->unit?->name }}</td>
                            <td>
                                <x-depot.status-badge :status="$transaction->status" type="transaction" />
                                @if($transaction->depotRequest)
                                    <a href="{{ route('depots.requests.show', $transaction->depotRequest) }}" class="badge bg-info text-decoration-none">{{ localize('global.depot.request') }}</a>
                                @endif
                            </td>
                            <td>{{ $transaction->createdBy?->name ?? '-' }}</td>
                            <td>{{ $transaction->transaction_date ? verta($transaction->transaction_date)->format('Y-m-d') : '-' }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary" href="{{ route('depots.transactions.show', $transaction) }}">
                                    <i class="bx bx-show-alt"></i>
                                </a>
                                @if(($permissions['cancel'] ?? true) && $transaction->status !== 'cancelled')
                                    <form action="{{ route('depots.transactions.cancel', $transaction) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.confirm_delete') }}')" type="submit">
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">{{ localize('global.no_data_found') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection
