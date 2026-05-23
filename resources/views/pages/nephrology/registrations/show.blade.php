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
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-body-tertiary" style="width: 20%;">{{ localize('global.ref_no') }}</th>
                                            <td>{{ $nephrologyRegistration->ref_no }}</td>
                                            <th class="bg-body-tertiary" style="width: 20%;">{{ localize('global.patient_name') }}</th>
                                            <td>{{ $nephrologyRegistration->patient->name }} {{ $nephrologyRegistration->patient->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">{{ localize('global.doctor') }}</th>
                                            <td>{{ $nephrologyRegistration->doctor->name ?? localize('global.not_available') }}</td>
                                            <th class="bg-body-tertiary">{{ localize('global.status') }}</th>
                                            <td><span class="badge bg-info">{{ localize('global.' . $nephrologyRegistration->status) }}</span></td>
                                        </tr>
                                    </tbody>
                                </table>
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
