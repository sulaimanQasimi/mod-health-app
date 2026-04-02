@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="mb-0">{{ localize('global.prosthetics_referrals') }}</h4>
                <a href="{{ route('prosthetics.referrals.create') }}" class="btn btn-primary btn-sm">{{ localize('global.prosthetics_new_referral') }}</a>
            </div>

            <form method="get" class="row g-2 mb-3">
                <div class="col-auto">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="{{ localize('global.search') ?? 'Search' }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-secondary">{{ localize('global.search') ?? 'Search' }}</button>
                </div>
            </form>

            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.prosthetics_referral_number') }}</th>
                                <th>{{ localize('global.patient_name') ?? 'Patient' }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.date') ?? 'Date' }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($referrals as $r)
                                <tr>
                                    <td><code>{{ $r->referral_number }}</code></td>
                                    <td>{{ $r->patient->name ?? '—' }}</td>
                                    <td><span class="badge bg-label-secondary">{{ $r->status }}</span></td>
                                    <td>{{ $r->referral_date?->format('Y-m-d') }}</td>
                                    <td>
                                        <a href="{{ route('prosthetics.referrals.show', $r) }}" class="btn btn-sm btn-outline-primary">{{ localize('global.show') ?? 'View' }}</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">{{ $referrals->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>
    </div>
@endsection
