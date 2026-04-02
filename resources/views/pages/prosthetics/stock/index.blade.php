@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <h4 class="mb-3">{{ localize('global.prosthetics_stock') }}</h4>

            @can('manage-prosthetics-stock')
                <div class="card mb-4">
                    <div class="card-header">{{ localize('global.receive_blood_unit') ?? 'Receive stock' }}</div>
                    <div class="card-body">
                        <form method="post" action="{{ route('prosthetics.stock.receive') }}" class="row g-2 align-items-end">
                            @csrf
                            <div class="col-md-5">
                                <label class="form-label">Component</label>
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
                <div class="card-header">{{ localize('global.blood_inventory') ?? 'Balances' }}</div>
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>{{ localize('global.name') }}</th>
                                <th>{{ localize('global.quantity') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($balances as $b)
                                <tr>
                                    <td><code>{{ $b->catalogItem->item_code ?? '—' }}</code></td>
                                    <td>{{ $b->catalogItem->name ?? '—' }}</td>
                                    <td>{{ $b->quantity }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">{{ localize('global.no_item_is_found') }}</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $balances->links('pagination::bootstrap-5') }}</div>
            </div>

            <div class="card">
                <div class="card-header">{{ localize('global.stock_movement_audit') ?? 'Recent movements' }}</div>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.date') ?? 'When' }}</th>
                                <th>Type</th>
                                <th>Δ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($movements as $m)
                                <tr>
                                    <td>{{ $m->created_at }}</td>
                                    <td>{{ $m->movement_type }}</td>
                                    <td>{{ $m->quantity_delta }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
