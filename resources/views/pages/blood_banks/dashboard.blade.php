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
                    <div class="card h-100 border-{{ $criticalExpiryCount > 0 ? 'danger' : 'secondary' }}">
                        <div class="card-body">
                            <div class="text-body-secondary small">{{ localize('global.critical_expiry_alert') }}
                                ({{ $criticalDays }} {{ localize('global.days') }})</div>
                            <div class="fs-3 fw-semibold">{{ $criticalExpiryCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-body-secondary small">{{ localize('global.pending_transfers_alert') }}</div>
                            <div class="fs-3 fw-semibold">{{ $pendingTransfersCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-body-secondary small">{{ localize('global.quarantine_units_title') }}</div>
                            <div class="fs-3 fw-semibold">{{ $quarantineCount }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="text-body-secondary small">{{ localize('global.expiring_blood_units') }}
                                ({{ $warningDays }}d)</div>
                            <div class="fs-3 fw-semibold">{{ $expiringSoon->count() }}</div>
                        </div>
                    </div>
                </div>
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
                @foreach (['new', 'approved', 'rejected', 'delivered'] as $st)
                    <div class="col-md-3 col-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="text-body-secondary small">{{ ucfirst($st) }}</div>
                                <div class="fs-3 fw-semibold">{{ $statusCounts[$st] ?? 0 }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

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
