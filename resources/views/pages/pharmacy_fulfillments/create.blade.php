@extends('layouts.master')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="content-wrapper">
            @if (Session::has('success') || Session::has('error'))
                @include('components.toast')
            @endif
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.create_pharmacy_fulfillment') }}</h5>
                    </div>
                    <div class="card-body">
                        @if($userPharmacy)
                        <div class="alert alert-info mb-3">
                            <i class="bx bx-info-circle me-2"></i>
                            <strong>{{ localize('global.pharmacy') }}:</strong> {{ $userPharmacy->name }}
                        </div>
                        @endif
                        <form method="POST" action="{{ route('pharmacy_fulfillments.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medicine_id">{{ localize('global.medicine') }} <span class="text-danger">*</span></label>
                                        <select class="form-control select2 @error('medicine_id') is-invalid @enderror"
                                            id="medicine_id" name="medicine_id" required>
                                            <option value="">{{ localize('global.select_medicine') }}</option>
                                            @foreach ($medicines as $medicine)
                                                <option value="{{ $medicine->id }}"
                                                    {{ old('medicine_id') == $medicine->id ? 'selected' : '' }}>
                                                    {{ $medicine->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('medicine_id')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="unit_type">{{ localize('global.unit_type') }}</label>
                                        <input type="text" class="form-control @error('unit_type') is-invalid @enderror"
                                            id="unit_type" name="unit_type" value="{{ old('unit_type') }}" placeholder="{{ localize('global.unit_type') }}">
                                        @error('unit_type')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="amount">{{ localize('global.amount') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('amount') is-invalid @enderror"
                                            id="amount" name="amount" value="{{ old('amount') }}" required>
                                        @error('amount')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="form_no">{{ localize('global.form_no') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('form_no') is-invalid @enderror"
                                            id="form_no" name="form_no" value="{{ old('form_no') }}" required>
                                        @error('form_no')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="date">{{ localize('global.date') }} <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control datepicker_dari @error('date') is-invalid @enderror"
                                            id="date" name="date" value="{{ old('date') }}" required placeholder="{{ localize('global.date') }}">
                                        @error('date')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="form">{{ localize('global.form') }} (PDF)</label>
                                        <input type="file" class="form-control @error('form') is-invalid @enderror"
                                            id="form" name="form" accept=".pdf,application/pdf">
                                        <small class="form-text text-muted">{{ localize('global.max_file_size_10mb') }}</small>
                                        @error('form')
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            {{ localize('global.create') }}
                                        </button>
                                        <a href="{{ route('pharmacy_fulfillments.index') }}" class="btn btn-secondary">
                                            {{ localize('global.cancel') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-js')
    <script src="{{ asset('ShamsiCalender/js/persianDatepicker.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Persian date picker
            $('.datepicker_dari').persianDatepicker({
                formatDate: 'YYYY-MM-DD',
                calendar: {
                    persian: {
                        locale: 'en',
                        showHint: true,
                        leapYearMode: 'algorithmic'
                    }
                },
                checkDate: function(unix) {
                    return true;
                }
            });
        });
    </script>
@endpush
