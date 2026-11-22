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
                <small class="text-muted">{{ localize('global.depot.full_stock') }}</small>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @if($permissions['request_create'] ?? true)
                    <a href="{{ route('depots.requests.create', ['source_depot_id' => $depot->id]) }}" class="btn btn-sm btn-success">
                        <i class="bx bx-plus-circle me-1"></i>{{ localize('global.depot.new_request') }}
                    </a>
                @endif
                @if($permissions['transaction_create'] ?? true)
                    <a href="{{ route('depots.transactions.create') }}?depot_id={{ $depot->id }}" class="btn btn-sm btn-outline-secondary">
                        <i class="bx bx-slider-alt me-1"></i>{{ localize('global.depot.stock_adjustment') }}
                    </a>
                @endif
                <a href="{{ route('depots.show', $depot) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bx bx-show-alt me-1"></i>{{ localize('global.view') }}
                </a>
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
                <span class="badge bg-info">{{ $itemType ? ucfirst($itemType) : localize('global.all') }}</span>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.item') }}</div>
                    <h4 class="mb-0">{{ number_format($stockCount) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.quantity') }}</div>
                    <h4 class="mb-0">{{ number_format($stockTotal) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.medicine') }}</div>
                    <h4 class="mb-0">{{ number_format($medicineCount) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small">{{ localize('global.depot.tool') }}</div>
                    <h4 class="mb-0">{{ number_format($toolCount) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach($statusChips as $chip)
                    <a
                        href="{{ $chip['url'] }}"
                        @class(['btn btn-sm', 'btn-primary' => $chip['active'], 'btn-outline-secondary' => ! $chip['active']])
                    >
                        <i class="bx {{ $chip['icon'] }} me-1"></i>
                        {{ $chip['label'] }}
                        @if($chip['count'] !== null)
                            <span class="badge bg-label-secondary ms-1">{{ number_format($chip['count']) }}</span>
                        @endif
                    </a>
                @endforeach
            </div>

            <form method="GET" class="row g-3 align-items-end mb-3">
                @if($activeStatus !== '')
                    <input type="hidden" name="stock_status" value="{{ $activeStatus }}">
                @endif
                <div class="col-md-3">
                    <label class="form-label" for="search">{{ localize('global.search') }}</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="{{ localize('global.search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="item_type">{{ localize('global.type') }}</label>
                    <select name="item_type" id="item_type" class="form-select">
                        <option value="">{{ localize('global.all') }}</option>
                        <option value="medicine" @selected($itemType === 'medicine')>{{ localize('global.medicine') }}</option>
                        <option value="tool" @selected($itemType === 'tool')>{{ localize('global.depot.tool') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="sort_by">{{ localize('global.sort_by') }}</label>
                    <select name="sort_by" id="sort_by" class="form-select">
                        <option value="name" @selected(($filters['sort_by'] ?? 'name') === 'name')>{{ localize('global.name') }}</option>
                        <option value="quantity" @selected(($filters['sort_by'] ?? '') === 'quantity')>{{ localize('global.quantity') }}</option>
                        <option value="item_type" @selected(($filters['sort_by'] ?? '') === 'item_type')>{{ localize('global.type') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="sort_order">{{ localize('global.sort_order') }}</label>
                    <select name="sort_order" id="sort_order" class="form-select">
                        <option value="asc" @selected(($filters['sort_order'] ?? 'asc') === 'asc')>{{ localize('global.ascending') }}</option>
                        <option value="desc" @selected(($filters['sort_order'] ?? '') === 'desc')>{{ localize('global.descending') }}</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                    </button>
                    <a href="{{ route('depots.stock', $depot) }}" class="btn btn-outline-secondary">
                        <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') }}
                    </a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>{{ localize('global.item') }}</th>
                            <th>{{ localize('global.type') }}</th>
                            <th>{{ localize('global.stock_status') }}</th>
                            <th class="text-center">{{ localize('global.available_stock') }}</th>
                            <th class="text-center">{{ localize('global.unit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockItems as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item['name'] }}</strong>
                                </td>
                                <td>
                                    <span @class(['badge', 'bg-info' => $item['is_tool'], 'bg-primary' => ! $item['is_tool']])>
                                        {{ $item['item_type_label'] }}
                                    </span>
                                </td>
                                <td>
                                    <x-depot.stock-level
                                        :quantity="(int) $item['available']"
                                        :status="$item['stock_status']"
                                        :max-quantity="$maxQuantity"
                                    />
                                </td>
                                <td class="text-center">
                                    {{ number_format($item['available']) }}
                                </td>
                                <td class="text-center">
                                    {{ $item['unit'] ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted text-center">{{ localize('global.no_data_found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
