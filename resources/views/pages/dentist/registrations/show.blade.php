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
                            <li class="breadcrumb-item active" aria-current="page">{{ localize('global.registration_details') }}</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between align-items-center">
                        <h2 class="h4 mb-0">{{ localize('global.dentist_registration_details') }}</h2>
                        <div class="d-flex gap-2">
                            <a href="{{ route('appointments.show', $dentistRegistration->appointment) }}" class="btn btn-secondary">
                                <i class="bx bx-arrow-back"></i> {{ localize('global.back_to_appointment') }}
                            </a>
                            @can('edit-dentist-registrations')
                            <a href="{{ route('dentist-registrations.edit', $dentistRegistration) }}" class="btn btn-warning">
                                <i class="bx bx-edit"></i> {{ localize('global.edit') }}
                            </a>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Registration Info Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-body-secondary text-body">
                            <h5 class="mb-0 text-center">
                                <i class="bx bx-calendar-check me-2 text-primary"></i>
                                {{ localize('global.registration_information') }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.ref_no') }}</div>
                                        <div class="fw-bold">{{ $dentistRegistration->ref_no }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.patient_name') }}</div>
                                        <div class="fw-bold">
                                            {{ $dentistRegistration->appointment->patient->name }} {{ $dentistRegistration->appointment->patient->last_name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.dentist') }}</div>
                                        <div class="fw-bold">{{ $dentistRegistration->dentist->name ?? localize('global.not_available') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.status') }}</div>
                                        <div class="fw-bold">
                                            @if($dentistRegistration->status == 'pending')
                                                <span class="badge bg-warning">{{ localize('global.pending') }}</span>
                                            @elseif($dentistRegistration->status == 'in_progress')
                                                <span class="badge bg-info">{{ localize('global.in_progress') }}</span>
                                            @elseif($dentistRegistration->status == 'completed')
                                                <span class="badge bg-success">{{ localize('global.completed') }}</span>
                                            @else
                                                <span class="badge bg-danger">{{ localize('global.cancelled') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.registration_date') }}</div>
                                        <div class="fw-bold">
                                            {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($dentistRegistration->registration_date) }}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="text-center p-3 border rounded bg-body-secondary">
                                        <div class="text-body small mb-1">{{ localize('global.appointment_date') }}</div>
                                        <div class="fw-bold">
                                            {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($dentistRegistration->appointment->date) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @if($dentistRegistration->notes)
                            <div class="row mt-3">
                                <div class="col-12">
                                    <strong>{{ localize('global.notes') }}:</strong>
                                    <p>{{ $dentistRegistration->notes }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs Navigation -->
            <div class="row mb-4">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="dentistTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="examinations-tab" data-bs-toggle="tab" data-bs-target="#examinations" type="button" role="tab">
                                <i class="bx bx-search-alt me-1"></i> {{ localize('global.examinations') }}
                                @if($dentistRegistration->examinations->count() > 0)
                                    <span class="badge bg-primary ms-1">{{ $dentistRegistration->examinations->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="treatments-tab" data-bs-toggle="tab" data-bs-target="#treatments" type="button" role="tab">
                                <i class="bx bx-plus-medical me-1"></i> {{ localize('global.treatments') }}
                                @if($dentistRegistration->treatments->count() > 0)
                                    <span class="badge bg-success ms-1">{{ $dentistRegistration->treatments->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="xrays-tab" data-bs-toggle="tab" data-bs-target="#xrays" type="button" role="tab">
                                <i class="bx bx-image me-1"></i> {{ localize('global.xrays') }}
                                @if($dentistRegistration->xrays->count() > 0)
                                    <span class="badge bg-info ms-1">{{ $dentistRegistration->xrays->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab">
                                <i class="bx bx-note me-1"></i> {{ localize('global.notes') }}
                                @if($dentistRegistration->dentalNotes->count() > 0)
                                    <span class="badge bg-warning ms-1">{{ $dentistRegistration->dentalNotes->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="prescription-tab" data-bs-toggle="tab" data-bs-target="#prescription" type="button" role="tab">
                                <i class="bx bx-notepad me-1"></i> {{ localize('global.prescription') }}
                                @if($dentistRegistration->appointment && $dentistRegistration->appointment->prescription->count() > 0)
                                    <span class="badge bg-success ms-1">{{ $dentistRegistration->appointment->prescription->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dental-chart-tab" data-bs-toggle="tab" data-bs-target="#dental-chart" type="button" role="tab">
                                <i class="bx bx-grid-alt me-1"></i> {{ localize('global.dental_chart') }}
                                @if($dentistRegistration->dentalCharts->count() > 0)
                                    <span class="badge bg-primary ms-1">{{ $dentistRegistration->dentalCharts->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="tab-content" id="dentistTabsContent">
                <!-- Examinations Tab (Lab Test Registration Section) -->
                <div class="tab-pane fade show active" id="examinations" role="tabpanel">
                    @if($dentistRegistration->appointment)
                        <x-lab-test-registration-section 
                            :entity="$dentistRegistration->appointment"
                            entity-type="appointment"
                            :entity-id="$dentistRegistration->appointment->id"
                            :can-add-test-registration="auth()->user()->can('register-patient-tests')"
                            :appointment-completed="$dentistRegistration->appointment->is_completed == 1"
                        />
                    @else
                    <div class="card">
                        <div class="card-body">
                                <p class="text-center text-muted">{{ localize('global.no_appointment_available') }}</p>
                                            </div>
                                            </div>
                    @endif
                </div>

                <!-- Treatments Tab -->
                <div class="tab-pane fade" id="treatments" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.dental_treatments') }}</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addTreatmentModal">
                                <i class="bx bx-plus"></i> {{ localize('global.add_treatment') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>{{ localize('global.date') }}</th>
                                            <th>{{ localize('global.treatment_type') }}</th>
                                            <th>{{ localize('global.tooth_number') }}</th>
                                            <th>{{ localize('global.description') }}</th>
                                            <th>{{ localize('global.status') }}</th>
                                            <th>{{ localize('global.cost') }}</th>
                                            <th>{{ localize('global.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dentistRegistration->treatments as $treatment)
                                            <tr>
                                                <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($treatment->treatment_date) }}</td>
                                                <td>{{ $treatment->treatment_type }}</td>
                                                <td>{{ $treatment->tooth_number ?? localize('global.not_available') }}</td>
                                                <td>{{ $treatment->treatment_description }}</td>
                                                <td>
                                                    @if($treatment->status == 'planned')
                                                        <span class="badge bg-secondary">{{ localize('global.planned') }}</span>
                                                    @elseif($treatment->status == 'in_progress')
                                                        <span class="badge bg-info">{{ localize('global.in_progress') }}</span>
                                                    @elseif($treatment->status == 'completed')
                                                        <span class="badge bg-success">{{ localize('global.completed') }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ localize('global.cancelled') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $treatment->cost ? number_format($treatment->cost, 2) : localize('global.not_available') }}</td>
                                                <td>
                                                    <form action="{{ route('dental-treatments.destroy', $treatment) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.are_you_sure') }}')">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">{{ localize('global.no_treatments_found') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- X-Rays Tab -->
                <div class="tab-pane fade" id="xrays" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.dental_xrays') }}</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addXrayModal">
                                <i class="bx bx-plus"></i> {{ localize('global.add_xray') }}
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @forelse($dentistRegistration->xrays as $xray)
                                    <div class="col-md-4 mb-3">
                                        <div class="card">
                                            <div class="card-body">
                                                <h6>{{ $xray->xray_type }}</h6>
                                                <p class="small text-muted">{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($xray->xray_date) }}</p>
                                                @if($xray->file_path)
                                                    <a href="{{ Storage::url($xray->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                                        <i class="bx bx-image"></i> {{ localize('global.view_image') }}
                                                    </a>
                                                @endif
                                                @if($xray->description)
                                                    <p class="mt-2">{{ $xray->description }}</p>
                                                @endif
                                                <form action="{{ route('dental-xrays.destroy', $xray) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.are_you_sure') }}')">
                                                        <i class="bx bx-trash"></i> {{ localize('global.delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-center text-muted">{{ localize('global.no_xrays_found') }}</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Tab -->
                <div class="tab-pane fade" id="notes" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.dental_notes') }}</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="bx bx-plus"></i> {{ localize('global.add_note') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @forelse($dentistRegistration->dentalNotes as $note)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title">
                                                    {{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($note->note_date) }}
                                                    <span class="badge bg-secondary">{{ $note->note_type }}</span>
                                                </h6>
                                                <p>{{ $note->content }}</p>
                                            </div>
                                            <div>
                                                <form action="{{ route('dental-notes.destroy', $note) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('{{ localize('global.are_you_sure') }}')">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p class="text-center text-muted">{{ localize('global.no_notes_found') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Prescription Tab -->
                <div class="tab-pane fade" id="prescription" role="tabpanel">
                    <div class="card">
                        <div class="card-body">
                            <!-- Prescription Section Vue Component -->
                            <div id="prescription-section-container" 
                                 data-appointment='@json($dentistRegistration->appointment ?? null)'
                                 data-permissions='@json([
                                     "canAddPrescription" => auth()->user()->can("add-prescription"),
                                     "canEditPrescription" => auth()->user()->can("edit-prescriptions"),
                                     "canDeletePrescription" => auth()->user()->can("delete-prescriptions")
                                 ])'>
                                <!-- Fallback content while Vue loads -->
                                <div class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2">{{ localize('global.loading_prescription_section') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dental Chart Tab -->
            <div class="tab-pane fade" id="dental-chart" role="tabpanel">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">{{ localize('global.dental_chart') }}</h5>
                            <div class="d-flex gap-2">
                                <a href="{{ route('dental-charts.history', $dentistRegistration) }}" class="btn btn-info btn-sm">
                                    <i class="bx bx-history"></i> {{ localize('global.history') }}
                                </a>
                                <a href="{{ route('dental-charts.print', $dentistRegistration) }}" class="btn btn-warning btn-sm" target="_blank">
                                    <i class="bx bx-printer"></i> {{ localize('global.print') }}
                                </a>
                                <a href="{{ route('dental-charts.export', $dentistRegistration) }}" class="btn btn-success btn-sm">
                                    <i class="bx bx-download"></i> {{ localize('global.export_pdf') }}
                                </a>
                                <a href="{{ route('dental-charts.create', $dentistRegistration) }}" class="btn btn-primary btn-sm">
                                    <i class="bx bx-plus"></i> {{ localize('global.add_tooth_record') }}
                                </a>
                            </div>
                        </div>
                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#chart-tab" type="button">
                                    <i class="bx bx-grid-alt me-1"></i> {{ localize('global.chart') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#images-tab" type="button">
                                    <i class="bx bx-image me-1"></i> {{ localize('global.images') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#periodontal-tab" type="button">
                                    <i class="bx bx-pulse me-1"></i> {{ localize('global.periodontal') }}
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#chart-treatments-tab" type="button">
                                    <i class="bx bx-plus-medical me-1"></i> {{ localize('global.treatments') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Chart Tab -->
                            <div class="tab-pane fade show active" id="chart-tab">
                                <div class="text-center mb-3">
                                    <h6>{{ localize('global.visual_tooth_chart') }}</h6>
                                </div>
                                @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth, 'dentistRegistration' => $dentistRegistration])
                                <div id="dental-chart-i18n"
                                     data-localize="{{ json_encode([
                                        'global.images' => localize('global.images'),
                                        'global.upload_image' => localize('global.upload_image'),
                                        'global.no_images_uploaded' => localize('global.no_images_uploaded'),
                                        'global.periodontal_measurements' => localize('global.periodontal_measurements'),
                                        'global.no_measurements_recorded' => localize('global.no_measurements_recorded'),
                                        'global.delete' => localize('global.delete'),
                                        'global.cancel' => localize('global.cancel'),
                                        'global.save' => localize('global.save'),
                                        'global.upload' => localize('global.upload'),
                                        'global.description' => localize('global.description'),
                                        'global.date' => localize('global.date'),
                                        'global.notes' => localize('global.notes'),
                                        'global.add_measurements' => localize('global.add_measurements'),
                                    ]) }}">
                                </div>
                                <script>
                                    // Provide a global localization function for Vue (needed by dental chart Vue components).
                                    // Do not override if another page already defined it.
                                    (function () {
                                        if (window.localize) return;
                                        const el = document.getElementById('dental-chart-i18n');
                                        try {
                                            window.__dentalChartTranslations = JSON.parse(el?.dataset?.localize || '{}');
                                        } catch (e) {
                                            window.__dentalChartTranslations = {};
                                        }
                                        window.localize = function (key) {
                                            return window.__dentalChartTranslations?.[key] || key;
                                        };
                                    })();
                                </script>
                                @vite('public/assets/js/vue/dental-chart-app.js')
                                
                                <!-- Chart Details Table -->
                                <div class="mt-4">
                                    <h6 class="mb-3">{{ localize('global.chart_details') }}</h6>
                                    <div class="table-responsive">
                                        <table class="table table-striped table-sm">
                                            <thead>
                                                <tr>
                                                    <th>{{ localize('global.tooth_number') }}</th>
                                                    <th>{{ localize('global.condition') }}</th>
                                                    <th>{{ localize('global.gum_health') }}</th>
                                                    <th>{{ localize('global.oral_hygiene_score') }}</th>
                                                    <th>{{ localize('global.pocket_depth') }}</th>
                                                    <th>{{ localize('global.bleeding') }}</th>
                                                    <th>{{ localize('global.mobility') }}</th>
                                                    <th>{{ localize('global.chart_date') }}</th>
                                                    <th>{{ localize('global.actions') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($latestCharts as $chart)
                                                    <tr>
                                                        <td><strong>{{ $chart->tooth_number }}</strong></td>
                                                        <td>
                                                            <span class="badge bg-{{ $chart->tooth_condition == 'healthy' ? 'success' : ($chart->tooth_condition == 'cavity' ? 'warning' : ($chart->tooth_condition == 'missing' ? 'secondary' : 'info')) }}">
                                                                {{ localize('global.' . $chart->tooth_condition) ?: ucfirst(str_replace('_', ' ', $chart->tooth_condition)) }}
                                                            </span>
                                                        </td>
                                                        <td>{{ $chart->gum_health ? (localize('global.gum_health_' . $chart->gum_health) ?: ucfirst($chart->gum_health)) : localize('global.not_available') }}</td>
                                                        <td>{{ $chart->oral_hygiene_score ?? localize('global.not_available') }}</td>
                                                        <td>{{ $chart->pocket_depth ? $chart->pocket_depth . ' ' . localize('global.mm') : localize('global.not_available') }}</td>
                                                        <td>
                                                            @if($chart->bleeding)
                                                                <span class="badge bg-danger">{{ localize('global.yes') }}</span>
                                                            @else
                                                                <span class="badge bg-success">{{ localize('global.no') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $chart->mobility ? (localize('global.mobility_' . $chart->mobility) ?: ucfirst($chart->mobility)) : localize('global.not_available') }}</td>
                                                        <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($chart->chart_date) }}</td>
                                                        <td>
                                                            <a href="{{ route('dental-charts.edit', $chart) }}" class="btn btn-sm btn-warning">
                                                                <i class="bx bx-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="9" class="text-center">{{ localize('global.no_charts_found') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Images Tab -->
                            <div class="tab-pane fade" id="images-tab">
                                @if($latestCharts->isNotEmpty() && $latestCharts->first())
                                    @php
                                        $firstChart = $latestCharts->first();
                                        $imagesData = $firstChart->images->map(function($img) {
                                            return [
                                                'id' => $img->id,
                                                'image_path' => $img->image_path,
                                                'image_url' => $img->image_url,
                                                'image_type' => $img->image_type,
                                                'description' => $img->description,
                                            ];
                                        })->toArray();
                                        $imagesJson = json_encode($imagesData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                    @endphp
                                    <div id="image-gallery-container" 
                                         data-dental-chart-id="{{ $firstChart->id }}"
                                         data-images="{{ $imagesJson }}">
                                        <!-- Vue component will mount here -->
                                    </div>
                                    @vite('public/assets/js/vue/dental-chart-advanced-app.js')
                                @else
                                    <p class="text-center text-muted">{{ localize('global.no_chart_data') }}</p>
                                @endif
                            </div>

                            <!-- Periodontal Tab -->
                            <div class="tab-pane fade" id="periodontal-tab">
                                @if($latestCharts->isNotEmpty() && $latestCharts->first())
                                    @php
                                        $firstChart = $latestCharts->first();
                                        $measurementsData = $firstChart->periodontalMeasurements->map(function($m) {
                                            return [
                                                'id' => $m->id,
                                                'measurement_point' => $m->measurement_point,
                                                'pocket_depth' => $m->pocket_depth,
                                                'recession' => $m->recession,
                                                'bleeding' => $m->bleeding,
                                                'plaque' => $m->plaque,
                                                'measurement_date' => $m->measurement_date ? $m->measurement_date->format('Y-m-d') : null,
                                                'notes' => $m->notes,
                                            ];
                                        })->toArray();
                                        $measurementsJson = json_encode($measurementsData, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                    @endphp
                                    <div id="periodontal-chart-container"
                                         data-dental-chart-id="{{ $firstChart->id }}"
                                         data-measurements="{{ $measurementsJson }}">
                                        <!-- Vue component will mount here -->
                                    </div>
                                    @vite('public/assets/js/vue/dental-chart-advanced-app.js')
                                @else
                                    <p class="text-center text-muted">{{ localize('global.no_chart_data') }}</p>
                                @endif
                            </div>

                            <!-- Treatments Tab -->
                            <div class="tab-pane fade" id="chart-treatments-tab">
                                @if($latestCharts->isNotEmpty())
                                    <div class="accordion" id="chartTreatmentsAccordion">
                                        @foreach($latestCharts as $chart)
                                            @php
                                                $toothTreatments = $dentistRegistration->treatments()
                                                    ->where('tooth_number', $chart->tooth_number)
                                                    ->get();
                                            @endphp
                                            @if($toothTreatments->count() > 0)
                                                <div class="accordion-item">
                                                    <h2 class="accordion-header" id="chartHeading{{ $chart->tooth_number }}">
                                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                                                data-bs-target="#chartCollapse{{ $chart->tooth_number }}" aria-expanded="false">
                                                            {{ localize('global.tooth') }} {{ $chart->tooth_number }} 
                                                            <span class="badge bg-primary ms-2">{{ $toothTreatments->count() }}</span>
                                                        </button>
                                                    </h2>
                                                    <div id="chartCollapse{{ $chart->tooth_number }}" class="accordion-collapse collapse" 
                                                         data-bs-parent="#chartTreatmentsAccordion">
                                                        <div class="accordion-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>{{ localize('global.date') }}</th>
                                                                            <th>{{ localize('global.treatment_type') }}</th>
                                                                            <th>{{ localize('global.description') }}</th>
                                                                            <th>{{ localize('global.status') }}</th>
                                                                            <th>{{ localize('global.cost') }}</th>
                                                                            <th>{{ localize('global.linked_to_chart') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($toothTreatments as $treatment)
                                                                            <tr>
                                                                                <td>{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($treatment->treatment_date) }}</td>
                                                                                <td>{{ $treatment->treatment_type }}</td>
                                                                                <td>{{ Str::limit($treatment->treatment_description, 50) }}</td>
                                                                                <td>
                                                                                    @if($treatment->status == 'planned')
                                                                                        <span class="badge bg-secondary">{{ localize('global.planned') }}</span>
                                                                                    @elseif($treatment->status == 'in_progress')
                                                                                        <span class="badge bg-info">{{ localize('global.in_progress') }}</span>
                                                                                    @elseif($treatment->status == 'completed')
                                                                                        <span class="badge bg-success">{{ localize('global.completed') }}</span>
                                                                                    @else
                                                                                        <span class="badge bg-danger">{{ localize('global.cancelled') }}</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td>{{ $treatment->cost ? number_format($treatment->cost, 2) : 'N/A' }}</td>
                                                                                <td>
                                                                                    @if($treatment->dental_chart_id == $chart->id)
                                                                                        <span class="badge bg-success">{{ localize('global.yes') }}</span>
                                                                                    @else
                                                                                        <span class="badge bg-secondary">{{ localize('global.no') }}</span>
                                                                                    @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @if($latestCharts->filter(function($chart) use ($dentistRegistration) {
                                        return $dentistRegistration->treatments()->where('tooth_number', $chart->tooth_number)->count() > 0;
                                    })->isEmpty())
                                        <p class="text-muted text-center">{{ localize('global.no_treatments_found') }}</p>
                                    @endif
                                @else
                                    <p class="text-muted text-center">{{ localize('global.no_charts_found') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Examination Modal -->
    <div class="modal fade" id="addExaminationModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_examination') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dental-examinations.store', $dentistRegistration) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="examination_date" class="form-label">{{ localize('global.examination_date') }}</label>
                            <input type="text" class="form-control datepicker_dari" id="examination_date" name="examination_date" 
                                   placeholder="{{ localize('global.select_date') }}" required readonly>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="chief_complaint" class="form-label">{{ localize('global.chief_complaint') }}</label>
                                <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="diagnosis" class="form-label">{{ localize('global.diagnosis') }}</label>
                                <textarea class="form-control" id="diagnosis" name="diagnosis" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="clinical_findings" class="form-label">{{ localize('global.clinical_findings') }}</label>
                                <textarea class="form-control" id="clinical_findings" name="clinical_findings" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="treatment_plan" class="form-label">{{ localize('global.treatment_plan') }}</label>
                                <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Treatment Modal -->
    <div class="modal fade" id="addTreatmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_treatment') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dental-treatments.store', $dentistRegistration) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="treatment_type" class="form-label">{{ localize('global.treatment_type') }}</label>
                                <input type="text" class="form-control" id="treatment_type" name="treatment_type" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="treatment_date" class="form-label">{{ localize('global.treatment_date') }} <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker_dari" id="treatment_date" name="treatment_date" 
                                       placeholder="{{ localize('global.select_date') }}" required readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ localize('global.tooth_number') }}</label>
                                <select class="form-select" id="tooth_number" name="tooth_number">
                                    <option value="">{{ localize('global.select') }} / {{ localize('global.none') }}</option>
                                    <optgroup label="Upper Right (11-18)">
                                        @for($i = 11; $i <= 18; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </optgroup>
                                    <optgroup label="Upper Left (21-28)">
                                        @for($i = 21; $i <= 28; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </optgroup>
                                    <optgroup label="Lower Left (31-38)">
                                        @for($i = 31; $i <= 38; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </optgroup>
                                    <optgroup label="Lower Right (41-48)">
                                        @for($i = 41; $i <= 48; $i++)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">{{ localize('global.status') }}</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="planned">{{ localize('global.planned') }}</option>
                                    <option value="in_progress">{{ localize('global.in_progress') }}</option>
                                    <option value="completed">{{ localize('global.completed') }}</option>
                                    <option value="cancelled">{{ localize('global.cancelled') }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cost" class="form-label">{{ localize('global.cost') }}</label>
                                <input type="number" step="0.01" class="form-control" id="cost" name="cost">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="treatment_description" class="form-label">{{ localize('global.description') }}</label>
                                <textarea class="form-control" id="treatment_description" name="treatment_description" rows="3" required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                                <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add X-Ray Modal -->
    <div class="modal fade" id="addXrayModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_xray') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dental-xrays.store', $dentistRegistration) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="xray_type" class="form-label">{{ localize('global.xray_type') }}</label>
                            <input type="text" class="form-control" id="xray_type" name="xray_type" required>
                        </div>
                        <div class="mb-3">
                            <label for="xray_date" class="form-label">{{ localize('global.xray_date') }}</label>
                            <input type="date" class="form-control" id="xray_date" name="xray_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="file" class="form-label">{{ localize('global.file') }}</label>
                            <input type="file" class="form-control" id="file" name="file" accept="image/*,.pdf">
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">{{ localize('global.description') }}</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ localize('global.add_note') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('dental-notes.store', $dentistRegistration) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="note_date" class="form-label">{{ localize('global.note_date') }}</label>
                            <input type="text" class="form-control datepicker_dari" id="note_date" name="note_date" 
                                   placeholder="{{ localize('global.select_date') }}" required readonly>
                        </div>
                        <div class="mb-3">
                            <label for="note_type" class="form-label">{{ localize('global.note_type') }}</label>
                            <input type="text" class="form-control" id="note_type" name="note_type" value="general" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">{{ localize('global.content') }}</label>
                            <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- Vue.js Prescription Section -->
    @vite(['public/assets/js/vue/appointment-prescription-app.js'])
    
    <!-- Persian Datepicker Library -->
    <script src="{{ asset('assets/persian date2/js/persianDatepicker.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/persian date2/css/persianDatepicker-default.css') }}" type="text/css" />
    
    <script>
        // Initialize Persian datepicker for treatment date and examination date
        $(document).ready(function() {
            // Initialize Persian date picker for treatment_date in modal
            $('#addTreatmentModal').on('shown.bs.modal', function() {
                const treatmentDateInput = $('#treatment_date');
                if (treatmentDateInput.length && !treatmentDateInput.data('persianDatepicker')) {
                    treatmentDateInput.persianDatepicker({
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
                }
            });
            
            // Also initialize if modal is already shown (for page refresh scenarios)
            if ($('#addTreatmentModal').hasClass('show')) {
                const treatmentDateInput = $('#treatment_date');
                if (treatmentDateInput.length && !treatmentDateInput.data('persianDatepicker')) {
                    treatmentDateInput.persianDatepicker({
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
                }
            }
            
            // Initialize Persian date picker for examination_date in modal
            $('#addExaminationModal').on('shown.bs.modal', function() {
                const examinationDateInput = $('#examination_date');
                if (examinationDateInput.length && !examinationDateInput.data('persianDatepicker')) {
                    examinationDateInput.persianDatepicker({
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
                }
            });
            
            // Also initialize if modal is already shown (for page refresh scenarios)
            if ($('#addExaminationModal').hasClass('show')) {
                const examinationDateInput = $('#examination_date');
                if (examinationDateInput.length && !examinationDateInput.data('persianDatepicker')) {
                    examinationDateInput.persianDatepicker({
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
                }
            }
            
            // Initialize Persian date picker for note_date in modal
            $('#addNoteModal').on('shown.bs.modal', function() {
                const noteDateInput = $('#note_date');
                if (noteDateInput.length && !noteDateInput.data('persianDatepicker')) {
                    noteDateInput.persianDatepicker({
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
                }
            });
            
            // Also initialize if modal is already shown (for page refresh scenarios)
            if ($('#addNoteModal').hasClass('show')) {
                const noteDateInput = $('#note_date');
                if (noteDateInput.length && !noteDateInput.data('persianDatepicker')) {
                    noteDateInput.persianDatepicker({
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
                }
            }
        });
        
        // Auto-activate dental-chart tab if redirected from dental-charts.show
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('tab') || sessionStorage.getItem('activeDentalChartTab');
            
            if (activeTab === 'dental-chart' || sessionStorage.getItem('activeDentalChartTab') === 'dental-chart') {
                // Activate the dental-chart tab
                const dentalChartTab = document.getElementById('dental-chart-tab');
                const dentalChartPane = document.getElementById('dental-chart');
                
                if (dentalChartTab && dentalChartPane) {
                    // Remove active class from all tabs and panes
                    document.querySelectorAll('#dentistTabs .nav-link').forEach(tab => {
                        tab.classList.remove('active');
                    });
                    document.querySelectorAll('#dentistTabsContent .tab-pane').forEach(pane => {
                        pane.classList.remove('show', 'active');
                    });
                    
                    // Activate dental-chart tab
                    dentalChartTab.classList.add('active');
                    dentalChartPane.classList.add('show', 'active');
                    
                    // Clear the session storage
                    sessionStorage.removeItem('activeDentalChartTab');
                }
            }
            
            // Store active tab in session when dental-chart tab is clicked
            const dentalChartTabBtn = document.getElementById('dental-chart-tab');
            if (dentalChartTabBtn) {
                dentalChartTabBtn.addEventListener('shown.bs.tab', function() {
                    sessionStorage.setItem('activeDentalChartTab', 'dental-chart');
                });
            }
        });
    </script>
@endsection
