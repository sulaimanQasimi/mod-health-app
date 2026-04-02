@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif

            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <h4 class="mb-1"><code>{{ $referral->referral_number }}</code></h4>
                    <p class="mb-0 text-muted">{{ $referral->patient->name ?? '—' }} — {{ localize('global.status') }}: {{ $referral->status }}</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('prosthetics.referrals.edit', $referral) }}" class="btn btn-sm btn-outline-secondary">{{ localize('global.edit') ?? 'Edit' }}</a>
                    @if (!$referral->converted_case_id)
                        <form method="post" action="{{ route('prosthetics.referrals.convert', $referral) }}" class="d-inline" onsubmit="return confirm('Create case from this referral?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">{{ localize('global.prosthetics_new_case') }}</button>
                        </form>
                    @else
                        <a href="{{ route('prosthetics.cases.show', $referral->convertedCase) }}" class="btn btn-sm btn-primary">{{ localize('global.prosthetics_case_detail') }}</a>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <p><strong>{{ localize('global.reason') ?? 'Reason' }}:</strong> {{ $referral->reason ?: '—' }}</p>
                    <p><strong>{{ localize('global.diagnose') ?? 'Diagnosis' }}:</strong> {{ $referral->diagnosis_summary ?: '—' }}</p>
                    <p><strong>{{ localize('global.notes') ?? 'Notes' }}:</strong> {{ $referral->notes ?: '—' }}</p>
                </div>
            </div>

            @if ($referral->status !== 'rejected')
                <div class="d-flex gap-2">
                    <form method="post" action="{{ route('prosthetics.referrals.accept', $referral) }}">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success">{{ localize('global.yes') ?? 'Accept' }}</button>
                    </form>
                    <form method="post" action="{{ route('prosthetics.referrals.reject', $referral) }}" class="d-flex gap-2 align-items-start">
                        @csrf
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="{{ localize('global.reject_reason') ?? 'Reason' }}" style="min-width:220px">
                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ localize('global.reject_request') ?? 'Reject' }}</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
