@extends('layouts.master')

@section('styles')
    <style>
        /* Middle-tone stat cards (prosthetics dashboard) */
        .prosthetics-stat-card--mid-blue {
            background-color: rgba(59, 130, 246, 0.14) !important;
            border: 1px solid rgba(59, 130, 246, 0.22);
        }
        .prosthetics-stat-badge--mid-blue {
            background-color: #3b82f6 !important;
            color: #fff !important;
            border: none !important;
        }
        .prosthetics-stat-card--mid-green {
            background-color: rgba(34, 197, 94, 0.14) !important;
            border: 1px solid rgba(34, 197, 94, 0.25);
        }
        .prosthetics-stat-badge--mid-green {
            background-color: #22c55e !important;
            color: #fff !important;
        }
        .prosthetics-stat-card--mid-purple {
            background-color: rgba(147, 51, 234, 0.12) !important;
            border: 1px solid rgba(147, 51, 234, 0.22);
        }
        .prosthetics-stat-badge--mid-purple {
            background-color: #9333ea !important;
            color: #fff !important;
        }
    </style>
@endsection

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_dashboard') }}</h4>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('prosthetics.referrals.create') }}" class="btn btn-primary btn-sm">
                        {{ localize('global.prosthetics_new_referral') }}
                    </a>
                    <a href="{{ route('prosthetics.cases.create') }}" class="btn btn-outline-primary btn-sm">
                        {{ localize('global.prosthetics_new_case') }}
                    </a>
                    <a href="{{ route('prosthetics.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bx bx-list-ul me-1"></i>{{ localize('global.reports') }}
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_referrals') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                            {{ $referralPending }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-receipt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_workflow') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">
                                            {{ $waitingApproval }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-time-five bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_production_trial') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">
                                            {{ $inProduction }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-briefcase-alt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card bg-label-danger">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_workshop_orders') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-danger" style="font-size: xx-large;">
                                            {{ $workOrdersActive }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-danger rounded p-2">
                                    <i class="bx bx-cog bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $statusCards = [
                    'new' => ['bg' => 'bg-label-warning', 'badge' => 'bg-warning text-dark', 'icon' => 'bx bx-plus-circle', 'label_key' => 'global.new'],
                    'under_assessment' => ['bg' => 'bg-label-dark', 'badge' => 'bg-dark', 'icon' => 'bx bx-search', 'label_key' => 'global.prosthetics_case_status_under_assessment'],
                    'waiting_approval' => ['bg' => 'prosthetics-stat-card--mid-green', 'badge' => 'prosthetics-stat-badge--mid-green', 'icon' => 'bx bx-time-five', 'label_key' => 'global.prosthetics_case_status_waiting_approval'],
                    'approved' => ['bg' => 'prosthetics-stat-card--mid-blue', 'badge' => 'prosthetics-stat-badge--mid-blue', 'icon' => 'bx bx-check-circle', 'label_key' => 'global.approved'],
                    'in_production' => ['bg' => 'bg-label-linkedin', 'badge' => 'bg-linkedin', 'icon' => 'bx bx-cog', 'label_key' => 'global.prosthetics_case_status_in_production'],
                    'trial_fit' => ['bg' => 'bg-label-dribbble', 'badge' => 'bg-dribbble', 'icon' => 'bx bx-walk', 'label_key' => 'global.prosthetics_case_status_trial_fit'],
                    'delivered' => ['bg' => 'prosthetics-stat-card--mid-purple', 'badge' => 'prosthetics-stat-badge--mid-purple', 'icon' => 'bx bx-package', 'label_key' => 'global.delivered'],
                ];
            @endphp

            <div class="row g-4 mb-4">
                @foreach($statusCards as $st => $meta)
                    @php
                        $label = localize($meta['label_key']);
                        $count = $statusCounts[$st] ?? 0;
                    @endphp
                    <div class="col-sm-6 col-xl-3">
                        <div class="card {{ $meta['bg'] }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="content-left">
                                        <span>{{ $label }}</span>
                                        <div class="d-flex align-items-end mt-2">
                                            <h4 class="mb-0 me-2 badge badge-center {{ $meta['badge'] }}"
                                                style="font-size: xx-large;">
                                                {{ $count }}
                                            </h4>
                                        </div>
                                    </div>
                                    <span class="badge rounded p-2 {{ $meta['badge'] }}">
                                        <i class="{{ $meta['icon'] }} bx-lg"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.prosthetics_cases') }}</h5>
                            <a href="{{ route('prosthetics.reports.index') }}" class="btn btn-sm btn-outline-secondary">
                                {{ localize('global.reports') }}
                            </a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.prosthetics_case_number') }}</th>
                                            <th>{{ localize('global.patient_name') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentCases as $c)
                                            <tr>
                                                <td><code>{{ $c->case_number }}</code></td>
                                                <td>{{ $c->patient->name ?? '—' }}</td>
                                                <td>
                                                    @php
                                                        $caseStatusLabelKey = $statusCards[$c->status]['label_key'] ?? null;
                                                    @endphp
                                                    <span class="badge bg-label-secondary">{{ $caseStatusLabelKey ? localize($caseStatusLabelKey) : $c->status }}</span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('prosthetics.cases.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="bx bx-show me-1"></i>{{ localize('global.show') }}
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">{{ localize('global.no_item_is_found') }}</td>
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
