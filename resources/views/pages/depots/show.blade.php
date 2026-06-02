@extends('layouts.master')

@section('styles')
<style>
    .depot-show {
        --depot-ink: #172033;
        --depot-muted: #6b7280;
        --depot-line: rgba(23, 32, 51, .09);
        --depot-soft: #f6f8fb;
        --depot-primary: #2754c7;
        --depot-accent: #0f9f8f;
    }

    .depot-shell {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .depot-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(39, 84, 199, .14);
        border-radius: 8px;
        background:
            radial-gradient(circle at 12% 8%, rgba(15, 159, 143, .18), transparent 28%),
            linear-gradient(135deg, #ffffff 0%, #eef5ff 58%, #f9fcff 100%);
        box-shadow: 0 18px 45px rgba(35, 44, 72, .08);
    }

    .depot-hero::after {
        content: "";
        position: absolute;
        inset-inline-end: -80px;
        top: -100px;
        width: 280px;
        height: 280px;
        border: 36px solid rgba(39, 84, 199, .07);
        border-radius: 50%;
    }

    .depot-hero-inner {
        position: relative;
        z-index: 1;
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1.25rem;
        padding: 1.5rem;
    }

    .depot-kicker {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--depot-primary);
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    .depot-title {
        color: var(--depot-ink);
        font-size: clamp(1.5rem, 2.2vw, 2rem);
        font-weight: 800;
        line-height: 1.2;
        margin: .4rem 0 .55rem;
    }

    .depot-subtitle {
        color: var(--depot-muted);
        max-width: 760px;
        margin: 0;
    }

    .depot-status-row {
        display: flex;
        flex-wrap: wrap;
        gap: .55rem;
        margin-top: 1rem;
    }

    .depot-pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        min-height: 32px;
        padding: .35rem .7rem;
        border: 1px solid rgba(23, 32, 51, .08);
        border-radius: 999px;
        background: rgba(255, 255, 255, .82);
        color: var(--depot-ink);
        font-weight: 700;
        font-size: .82rem;
        box-shadow: 0 8px 22px rgba(35, 44, 72, .06);
    }

    .depot-pill-success {
        color: #127852;
        background: rgba(35, 187, 133, .13);
        border-color: rgba(35, 187, 133, .2);
    }

    .depot-pill-muted {
        color: #6b7280;
        background: rgba(107, 114, 128, .1);
        border-color: rgba(107, 114, 128, .16);
    }

    .depot-pill-primary {
        color: var(--depot-primary);
        background: rgba(39, 84, 199, .1);
        border-color: rgba(39, 84, 199, .16);
    }

    .depot-actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        align-content: flex-start;
        gap: .55rem;
        max-width: 430px;
    }

    .depot-actions .btn {
        border-radius: 6px;
        box-shadow: none;
        white-space: nowrap;
    }

    .depot-metric-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
    }

    .depot-metric {
        display: flex;
        gap: .85rem;
        align-items: center;
        min-height: 92px;
        padding: 1rem;
        border: 1px solid var(--depot-line);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(35, 44, 72, .055);
    }

    .depot-metric-icon {
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
        border-radius: 8px;
        color: #fff;
        background: linear-gradient(135deg, var(--depot-primary), #5a7ee8);
        font-size: 1.35rem;
    }

    .depot-metric:nth-child(2) .depot-metric-icon {
        background: linear-gradient(135deg, var(--depot-accent), #2fc5b8);
    }

    .depot-metric:nth-child(3) .depot-metric-icon {
        background: linear-gradient(135deg, #f59f20, #f7c46c);
    }

    .depot-metric:nth-child(4) .depot-metric-icon {
        background: linear-gradient(135deg, #6f42c1, #9a7be2);
    }

    .depot-metric-label {
        color: var(--depot-muted);
        font-size: .78rem;
        font-weight: 700;
        margin-bottom: .15rem;
    }

    .depot-metric-value {
        color: var(--depot-ink);
        font-size: 1.35rem;
        font-weight: 800;
        line-height: 1.15;
    }

    .depot-panel {
        height: 100%;
        overflow: hidden;
        border: 1px solid var(--depot-line);
        border-radius: 8px;
        background: #fff;
        box-shadow: 0 10px 28px rgba(35, 44, 72, .055);
    }

    .depot-panel-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-bottom: 1px solid var(--depot-line);
        background: linear-gradient(180deg, #fff, #fbfcff);
    }

    .depot-panel-title {
        display: flex;
        align-items: center;
        gap: .55rem;
        color: var(--depot-ink);
        font-weight: 800;
        margin: 0;
    }

    .depot-panel-title i {
        color: var(--depot-primary);
        font-size: 1.15rem;
    }

    .depot-panel-link {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        color: var(--depot-primary);
        font-weight: 700;
        font-size: .84rem;
    }

    .depot-info-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        padding: 1rem;
    }

    .depot-info-item {
        min-height: 76px;
        padding: .9rem;
        border: 1px solid var(--depot-line);
        border-radius: 8px;
        background: var(--depot-soft);
    }

    .depot-info-label {
        color: var(--depot-muted);
        font-size: .76rem;
        font-weight: 700;
        margin-bottom: .35rem;
    }

    .depot-info-value {
        color: var(--depot-ink);
        font-weight: 800;
        line-height: 1.35;
    }

    .depot-table {
        margin: 0;
    }

    .depot-table thead th {
        border-bottom: 1px solid var(--depot-line);
        color: var(--depot-muted);
        font-size: .73rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
        background: #fbfcff;
    }

    .depot-table tbody td {
        vertical-align: middle;
        color: var(--depot-ink);
        border-color: rgba(23, 32, 51, .06);
    }

    .depot-quantity {
        display: inline-flex;
        min-width: 52px;
        justify-content: center;
        padding: .25rem .5rem;
        border-radius: 6px;
        color: #0e6e5d;
        background: rgba(15, 159, 143, .1);
        font-weight: 800;
    }

    .depot-type-badge {
        display: inline-flex;
        align-items: center;
        padding: .25rem .5rem;
        border-radius: 6px;
        color: var(--depot-primary);
        background: rgba(39, 84, 199, .09);
        font-weight: 700;
        font-size: .78rem;
        text-transform: capitalize;
    }

    .depot-transaction-type {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: var(--depot-ink);
        font-weight: 800;
        text-transform: capitalize;
    }

    .depot-transaction-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 30px;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        font-size: 1.05rem;
    }

    .depot-transaction-icon.is-in {
        color: #0e6e5d;
        background: rgba(15, 159, 143, .12);
    }

    .depot-transaction-icon.is-out {
        color: #c2410c;
        background: rgba(249, 115, 22, .13);
    }

    .depot-transaction-icon.is-transfer {
        color: var(--depot-primary);
        background: rgba(39, 84, 199, .1);
    }

    .depot-transaction-icon.is-adjustment {
        color: #6f42c1;
        background: rgba(111, 66, 193, .1);
    }

    .depot-request-list {
        display: flex;
        flex-direction: column;
        gap: .7rem;
        padding: 1rem;
    }

    .depot-request-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: .85rem;
        border: 1px solid var(--depot-line);
        border-radius: 8px;
        background: #fff;
    }

    .depot-request-name {
        color: var(--depot-ink);
        font-weight: 800;
        margin-bottom: .2rem;
    }

    .depot-request-meta {
        color: var(--depot-muted);
        font-size: .82rem;
    }

    .depot-empty {
        padding: 1.4rem 1rem;
        color: var(--depot-muted);
        text-align: center;
    }

    @media (max-width: 1199.98px) {
        .depot-metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .depot-hero-inner {
            grid-template-columns: 1fr;
        }

        .depot-actions {
            justify-content: flex-start;
            max-width: none;
        }

        .depot-info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575.98px) {
        .depot-hero-inner {
            padding: 1.1rem;
        }

        .depot-metric-grid,
        .depot-info-grid {
            grid-template-columns: 1fr;
        }

        .depot-actions .btn {
            width: 100%;
            justify-content: center;
        }

        .depot-request-item {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>
@endsection

@section('content')
@php
    $stockCount = $stockItems->count();
    $stockTotal = $stockItems->sum('available');
    $transactionCount = $recentTransactions->count();
    $requestCount = $pendingOutgoingRequests->count() + $pendingIncomingRequests->count();
    $statusClass = $depot->is_active ? 'depot-pill-success' : 'depot-pill-muted';
@endphp

<div class="container-xxl flex-grow-1 container-p-y depot-show">
    <div class="depot-shell">
        <section class="depot-hero">
            <div class="depot-hero-inner">
                <div>
                    <div class="depot-kicker">
                        <i class="bx bx-store-alt"></i>
                        {{ localize('global.depot.title') }}
                    </div>
                    <h1 class="depot-title">{{ $depot->name }}</h1>
                    <p class="depot-subtitle">{{ $depot->address ?: localize('global.depot.address') . ': -' }}</p>
                    <div class="depot-status-row">
                        <span class="depot-pill {{ $statusClass }}">
                            <i class="bx bx-radio-circle-marked"></i>
                            {{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}
                        </span>
                        <span class="depot-pill {{ $depot->is_base ? 'depot-pill-primary' : 'depot-pill-muted' }}">
                            <i class="bx bx-git-branch"></i>
                            {{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}
                        </span>
                        <span class="depot-pill">
                            <i class="bx bx-buildings"></i>
                            {{ $depot->branch?->name ?? '-' }}
                        </span>
                    </div>
                </div>

                <div class="depot-actions">
                    @can('depot.request.create')
                    <a href="{{ route('depots.requests.create') }}?requesting_depot_id={{ $depot->id }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus-circle me-1"></i>{{ localize('global.depot.new_request') }}
                    </a>
                    @endcan
                    @can('depot.transaction.create')
                    <a href="{{ route('depots.transactions.create') }}?depot_id={{ $depot->id }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-package me-1"></i>{{ localize('global.depot.new') }}
                    </a>
                    @endcan
                    @if($depot->pharmacy_id)
                    @can('depot.movement.depot_to_pharmacy')
                    <a href="{{ route('depots.movements.depot_to_pharmacy') }}?from_depot_id={{ $depot->id }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-clinic me-1"></i>{{ localize('global.depot.depot_to_pharmacy') }}
                    </a>
                    @endcan
                    @endif
                    <a href="{{ route('depots.stock.index', $depot) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-list-check me-1"></i>{{ localize('global.depot.full_stock') }}
                    </a>
                    @can('depot.update')
                    <a href="{{ route('depots.edit', $depot) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-edit me-1"></i>{{ localize('global.edit') }}
                    </a>
                    @endcan
                    <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                    </a>
                </div>
            </div>
        </section>

        <div class="depot-metric-grid">
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-capsule"></i></span>
                <div>
                    <div class="depot-metric-label">{{ localize('global.depot.stock_summary') }}</div>
                    <div class="depot-metric-value">{{ number_format($stockCount) }}</div>
                </div>
            </div>
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-layer"></i></span>
                <div>
                    <div class="depot-metric-label">{{ localize('global.quantity') }}</div>
                    <div class="depot-metric-value">{{ number_format($stockTotal) }}</div>
                </div>
            </div>
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-transfer"></i></span>
                <div>
                    <div class="depot-metric-label">{{ localize('global.depot.recent_transactions') }}</div>
                    <div class="depot-metric-value">{{ number_format($transactionCount) }}</div>
                </div>
            </div>
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-time-five"></i></span>
                <div>
                    <div class="depot-metric-label">{{ localize('global.depot.requests') }}</div>
                    <div class="depot-metric-value">{{ number_format($requestCount) }}</div>
                </div>
            </div>
        </div>

        <div class="depot-panel">
            <div class="depot-panel-header">
                <h2 class="depot-panel-title h6">
                    <i class="bx bx-info-circle"></i>
                    {{ localize('global.details') }}
                </h2>
            </div>
            <div class="depot-info-grid">
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.branch') }}</div>
                    <div class="depot-info-value">{{ $depot->branch?->name ?? '-' }}</div>
                </div>
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.department') }}</div>
                    <div class="depot-info-value">{{ $depot->department?->name ?? '-' }}</div>
                </div>
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.pharmacy') }}</div>
                    <div class="depot-info-value">{{ $depot->pharmacy?->name ?? '-' }}</div>
                </div>
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.parent_depot') }}</div>
                    <div class="depot-info-value">{{ $depot->parentDepot?->name ?? '-' }}</div>
                </div>
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.is_active') }}</div>
                    <div class="depot-info-value">{{ $depot->is_active ? localize('global.active') : localize('global.inactive') }}</div>
                </div>
                <div class="depot-info-item">
                    <div class="depot-info-label">{{ localize('global.depot.is_base') }}</div>
                    <div class="depot-info-value">{{ $depot->is_base ? localize('global.depot.base') : localize('global.depot.child') }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-xl-6">
                <div class="depot-panel">
                    <div class="depot-panel-header">
                        <h2 class="depot-panel-title h6">
                            <i class="bx bx-box"></i>
                            {{ localize('global.depot.stock_summary') }}
                        </h2>
                        <a class="depot-panel-link" href="{{ route('depots.stock.index', $depot) }}">
                            {{ localize('global.view') }} <i class="bx bx-chevron-right"></i>
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table depot-table">
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
                                        <td><span class="depot-type-badge">{{ ucfirst($item['item_type']) }}</span></td>
                                        <td>{{ $item['name'] }}</td>
                                        <td class="text-center"><span class="depot-quantity">{{ number_format($item['available']) }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="depot-empty">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="depot-panel">
                    <div class="depot-panel-header">
                        <h2 class="depot-panel-title h6">
                            <i class="bx bx-transfer-alt"></i>
                            {{ localize('global.depot.recent_transactions') }}
                        </h2>
                    </div>
                    <div class="table-responsive">
                        <table class="table depot-table">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.item') }}</th>
                                    <th class="text-center">{{ localize('global.quantity') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $txn)
                                    @php
                                        $isDepotTransfer = $txn->type === \App\Models\DepotTransaction::TYPE_DEPOT_TO_DEPOT;
                                        $isIncomingTransfer = $isDepotTransfer && (int) $txn->to_depot_id === (int) $depot->id;
                                        $isOutgoingTransfer = $isDepotTransfer && (int) $txn->from_depot_id === (int) $depot->id;
                                        $transactionDirection = match (true) {
                                            in_array($txn->type, [\App\Models\DepotTransaction::TYPE_STOCK_IN, \App\Models\DepotTransaction::TYPE_ADJUSTMENT], true), $isIncomingTransfer => 'in',
                                            in_array($txn->type, [\App\Models\DepotTransaction::TYPE_STOCK_OUT, \App\Models\DepotTransaction::TYPE_DEPOT_TO_PHARMACY], true), $isOutgoingTransfer => 'out',
                                            default => 'transfer',
                                        };
                                        $transactionIcon = match ($transactionDirection) {
                                            'in' => 'bx-down-arrow-alt',
                                            'out' => 'bx-up-arrow-alt',
                                            default => 'bx-transfer-alt',
                                        };
                                        $transactionIconClass = $txn->type === \App\Models\DepotTransaction::TYPE_ADJUSTMENT
                                            ? 'is-adjustment'
                                            : 'is-' . $transactionDirection;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="depot-transaction-type">
                                                <span class="depot-transaction-icon {{ $transactionIconClass }}">
                                                    <i class="bx {{ $transactionIcon }}"></i>
                                                </span>
                                                {{ str_replace('_', ' ', $txn->type) }}
                                            </span>
                                        </td>
                                        <td>{{ $txn->medicine?->name ?? $txn->tool?->name ?? '-' }}</td>
                                        <td class="text-center"><span class="depot-quantity">{{ number_format($txn->quantity) }}</span></td>
                                        <td>{{ $txn->transaction_date ? verta($txn->transaction_date)->format('Y-m-d') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="depot-empty">{{ localize('global.no_data_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="depot-panel">
                    <div class="depot-panel-header">
                        <h2 class="depot-panel-title h6">
                            <i class="bx bx-send"></i>
                            {{ localize('global.depot.my_requests') }}
                        </h2>
                    </div>
                    <div class="depot-request-list">
                        @forelse($pendingOutgoingRequests as $req)
                            <div class="depot-request-item">
                                <div>
                                    <div class="depot-request-name">{{ $req->itemName() }}</div>
                                    <div class="depot-request-meta">{{ localize('global.quantity') }}: {{ number_format($req->quantity) }}</div>
                                </div>
                                <a href="{{ route('depots.requests.show', $req) }}" class="btn btn-outline-primary btn-sm">
                                    {{ ucfirst($req->status) }}
                                </a>
                            </div>
                        @empty
                            <div class="depot-empty">{{ localize('global.no_data_found') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="depot-panel">
                    <div class="depot-panel-header">
                        <h2 class="depot-panel-title h6">
                            <i class="bx bx-download"></i>
                            {{ localize('global.depot.incoming_requests') }}
                        </h2>
                    </div>
                    <div class="depot-request-list">
                        @forelse($pendingIncomingRequests as $req)
                            <div class="depot-request-item">
                                <div>
                                    <div class="depot-request-name">{{ $req->requestingDepot?->name ?? '-' }}</div>
                                    <div class="depot-request-meta">{{ $req->itemName() }} - {{ localize('global.quantity') }}: {{ number_format($req->quantity) }}</div>
                                </div>
                                <a href="{{ route('depots.requests.show', $req) }}" class="btn btn-outline-primary btn-sm">
                                    {{ ucfirst($req->status) }}
                                </a>
                            </div>
                        @empty
                            <div class="depot-empty">{{ localize('global.no_data_found') }}</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
