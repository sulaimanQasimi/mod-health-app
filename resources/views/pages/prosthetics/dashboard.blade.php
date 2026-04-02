@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

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

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card h-100 {{ $referralPending > 0 ? 'bg-label-warning' : 'bg-label-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_referrals') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center {{ $referralPending > 0 ? 'bg-warning' : 'bg-secondary text-dark' }}"
                                            style="font-size: xx-large;">
                                            {{ $referralPending }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge {{ $referralPending > 0 ? 'bg-warning text-dark' : 'bg-secondary text-dark' }} rounded p-2">
                                    <i class="bx bx-receipt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 {{ $waitingApproval > 0 ? 'bg-label-info' : 'bg-label-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_workflow') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center {{ $waitingApproval > 0 ? 'bg-info' : 'bg-secondary text-dark' }}"
                                            style="font-size: xx-large;">
                                            {{ $waitingApproval }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge {{ $waitingApproval > 0 ? 'bg-info' : 'bg-secondary' }} rounded p-2">
                                    <i class="bx bx-time-five bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 {{ $inProduction > 0 ? 'bg-label-primary' : 'bg-label-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_production_trial') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center {{ $inProduction > 0 ? 'bg-primary' : 'bg-secondary text-dark' }}"
                                            style="font-size: xx-large;">
                                            {{ $inProduction }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge {{ $inProduction > 0 ? 'bg-primary' : 'bg-secondary' }} rounded p-2">
                                    <i class="bx bx-briefcase-alt bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 {{ $workOrdersActive > 0 ? 'bg-label-secondary' : 'bg-label-secondary' }}">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.prosthetics_workshop_orders') }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-secondary" style="font-size: xx-large;">
                                            {{ $workOrdersActive }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-secondary rounded p-2">
                                    <i class="bx bx-cog bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @php
                $statusCards = [
                    'new' => ['bg' => 'bg-label-primary', 'icon' => 'bx bx-plus-circle'],
                    'under_assessment' => ['bg' => 'bg-label-info', 'icon' => 'bx bx-search'],
                    'waiting_approval' => ['bg' => 'bg-label-warning', 'icon' => 'bx bx-time-five'],
                    'approved' => ['bg' => 'bg-label-success', 'icon' => 'bx bx-check-circle'],
                    'in_production' => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-cog'],
                    'trial_fit' => ['bg' => 'bg-label-secondary', 'icon' => 'bx bx-walk'],
                    'delivered' => ['bg' => 'bg-label-success', 'icon' => 'bx bx-package'],
                ];
            @endphp

            <div class="row g-3 mb-4">
                @foreach($statusCards as $st => $meta)
                    @php
                        $label = localize('global.' . $st);
                        if ($label === 'global.' . $st) {
                            $label = ucwords(str_replace('_', ' ', $st));
                        }
                        $count = $statusCounts[$st] ?? 0;
                    @endphp
                    <div class="col-md-3 col-6">
                        <div class="card h-100 {{ $meta['bg'] }}">
                            <div class="card-body">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div class="content-left">
                                        <span>{{ $label }}</span>
                                        <div class="d-flex align-items-end mt-2">
                                            <h4 class="mb-0 me-2 badge badge-center"
                                                style="font-size: xx-large;">
                                                {{ $count }}
                                            </h4>
                                        </div>
                                    </div>
                                    <span class="badge rounded p-2 {{ $meta['bg'] }}">
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
                                                <td><span class="badge bg-label-secondary">{{ $c->status }}</span></td>
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
