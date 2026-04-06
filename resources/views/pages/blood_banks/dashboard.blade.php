@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.blood_bank_dashboard') }}</h4>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('blood_banks.movements') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-list-ul me-1"></i>{{ localize('global.stock_movement_audit') }}
                    </a>
                    <a href="{{ route('blood_banks.branch_transfers.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bx bx-transfer me-1"></i>{{ localize('global.blood_branch_transfers') }}
                    </a>
                    <a href="{{ route('blood_banks.inventory') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-package me-1"></i>{{ localize('global.blood_inventory') }}
                    </a>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card h-100 {{ $criticalExpiryCount > 0 ? 'bg-label-danger' : 'bg-label-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.critical_expiry_alert') }}
                                        ({{ $criticalDays }} {{ localize('global.days') }})</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center {{ $criticalExpiryCount > 0 ? 'bg-danger' : 'bg-secondary' }}"
                                            style="font-size: xx-large;">
                                            {{ $criticalExpiryCount }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge {{ $criticalExpiryCount > 0 ? 'bg-danger' : 'bg-secondary' }} rounded p-2">
                                    <i class="bx bx-error-circle bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-warning">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.pending_transfers_alert') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-warning text-dark"
                                            style="font-size: xx-large;">
                                            {{ $pendingTransfersCount }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-warning text-dark rounded p-2">
                                    <i class="bx bx-transfer-alt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.quarantine_units_title') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-info"
                                            style="font-size: xx-large;">
                                            {{ $quarantineCount }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-shield-quarter bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.expiring_blood_units') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary"
                                            style="font-size: xx-large;">
                                            {{ $expiringSoon->count() }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-time-five bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                @foreach (['new', 'approved', 'rejected', 'delivered'] as $st)
                    <div class="col-md-3 col-6">
                        <div class="card h-100 {{ $st === 'approved' ? 'bg-label-success' : ($st === 'rejected' ? 'bg-label-danger' : ($st === 'delivered' ? 'bg-label-info' : 'bg-label-primary')) }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="content-left">
                                        <span>{{ localize('global.' . $st) ?? ucfirst($st) }}</span>
                                        <div class="d-flex align-items-end mt-2">
                                            <h4 class="mb-0 me-2 badge badge-center {{ $st === 'approved' ? 'bg-success' : ($st === 'rejected' ? 'bg-danger' : ($st === 'delivered' ? 'bg-info' : 'bg-primary')) }}"
                                                style="font-size: xx-large;">
                                                {{ $statusCounts[$st] ?? 0 }}
                                            </h4>
                                        </div>
                                    </div>
                                    <span class="badge {{ $st === 'approved' ? 'bg-success' : ($st === 'rejected' ? 'bg-danger' : ($st === 'delivered' ? 'bg-info' : 'bg-primary')) }} rounded p-2">
                                        <i class="bx {{ $st === 'approved' ? 'bx-check-circle' : ($st === 'rejected' ? 'bx-x-circle' : ($st === 'delivered' ? 'bx-package' : 'bx-loader-circle')) }} bx-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($lowStockRows->isNotEmpty())
                <div class="alert alert-warning mb-4">
                    <strong>{{ localize('global.low_stock_alert') }}</strong>
                    ({{ localize('global.threshold') }}: {{ $lowThreshold }})
                    <ul class="mb-0 mt-2">
                        @foreach ($lowStockRows as $row)
                            <li>{{ $row->blood_group }} {{ $row->rh }} — {{ $row->component_type }}:
                                <strong>{{ $row->c }}</strong></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">{{ localize('global.available_stock_summary') }}</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.blood_group') }}</th>
                                            <th>{{ localize('global.rh') }}</th>
                                            <th>{{ localize('global.component_type') }}</th>
                                            <th>{{ localize('global.quantity') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($availableByGroup as $row)
                                            <tr
                                                class="{{ (int) $row->c < $lowThreshold ? 'table-warning' : '' }}">
                                                <td>{{ $row->blood_group }}</td>
                                                <td>{{ $row->rh }}</td>
                                                <td>{{ $row->component_type }}</td>
                                                <td>{{ $row->c }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    {{ localize('global.no_item_is_found') }}
                                                </td>
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
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.expiring_blood_units') }}</h5>
                            <a href="{{ route('blood_banks.inventory', ['expires_within' => $warningDays, 'sort' => 'expires_at']) }}"
                                class="btn btn-sm btn-outline-primary">{{ localize('global.view_in_inventory') }}</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.bag_number') }}</th>
                                            <th>{{ localize('global.blood_group') }}</th>
                                            <th>{{ localize('global.rh') }}</th>
                                            <th>{{ localize('global.component_type') }}</th>
                                            <th>{{ localize('global.expires_at') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($expiringSoon as $u)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('blood_banks.inventory.show', $u) }}">{{ $u->bag_number }}</a>
                                                </td>
                                                <td>{{ $u->blood_group }}</td>
                                                <td>{{ $u->rh }}</td>
                                                <td>{{ $u->component_type }}</td>
                                                <td dir="ltr">{{ $u->expires_at?->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    {{ localize('global.no_item_is_found') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
