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
                            <li class="breadcrumb-item"><a href="{{ route('hemodialysis-sessions.index') }}">{{ localize('global.hemodialysis') }}</a></li>
                            <li class="breadcrumb-item active">{{ localize('global.ref_no') }} {{ $hemodialysisSession->ref_no }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h2 class="h4 mb-0">{{ localize('global.hemodialysis_session') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('hemodialysis-sessions.edit', $hemodialysisSession) }}" class="btn btn-warning">
                                <i class="bx bx-edit"></i> {{ localize('global.edit') }}
                            </a>
                            <form action="{{ route('hemodialysis-sessions.destroy', $hemodialysisSession) }}" method="POST"
                                onsubmit="return confirm('{{ localize('global.confirm_delete') }}');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="bx bx-trash"></i> {{ localize('global.delete') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary">
                            <h5 class="mb-0">{{ localize('global.patient_information') }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-body-tertiary" style="width: 20%;">{{ localize('global.ref_no') }}</th>
                                            <td>{{ $hemodialysisSession->ref_no }}</td>
                                            <th class="bg-body-tertiary" style="width: 20%;">{{ localize('global.status') }}</th>
                                            <td><span class="badge bg-info">{{ localize('global.' . $hemodialysisSession->status) }}</span></td>
                                        </tr>
                                        <tr>
                                            <th class="bg-body-tertiary">{{ localize('global.patient_name') }}</th>
                                            <td>
                                                <a href="{{ route('patients.show', $hemodialysisSession->patient) }}">
                                                    {{ $hemodialysisSession->patient->name }} {{ $hemodialysisSession->patient->last_name }}
                                                </a>
                                            </td>
                                            <th class="bg-body-tertiary">{{ localize('global.patient_id') }}</th>
                                            <td>{{ $hemodialysisSession->patient->patient_id ?? $hemodialysisSession->patient_id }}</td>
                                        </tr>
                                        @if($hemodialysisSession->nephrologyRegistration)
                                        <tr>
                                            <th class="bg-body-tertiary">{{ localize('global.nephrology_registration') }}</th>
                                            <td colspan="3">
                                                <a href="{{ route('nephrology-registrations.show', $hemodialysisSession->nephrologyRegistration) }}">
                                                    {{ localize('global.ref_no') }} {{ $hemodialysisSession->nephrologyRegistration->ref_no }}
                                                </a>
                                            </td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.session_details') }}</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-body-tertiary" style="width: 20%;">{{ localize('global.diagnosis') }}</th>
                                    <td colspan="3">{{ $hemodialysisSession->diagnosis ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary">{{ localize('global.dialysis_schedule') }}</th>
                                    <td>{{ $hemodialysisSession->dialysis_schedule ?: '—' }}</td>
                                    <th class="bg-body-tertiary">{{ localize('global.attending_nephrologist') }}</th>
                                    <td>{{ $hemodialysisSession->doctor->name ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary">{{ localize('global.session_date') }}</th>
                                    <td>{{ $hemodialysisSession->session_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($hemodialysisSession->session_date) : '—' }}</td>
                                    <th class="bg-body-tertiary">{{ localize('global.session_time') }}</th>
                                    <td>{{ $hemodialysisSession->session_time ? \Carbon\Carbon::parse($hemodialysisSession->session_time)->format('H:i') : '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary">{{ localize('global.duration_minutes') }}</th>
                                    <td>{{ $hemodialysisSession->duration_minutes ?? '—' }}</td>
                                    <th class="bg-body-tertiary">{{ localize('global.vascular_access_type') }}</th>
                                    <td>
                                        @if($hemodialysisSession->vascular_access_type)
                                            {{ localize('global.' . $hemodialysisSession->vascular_access_type) }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary">{{ localize('global.dialyzer_type') }}</th>
                                    <td>{{ $hemodialysisSession->dialyzer_type ?: '—' }}</td>
                                    <th class="bg-body-tertiary">{{ localize('global.blood_type') }}</th>
                                    <td>{{ $hemodialysisSession->blood_type ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-body-tertiary">{{ localize('global.fluid_removed_ml') }}</th>
                                    <td colspan="3">{{ $hemodialysisSession->fluid_removed_ml ?? '—' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ localize('global.pre_dialysis_vitals') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.blood_pressure') }}</th>
                                        <td>{{ $hemodialysisSession->pre_blood_pressure ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.weight_kg') }}</th>
                                        <td>{{ $hemodialysisSession->pre_weight ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.pulse') }}</th>
                                        <td>{{ $hemodialysisSession->pre_pulse ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.temperature') }}</th>
                                        <td>{{ $hemodialysisSession->pre_temperature ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="mb-0">{{ localize('global.post_dialysis_vitals') }}</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered mb-0">
                                <tbody>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.blood_pressure') }}</th>
                                        <td>{{ $hemodialysisSession->post_blood_pressure ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.weight_kg') }}</th>
                                        <td>{{ $hemodialysisSession->post_weight ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.pulse') }}</th>
                                        <td>{{ $hemodialysisSession->post_pulse ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-body-tertiary">{{ localize('global.temperature') }}</th>
                                        <td>{{ $hemodialysisSession->post_temperature ?? '—' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            @if($hemodialysisSession->complications_notes)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">{{ localize('global.complications_notes') }}</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $hemodialysisSession->complications_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
