@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_stock') }}</h4>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="bx bx-filter-alt me-1"></i>{{ localize('global.filters') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form method="get" action="{{ route('prosthetics.stock.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small mb-0">{{ localize('global.search') }}</label>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control form-control-sm"
                                   placeholder="{{ localize('global.prosthetics_search_component') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small mb-0">{{ localize('global.category') }}</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach (($categories ?? []) as $categoryOption)
                                    <option value="{{ $categoryOption }}" {{ (string) request('category') === (string) $categoryOption ? 'selected' : '' }}>
                                        {{ $categoryOption }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 d-flex flex-wrap gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="bx bx-search me-1"></i>{{ localize('global.filter') }}
                            </button>
                            <a href="{{ route('prosthetics.stock.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-reset me-1"></i>{{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @can('manage-prosthetics-stock')
                <div class="card mb-4">
                    <div class="card-header">{{ localize('global.prosthetics_receive_stock') }}</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('prosthetics.stock.receive') }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label">{{ localize('global.prosthetics_component') }}</label>
                                <select name="prosthetic_component_catalog_id" class="form-select form-select-sm" required>
                                    <option value="">—</option>
                                    @foreach ($catalogForReceive as $c)
                                        <option value="{{ $c->id }}">{{ $c->item_code }} — {{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">{{ localize('global.quantity') }}</label>
                                <input type="number" step="0.001" min="0.001" name="quantity" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ localize('global.notes') }}</label>
                                <input type="text" name="notes" class="form-control form-control-sm">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.save') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endcan

            <div class="card mb-3">
                <div class="card-header">{{ localize('global.prosthetics_stock_balances') }}</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.code') ?? 'Code' }}</th>
                                <th>{{ localize('global.name') }}</th>
                                <th>{{ localize('global.category') }}</th>
                                <th>{{ localize('global.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($balances as $b)
                                <tr>
                                    <td><code>{{ $b->catalogItem->item_code ?? '—' }}</code></td>
                                    <td>{{ $b->catalogItem->name ?? '—' }}</td>
                                    <td>{{ $b->catalogItem->category ?? '—' }}</td>
                                    <td>{{ $b->quantity }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ localize('global.no_item_is_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $balances->links('pagination::bootstrap-5') }}</div>
            </div>

            <div class="card">
                <div class="card-header">{{ localize('global.stock_movement_audit') }}</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.date') }}</th>
                                <th>{{ localize('global.prosthetics_component') }}</th>
                                <th>{{ localize('global.type') ?? 'Type' }}</th>
                                <th>Δ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($movements as $m)
                                <tr>
                                    <td>{{ $m->created_at }}</td>
                                    <td>{{ $m->catalogItem->item_code ?? '—' }} — {{ $m->catalogItem->name ?? '—' }}</td>
                                    <td>{{ $m->movement_type }}</td>
                                    <td>{{ $m->quantity_delta }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">{{ localize('global.no_item_is_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
