@extends('layouts.master')

@section('content')
    @php
        $statusClass = match ($nephrologyRegistration->status) {
            'pending' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
        $diagnoseCount = $appointment?->diagnose?->count() ?? 0;
        $labTestCount = $appointment?->patientTestRegistrations?->count() ?? 0;
        $prescriptionCount = $appointment?->prescription?->count() ?? 0;
        $hemodialysisCount = $nephrologyRegistration->hemodialysisSessions->count();
    @endphp

    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
            @include('components.toast')
        @endif
        <div class="container-xxl flex-grow-1 container-p-y">
            {{-- Page header --}}
            <div class="row mb-4">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">{{ localize('global.dashboard') }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('nephrology-registrations.index') }}" class="text-decoration-none">{{ localize('global.nephrology_registrations') }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.nephrology_visit') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar avatar-lg">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    <i class="bx bx-droplet fs-3"></i>
                                </span>
                            </div>
                            <div>
                                <h2 class="h4 mb-0">{{ localize('global.nephrology_visit') }}</h2>
                                <p class="text-muted mb-0 small">
                                    {{ localize('global.ref_no') }}: <span class="fw-semibold">{{ $nephrologyRegistration->ref_no }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            @if($appointment)
                                <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-calendar me-1"></i> {{ localize('global.back_to_appointment') }}
                                </a>
                            @endif
                            <a href="{{ route('nephrology-registrations.index') }}" class="btn btn-secondary">
                                <i class="bx bx-list-ul me-1"></i> {{ localize('global.nephrology_registrations') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Patient summary cards --}}
            <div class="row mb-4 g-3">
                <div class="col-sm-6 col-xl">
                    <div class="card h-100 border shadow-none">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">{{ localize('global.patient_name') }}</div>
                            <div class="fw-bold text-truncate" title="{{ $nephrologyRegistration->patient->name }} {{ $nephrologyRegistration->patient->last_name }}">
                                {{ $nephrologyRegistration->patient->name }} {{ $nephrologyRegistration->patient->last_name }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card h-100 border shadow-none">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">{{ localize('global.doctor') }}</div>
                            <div class="fw-bold text-truncate">{{ $nephrologyRegistration->doctor->name ?? localize('global.not_available') }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card h-100 border shadow-none">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">{{ localize('global.visit_date') }}</div>
                            <div class="fw-bold" dir="ltr">{{ $nephrologyRegistration->visit_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($nephrologyRegistration->visit_date) : '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card h-100 border shadow-none">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">{{ localize('global.diseases') }}</div>
                            <div class="fw-bold text-truncate">{{ $nephrologyRegistration->disease->name ?? '—' }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl">
                    <div class="card h-100 border shadow-none">
                        <div class="card-body text-center py-3">
                            <div class="text-muted small mb-1">{{ localize('global.status') }}</div>
                            <span class="badge bg-{{ $statusClass }}">{{ localize('global.' . $nephrologyRegistration->status) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Main tabs --}}
            <ul class="nav nav-pills nav-fill flex-nowrap overflow-auto mb-4 nephrology-visit-tabs" id="nephrologyVisitTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="clinical-tab" data-bs-toggle="tab" data-bs-target="#clinical-pane" type="button" role="tab">
                        <i class="bx bx-clipboard me-1"></i> {{ localize('global.nephrology_clinical_record') }}
                    </button>
                </li>
                @if($appointment)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="diagnose-tab" data-bs-toggle="tab" data-bs-target="#diagnose-pane" type="button" role="tab">
                            <i class="bx bx-pulse me-1"></i> {{ localize('global.diagnose') }}
                            @if($diagnoseCount > 0)
                                <span class="badge bg-primary ms-1">{{ $diagnoseCount }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="lab-tests-tab" data-bs-toggle="tab" data-bs-target="#lab-tests-pane" type="button" role="tab">
                            <i class="bx bx-test-tube me-1"></i> {{ localize('global.lab_test_registrations') }}
                            @if($labTestCount > 0)
                                <span class="badge bg-info ms-1">{{ $labTestCount }}</span>
                            @endif
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="prescription-tab" data-bs-toggle="tab" data-bs-target="#prescription-pane" type="button" role="tab">
                            <i class="bx bx-notepad me-1"></i> {{ localize('global.prescription') }}
                            @if($prescriptionCount > 0)
                                <span class="badge bg-success ms-1">{{ $prescriptionCount }}</span>
                            @endif
                        </button>
                    </li>
                @endif
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="hemodialysis-tab" data-bs-toggle="tab" data-bs-target="#hemodialysis-pane" type="button" role="tab">
                        <i class="bx bx-water me-1"></i> {{ localize('global.hemodialysis_sessions') }}
                        @if($hemodialysisCount > 0)
                            <span class="badge bg-secondary ms-1">{{ $hemodialysisCount }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="nephrologyVisitTabsContent">
                {{-- 1. Clinical record --}}
                <div class="tab-pane fade show active" id="clinical-pane" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary d-flex align-items-center gap-2">
                            <i class="bx bx-edit-alt text-primary"></i>
                            <h5 class="mb-0">{{ localize('global.nephrology_clinical_record') }}</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('nephrology-registrations.update', $nephrologyRegistration) }}" method="POST" id="nephrology-clinical-form">
                                @csrf
                                @method('PUT')
                                @include('pages.nephrology.registrations._form', [
                                    'nephrologyRegistration' => $nephrologyRegistration,
                                    'doctors' => $doctors,
                                    'nephrologyDiseases' => $nephrologyDiseases,
                                ])
                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bx bx-save me-1"></i> {{ localize('global.save') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if($appointment)
                    {{-- 2. Diagnoses --}}
                    <div class="tab-pane fade" id="diagnose-pane" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header bg-body-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-pulse text-warning"></i>
                                <h5 class="mb-0">{{ localize('global.diagnose') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="diagnosis-section-container"
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddDiagnosis" => auth()->user()->can("add-diagnose"),
                                         "canEditDiagnosis" => auth()->user()->can("edit-diagnoses"),
                                         "canDeleteDiagnosis" => auth()->user()->can("delete-diagnoses")
                                     ])'>
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted mb-0">{{ localize('global.loading_diagnosis_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Lab tests / معاینات --}}
                    <div class="tab-pane fade" id="lab-tests-pane" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header bg-body-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-test-tube text-info"></i>
                                <h5 class="mb-0">{{ localize('global.lab_test_registrations') }}</h5>
                            </div>
                            <div class="card-body p-0">
                                <x-lab-test-registration-section
                                    :entity="$appointment"
                                    entity-type="appointment"
                                    :entity-id="$appointment->id"
                                    :can-add-test-registration="auth()->user()->can('register-patient-tests')"
                                    :appointment-completed="$appointment->is_completed == 1"
                                    accordion-id="nephrologyLabTestRegistrationsAccordion"
                                    collapse-id="nephrologyLabTestRegistrationsCollapse"
                                    header-id="nephrologyLabTestRegistrationsHeader"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- 4. Prescription --}}
                    <div class="tab-pane fade" id="prescription-pane" role="tabpanel">
                        <div class="card shadow-sm">
                            <div class="card-header bg-body-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-notepad text-success"></i>
                                <h5 class="mb-0">{{ localize('global.prescription') }}</h5>
                            </div>
                            <div class="card-body">
                                <div id="prescription-section-container"
                                     data-appointment='@json($appointment)'
                                     data-permissions='@json([
                                         "canAddPrescription" => auth()->user()->can("add-prescription"),
                                         "canEditPrescription" => auth()->user()->can("edit-prescriptions"),
                                         "canDeletePrescription" => auth()->user()->can("delete-prescriptions")
                                     ])'>
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="mt-2 text-muted mb-0">{{ localize('global.loading_prescription_section') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- 5. Hemodialysis sessions --}}
                <div class="tab-pane fade" id="hemodialysis-pane" role="tabpanel">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bx bx-water text-primary"></i>
                                <h5 class="mb-0">{{ localize('global.hemodialysis_sessions') }}</h5>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('hemodialysis-sessions.create', ['nephrology_registration_id' => $nephrologyRegistration->id, 'patient_id' => $nephrologyRegistration->patient_id]) }}" class="btn btn-sm btn-primary">
                                    <i class="bx bx-plus"></i> {{ localize('global.add_hemodialysis_session') }}
                                </a>
                                <a href="{{ route('hemodialysis-sessions.index', ['patient_id' => $nephrologyRegistration->patient_id]) }}" class="btn btn-sm btn-outline-secondary">
                                    {{ localize('global.view_all_hemodialysis_sessions') }}
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ localize('global.ref_no') }}</th>
                                        <th>{{ localize('global.session_date') }}</th>
                                        <th>{{ localize('global.duration_minutes') }}</th>
                                        <th>{{ localize('global.attending_nephrologist') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th class="text-end">{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($hemodialysisSessions as $hdSession)
                                        <tr>
                                            <td><span class="badge bg-label-primary">{{ $hdSession->ref_no }}</span></td>
                                            <td dir="ltr">{{ $hdSession->session_date ? \HanifHefaz\Dcter\Dcter::GregorianToJalali($hdSession->session_date) : '—' }}</td>
                                            <td>{{ $hdSession->duration_minutes ?? '—' }}</td>
                                            <td>{{ $hdSession->doctor->name ?? '—' }}</td>
                                            <td><span class="badge bg-info">{{ localize('global.' . $hdSession->status) }}</span></td>
                                            <td class="text-end">
                                                <a href="{{ route('hemodialysis-sessions.show', $hdSession) }}" class="btn btn-sm btn-icon btn-outline-primary" title="{{ localize('global.show') }}">
                                                    <i class="bx bx-show"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bx bx-water fs-1 d-block mb-2 opacity-50"></i>
                                                {{ localize('global.no_hemodialysis_sessions_found') }}
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if($appointment ?? null)
        @vite(['public/assets/js/vue/appointment-prescription-app.js', 'public/assets/js/vue/diagnosis-app.js'])
    @endif
@endsection

@push('custom-css')
<style>
    .nephrology-visit-tabs .nav-link {
        white-space: nowrap;
        border-radius: 0.375rem;
        font-weight: 500;
    }
    .nephrology-visit-tabs .nav-link:not(.active) {
        color: var(--bs-body-color);
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
    }
    .nephrology-visit-tabs .nav-link.active {
        box-shadow: 0 2px 6px rgba(var(--bs-primary-rgb), 0.25);
    }
    #lab-tests-pane .accordion-item {
        border: none;
    }
    #lab-tests-pane .accordion-button {
        display: none;
    }
    #lab-tests-pane .accordion-collapse {
        display: block !important;
    }
    #lab-tests-pane .accordion-body {
        padding: 1.25rem;
    }
</style>
@endpush

