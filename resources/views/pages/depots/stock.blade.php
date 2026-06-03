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

    .depot-filter-body {
        padding: 1rem;
        background: #fff;
    }

    .depot-filter-body .form-control,
    .depot-filter-body .form-select {
        border-radius: 6px;
        border-color: var(--depot-line);
        min-height: 42px;
    }

    .depot-filter-body .form-label {
        color: var(--depot-muted);
        font-size: .78rem;
        font-weight: 800;
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

    .depot-stock-item {
        display: flex;
        align-items: center;
        gap: .75rem;
        min-width: 220px;
    }

    .depot-stock-icon {
        display: inline-grid;
        place-items: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 8px;
        color: var(--depot-primary);
        background: rgba(39, 84, 199, .09);
        font-size: 1.2rem;
    }

    .depot-stock-icon.is-tool {
        color: #6f42c1;
        background: rgba(111, 66, 193, .1);
    }

    .depot-stock-name {
        color: var(--depot-ink);
        font-weight: 800;
        line-height: 1.25;
    }

    .depot-type-badge {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .25rem .55rem;
        border-radius: 6px;
        color: var(--depot-primary);
        background: rgba(39, 84, 199, .09);
        font-weight: 700;
        font-size: .78rem;
        text-transform: capitalize;
    }

    .depot-type-badge.is-tool {
        color: #6f42c1;
        background: rgba(111, 66, 193, .1);
    }

    .depot-quantity {
        display: inline-flex;
        min-width: 66px;
        justify-content: center;
        padding: .3rem .6rem;
        border-radius: 6px;
        color: #0e6e5d;
        background: rgba(15, 159, 143, .1);
        font-weight: 800;
    }

    .depot-unit {
        display: inline-flex;
        min-width: 48px;
        justify-content: center;
        padding: .25rem .55rem;
        border-radius: 6px;
        color: #6b7280;
        background: rgba(107, 114, 128, .1);
        font-weight: 700;
    }

    .depot-empty {
        padding: 1.6rem 1rem;
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
    }

    @media (max-width: 575.98px) {
        .depot-hero-inner {
            padding: 1.1rem;
        }

        .depot-metric-grid {
            grid-template-columns: 1fr;
        }

        .depot-actions .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
@php
    $stockCount = $stockItems->count();
    $stockTotal = $stockItems->sum('available');
    $medicineCount = $stockItems->where('item_type', 'medicine')->count();
    $toolCount = $stockItems->where('item_type', 'tool')->count();
    $statusClass = $depot->is_active ? 'depot-pill-success' : 'depot-pill-muted';
@endphp

<div class="container-xxl flex-grow-1 container-p-y depot-show">
    <div class="depot-shell">
        <section class="depot-hero">
            <div class="depot-hero-inner">
                <div>
                    <div class="depot-kicker">
                        <i class="bx bx-list-check"></i>
                        {{ localize('global.depot.full_stock') }}
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
                            <i class="bx bx-filter-alt"></i>
                            {{ $itemType ? ucfirst($itemType) : localize('global.all') }}
                        </span>
                    </div>
                </div>

                <div class="depot-actions">
                    @can('depot.transaction.create')
                    <a href="{{ route('depots.transactions.create') }}?depot_id={{ $depot->id }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-package me-1"></i>{{ localize('global.depot.new') }}
                    </a>
                    @endcan
                    <a href="{{ route('depots.show', $depot) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-show-alt me-1"></i>{{ localize('global.view') }}
                    </a>
                    <a href="{{ route('depots.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-arrow-back me-1"></i>{{ localize('global.back') }}
                    </a>
                </div>
            </div>
        </section>

        <div class="depot-metric-grid">
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-box"></i></span>
                <div>
                    <div class="depot-metric-label">{{ localize('global.item') }}</div>
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
                <span class="depot-metric-icon"><i class="bx bx-capsule"></i></span>
                <div>
                    <div class="depot-metric-label">Medicine</div>
                    <div class="depot-metric-value">{{ number_format($medicineCount) }}</div>
                </div>
            </div>
            <div class="depot-metric">
                <span class="depot-metric-icon"><i class="bx bx-wrench"></i></span>
                <div>
                    <div class="depot-metric-label">Tool</div>
                    <div class="depot-metric-value">{{ number_format($toolCount) }}</div>
                </div>
            </div>
        </div>

        <div class="depot-panel">
            <div class="depot-panel-header">
                <h2 class="depot-panel-title h6">
                    <i class="bx bx-filter-alt"></i>
                    {{ localize('global.filters') }}
                </h2>
            </div>
            <div class="depot-filter-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-5 col-md-6">
                        <label class="form-label" for="search">{{ localize('global.search') }}</label>
                        <input type="text" id="search" name="search" value="{{ $search }}" class="form-control" placeholder="{{ localize('global.search') }}">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label" for="item_type">{{ localize('global.type') }}</label>
                        <select name="item_type" id="item_type" class="form-select">
                            <option value="">{{ localize('global.all') }}</option>
                            <option value="medicine" @selected($itemType === 'medicine')>Medicine</option>
                            <option value="tool" @selected($itemType === 'tool')>Tool</option>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <button class="btn btn-primary w-100" type="submit">
                            <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <a href="{{ route('depots.stock.index', $depot) }}" class="btn btn-outline-secondary w-100">
                            <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="depot-panel">
            <div class="depot-panel-header">
                <h2 class="depot-panel-title h6">
                    <i class="bx bx-list-ul"></i>
                    {{ localize('global.depot.stock_summary') }}
                </h2>
                <span class="depot-pill">
                    <i class="bx bx-data"></i>
                    {{ number_format($stockCount) }}
                </span>
            </div>
            <div class="table-responsive">
                <table class="table depot-table">
                    <thead>
                        <tr>
                            <th>{{ localize('global.item') }}</th>
                            <th>{{ localize('global.type') }}</th>
                            <th class="text-center">Available</th>
                            <th class="text-center">{{ localize('global.unit') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stockItems as $item)
                            @php
                                $isTool = $item['item_type'] === 'tool';
                            @endphp
                            <tr>
                                <td>
                                    <div class="depot-stock-item">
                                        <span class="depot-stock-icon {{ $isTool ? 'is-tool' : '' }}">
                                            <i class="bx {{ $isTool ? 'bx-wrench' : 'bx-capsule' }}"></i>
                                        </span>
                                        <span class="depot-stock-name">{{ $item['name'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="depot-type-badge {{ $isTool ? 'is-tool' : '' }}">
                                        <i class="bx {{ $isTool ? 'bx-wrench' : 'bx-capsule' }}"></i>
                                        {{ ucfirst($item['item_type']) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="depot-quantity">{{ number_format($item['available']) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="depot-unit">{{ $item['unit'] ?? '-' }}</span>
                                </td>
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
</div>
@endsection
