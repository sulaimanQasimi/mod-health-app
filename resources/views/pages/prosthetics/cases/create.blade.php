@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.prosthetics_new_case') }}</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="{{ route('prosthetics.cases.store') }}">
                        @csrf
                        <input type="hidden" name="referral_id" value="{{ old('referral_id', $referralId) }}">
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.prosthetics_patient_id') }} *</label>
                            <input type="number" name="patient_id" class="form-control @error('patient_id') is-invalid @enderror"
                                   value="{{ old('patient_id', optional($prefill)->patient_id) }}" required min="1">
                            @error('patient_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Side *</label>
                                <select name="side" class="form-select" required>
                                    @foreach (['left', 'right', 'bilateral'] as $s)
                                        <option value="{{ $s }}" @selected(old('side', 'left') === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Category *</label>
                                <select name="case_category" class="form-select" required>
                                    @foreach (['prosthetic', 'orthotic', 'assistive'] as $cat)
                                        <option value="{{ $cat }}" @selected(old('case_category', 'prosthetic') === $cat)>{{ $cat }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">{{ localize('global.priority') ?? 'Priority' }}</label>
                                <select name="priority" class="form-select">
                                    @foreach (['low', 'normal', 'high', 'urgent'] as $p)
                                        <option value="{{ $p }}" @selected(old('priority', 'normal') === $p)>{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.prosthetics_body_region') }}</label>
                            <input type="text" name="body_region" class="form-control" value="{{ old('body_region') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.prosthetics_device_type') }}</label>
                            <input type="text" name="device_type" class="form-control" value="{{ old('device_type') }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.diagnose') ?? 'Primary diagnosis' }}</label>
                            <textarea name="primary_diagnosis" class="form-control" rows="2">{{ old('primary_diagnosis', optional($prefill)->diagnosis_summary) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">{{ localize('global.notes') ?? 'Cause / notes' }}</label>
                            <textarea name="cause_of_loss_notes" class="form-control" rows="2">{{ old('cause_of_loss_notes') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') ?? 'Save' }}</button>
                        <a href="{{ route('prosthetics.cases.index') }}" class="btn btn-outline-secondary">{{ localize('global.back') }}</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
