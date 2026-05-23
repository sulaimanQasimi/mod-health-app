@extends('layouts.master')

@section('content')
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('nephrology-registrations.index') }}">{{ localize('global.nephrology_registrations') }}</a></li>
                            <li class="breadcrumb-item active">{{ localize('global.nephrology_visit') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2 class="h4 mb-0">{{ localize('global.nephrology_visit') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('appointments.show', $nephrologyRegistration->appointment) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back_to_appointment') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary">
                            <h5 class="mb-0">{{ localize('global.registration_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted">{{ localize('global.ref_no') }}</small>
                                    <div class="fw-bold">{{ $nephrologyRegistration->ref_no }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted">{{ localize('global.patient_name') }}</small>
                                    <div class="fw-bold">
                                        {{ $nephrologyRegistration->patient->name }} {{ $nephrologyRegistration->patient->last_name }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted">{{ localize('global.doctor') }}</small>
                                    <div class="fw-bold">{{ $nephrologyRegistration->doctor->name ?? localize('global.not_available') }}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <small class="text-muted">{{ localize('global.status') }}</small>
                                    <div><span class="badge bg-info">{{ localize('global.' . $nephrologyRegistration->status) }}</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.nephrology_clinical_record') }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('nephrology-registrations.update', $nephrologyRegistration) }}" method="POST" id="nephrology-clinical-form">
                        @csrf
                        @method('PUT')
                        @include('pages.nephrology.registrations._form', ['nephrologyRegistration' => $nephrologyRegistration, 'doctors' => $doctors])
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save"></i> {{ localize('global.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
