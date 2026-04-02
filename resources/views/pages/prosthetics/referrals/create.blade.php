@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.prosthetics_new_referral') }}</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.referrals.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.prosthetics_patient_id') }} *</label>
                            <input type="number" name="patient_id" class="form-control @error('patient_id') is-invalid @enderror" value="{{ old('patient_id') }}" required min="1">
                            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">{{ localize('global.patients_list') ?? 'Use patient record ID from registration.' }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.date') ?? 'Referral date' }} *</label>
                            <input type="date" name="referral_date" class="form-control" value="{{ old('referral_date', now()->format('Y-m-d')) }}" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ localize('global.requested_department') ?? 'Referring facility' }}</label>
                                <input type="text" name="referring_facility" class="form-control" value="{{ old('referring_facility') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ localize('global.doctor') ?? 'Referring doctor' }}</label>
                                <input type="text" name="referring_doctor" class="form-control" value="{{ old('referring_doctor') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.reason') ?? 'Reason' }}</label>
                            <textarea name="reason" class="form-control" rows="2">{{ old('reason') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.diagnose') ?? 'Diagnosis summary' }}</label>
                            <textarea name="diagnosis_summary" class="form-control" rows="2">{{ old('diagnosis_summary') }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.notes') ?? 'Notes' }}</label>
                            <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') ?? 'Save' }}</button>
                        <a href="{{ route('prosthetics.referrals.index') }}" class="btn btn-outline-secondary">{{ localize('global.back') ?? 'Back' }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
