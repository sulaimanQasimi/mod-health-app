@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.edit') }} — <code>{{ $referral->referral_number }}</code></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.referrals.update', $referral) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.date') ?? 'Referral date' }}</label>
                            <input type="date" name="referral_date" class="form-control" value="{{ old('referral_date', $referral->referral_date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.status') }}</label>
                            <select name="status" class="form-select">
                                @foreach (['drafted', 'submitted', 'received', 'under_review', 'accepted', 'rejected', 'cancelled', 'converted_to_case'] as $st)
                                    <option value="{{ $st }}" @selected(old('status', $referral->status) === $st)>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.reason') ?? 'Reason' }}</label>
                            <textarea name="reason" class="form-control" rows="2">{{ old('reason', $referral->reason) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.diagnose') ?? 'Diagnosis' }}</label>
                            <textarea name="diagnosis_summary" class="form-control" rows="2">{{ old('diagnosis_summary', $referral->diagnosis_summary) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.notes') }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $referral->notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') ?? 'Save' }}</button>
                        <a href="{{ route('prosthetics.referrals.show', $referral) }}" class="btn btn-outline-secondary">{{ localize('global.back') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
