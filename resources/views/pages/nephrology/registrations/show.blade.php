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

            @php
                $hemodialysisSessions = $nephrologyRegistration->hemodialysisSessions()
                    ->with('doctor')
                    ->latest('session_date')
                    ->limit(10)
                    ->get();
            @endphp
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h5 class="mb-0">{{ localize('global.hemodialysis_sessions') }}</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('hemodialysis-sessions.create', ['nephrology_registration_id' => $nephrologyRegistration->id, 'patient_id' => $nephrologyRegistration->patient_id]) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-plus"></i> {{ localize('global.add_hemodialysis_session') }}
                        </a>
                        <a href="{{ route('hemodialysis-sessions.index', ['patient_id' => $nephrologyRegistration->patient_id]) }}" class="btn btn-sm btn-secondary">
                            {{ localize('global.view_all_hemodialysis_sessions') }}
                        </a>
                    </div>
                </div>
                <div class="card-datatable table-responsive">
                    <table class="table border-top mb-0">
                        <thead>
                            <tr>
                                <th>{{ localize('global.ref_no') }}</th>
                                <th>{{ localize('global.session_date') }}</th>
                                <th>{{ localize('global.duration_minutes') }}</th>
                                <th>{{ localize('global.attending_nephrologist') }}</th>
                                <th>{{ localize('global.status') }}</th>
                                <th>{{ localize('global.actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($hemodialysisSessions as $hdSession)
                                <tr>
                                    <td>{{ $hdSession->ref_no }}</td>
                                    <td>{{ $hdSession->session_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($hdSession->session_date) : '—' }}</td>
                                    <td>{{ $hdSession->duration_minutes ?? '—' }}</td>
                                    <td>{{ $hdSession->doctor->name ?? '—' }}</td>
                                    <td><span class="badge bg-info">{{ localize('global.' . $hdSession->status) }}</span></td>
                                    <td>
                                        <a href="{{ route('hemodialysis-sessions.show', $hdSession) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-show"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">{{ localize('global.no_hemodialysis_sessions_found') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
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
