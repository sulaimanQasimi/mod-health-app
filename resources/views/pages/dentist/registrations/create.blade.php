@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dentist-registrations.index') }}" class="text-decoration-none">{{ localize('global.dentist_registrations') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.create_registration') }}</li>
                        </ol>
                    </nav>
                    <h2 class="h4 mb-0">{{ localize('global.create_dentist_registration') }}</h2>
                </div>
            </div>

            <!-- Patient Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.appointment_information') }}</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>{{ localize('global.patient_name') }}:</strong>
                            {{ $appointment->patient->name }} {{ $appointment->patient->last_name }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.appointment_date') }}:</strong>
                            {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($appointment->date) }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.time') }}:</strong>
                            {{ $appointment->time }}
                        </div>
                        <div class="col-md-3">
                            <strong>{{ localize('global.department') }}:</strong>
                            {{ $appointment->department->name ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Form -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.registration_form') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('dentist-registrations.store', $appointment) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="dentist_id" class="form-label">{{ localize('global.dentist') }} <span class="text-danger">*</span></label>
                                <select class="form-select @error('dentist_id') is-invalid @enderror" id="dentist_id" name="dentist_id">
                                    <option value="">{{ localize('global.select_dentist') }}</option>
                                    @foreach($dentists as $dentist)
                                        <option value="{{ $dentist->id }}" {{ old('dentist_id') == $dentist->id ? 'selected' : '' }}>
                                            {{ $dentist->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('dentist_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="registration_date" class="form-label">{{ localize('global.registration_date') }} <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('registration_date') is-invalid @enderror" 
                                    id="registration_date" name="registration_date" value="{{ old('registration_date', date('Y-m-d')) }}" required>
                                @error('registration_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                    id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-secondary">
                                {{ localize('global.cancel') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.create_registration') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
