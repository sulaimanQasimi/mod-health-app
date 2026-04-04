@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_referrals') }}</h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('prosthetics.referrals.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i>{{ localize('global.prosthetics_new_referral') }}
                    </a>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('prosthetics.referrals.index') }}" class="row g-2 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label small">{{ localize('global.search') }}</label>
                            <input type="text"
                                   name="q"
                                   value="{{ request('q') }}"
                                   class="form-control form-control-sm"
                                   placeholder="Referral / patient / phone / NID / ID card">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.prosthetics_referral_number') }}</label>
                            <input type="text"
                                   name="referral_number"
                                   value="{{ request('referral_number') }}"
                                   class="form-control form-control-sm"
                                   placeholder="REF-...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.patient_name') ?? 'Patient name' }}</label>
                            <input type="text"
                                   name="patient_name"
                                   value="{{ request('patient_name') }}"
                                   class="form-control form-control-sm"
                                   placeholder="{{ localize('global.search') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label small">{{ localize('global.id') ?? 'ID' }}</label>
                            <input type="number"
                                   name="patient_id"
                                   value="{{ request('patient_id') }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.phone') }}</label>
                            <input type="text"
                                   name="phone"
                                   value="{{ request('phone') }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.nid') ?? 'NID' }}</label>
                            <input type="text"
                                   name="nid"
                                   value="{{ request('nid') }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.id_card') }}</label>
                            <input type="text"
                                   name="id_card"
                                   value="{{ request('id_card') }}"
                                   class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.status') }}</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">{{ localize('global.all') }}</option>
                                @foreach (['drafted','submitted','received','under_review','accepted','rejected','cancelled','converted_to_case'] as $st)
                                    <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.urgency') }}</label>
                            <input type="text"
                                   name="urgency"
                                   value="{{ request('urgency') }}"
                                   class="form-control form-control-sm"
                                   placeholder="routine / urgent / ...">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small">{{ localize('global.prosthetics_requested_service_type') }}</label>
                            <input type="text"
                                   name="requested_service_type"
                                   value="{{ request('requested_service_type') }}"
                                   class="form-control form-control-sm"
                                   placeholder="e.g. prosthetic / orthotic / assistive">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.from') }}</label>
                            <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">{{ localize('global.to') }}</label>
                            <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2 d-flex gap-2 align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                <i class="bx bx-search me-1"></i>{{ localize('global.filter') ?? 'Filter' }}
                            </button>
                            <a href="{{ route('prosthetics.referrals.index') }}" class="btn btn-sm btn-outline-secondary w-50">
                                <i class="bx bx-reset me-1"></i>{{ localize('global.reset') }}
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ localize('global.prosthetics_referrals') }}</h5>
                    <div class="text-muted small">
                        {{ $referrals->firstItem() ?? 0 }} - {{ $referrals->lastItem() ?? 0 }} / {{ $referrals->total() }}
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>{{ localize('global.prosthetics_referral_number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.nid') }}</th>
                                    <th>{{ localize('global.urgency') }}</th>
                                    <th>{{ localize('global.prosthetics_service_type') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.date') }}</th>
                                    <th class="text-end">{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($referrals as $r)
                                    <tr>
                                        <td><code>{{ $r->referral_number }}</code></td>
                                        <td>{{ $r->patient->name ?? '—' }}</td>
                                        <td>{{ $r->patient->nid ?? '—' }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $r->urgency ?? '—' }}</span></td>
                                        <td>{{ $r->requested_service_type ?? '—' }}</td>
                                        <td><span class="badge bg-label-secondary">{{ $r->status }}</span></td>
                                        <td dir="ltr">{{ $r->referral_date?->format('Y-m-d') }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('prosthetics.referrals.show', $r) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-expand me-1"></i>{{ localize('global.show') ?? 'View' }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $referrals->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
@endsection
