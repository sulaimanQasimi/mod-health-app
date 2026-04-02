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
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-warning">
                        <div class="card-body">
                            <span class="text-muted small">{{ localize('global.prosthetics_referrals') }}</span>
                            <h3 class="mb-0 mt-1">{{ $referralPending }}</h3>
                            <small class="text-muted">{{ localize('global.pending') ?? 'Pending intake' }}</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-info">
                        <div class="card-body">
                            <span class="text-muted small">{{ localize('global.prosthetics_workflow') }}</span>
                            <h3 class="mb-0 mt-1">{{ $waitingApproval }}</h3>
                            <small class="text-muted">{{ localize('global.approved_blood_requests') }} / approval queue</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-primary">
                        <div class="card-body">
                            <span class="text-muted small">{{ localize('global.prosthetics_work_order') }}</span>
                            <h3 class="mb-0 mt-1">{{ $inProduction }}</h3>
                            <small class="text-muted">Production / trial</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card h-100 bg-label-secondary">
                        <div class="card-body">
                            <span class="text-muted small">WO active</span>
                            <h3 class="mb-0 mt-1">{{ $workOrdersActive }}</h3>
                            <small class="text-muted">Workshop orders</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.prosthetics_cases') }} — {{ localize('global.reports') ?? 'Recent' }}</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.prosthetics_case_number') }}</th>
                                    <th>{{ localize('global.patient_name') ?? 'Patient' }}</th>
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
                                        <td>
                                            <a href="{{ route('prosthetics.cases.show', $c) }}" class="btn btn-sm btn-outline-primary">
                                                {{ localize('global.show') ?? 'View' }}
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
@endsection
