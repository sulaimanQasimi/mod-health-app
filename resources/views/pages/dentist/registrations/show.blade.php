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
                                        <div class="fw-bold">{{ $dentistRegistration->dentist->name ?? 'N/A' }}</div>
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
                <!-- Examinations Tab -->
                <div class="tab-pane fade show active" id="examinations" role="tabpanel">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">{{ localize('global.dental_examinations') }}</h5>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addExaminationModal">
                                <i class="bx bx-plus"></i> {{ localize('global.add_examination') }}
                            </button>
                        </div>
                        <div class="card-body">
                            @forelse($dentistRegistration->examinations as $examination)
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="card-title">{{ \HanifHefaz\Dcter\Dcter::GregorianToJalali($examination->examination_date) }}</h6>
                                                @if($examination->chief_complaint)
                                                    <p><strong>{{ localize('global.chief_complaint') }}:</strong> {{ $examination->chief_complaint }}</p>
                                                @endif
                                                @if($examination->clinical_findings)
                                                    <p><strong>{{ localize('global.clinical_findings') }}:</strong> {{ $examination->clinical_findings }}</p>
                                                @endif
                                                @if($examination->diagnosis)
                                                    <p><strong>{{ localize('global.diagnosis') }}:</strong> {{ $examination->diagnosis }}</p>
                                                @endif
                                                @if($examination->treatment_plan)
                                                    <p><strong>{{ localize('global.treatment_plan') }}:</strong> {{ $examination->treatment_plan }}</p>
                                                @endif
                                                @if($examination->notes)
                                                    <p><strong>{{ localize('global.notes') }}:</strong> {{ $examination->notes }}</p>
                                                @endif
                                            </div>
                                            <div>
                                                <form action="{{ route('dental-examinations.destroy', $examination) }}" method="POST" class="d-inline">
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
                                <p class="text-center text-muted">{{ localize('global.no_examinations_found') }}</p>
                            @endforelse
                        </div>
                    </div>
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
                                                <td>{{ $treatment->tooth_number ?? 'N/A' }}</td>
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
                                                <td>{{ $treatment->cost ? number_format($treatment->cost, 2) : 'N/A' }}</td>
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
            </div>

            <!-- Dental Chart Tab -->
            <div class="tab-pane fade" id="dental-chart" role="tabpanel">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">{{ localize('global.dental_chart') }}</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dental-charts.show', $dentistRegistration) }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-show"></i> {{ localize('global.view_full_chart') }}
                            </a>
                            <a href="{{ route('dental-charts.create', $dentistRegistration) }}" class="btn btn-success btn-sm">
                                <i class="bx bx-plus"></i> {{ localize('global.add_tooth_record') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @php
                            $dentistRegistration->load('dentalCharts');
                            $allTeeth = [];
                            for ($i = 11; $i <= 18; $i++) $allTeeth[$i] = null;
                            for ($i = 21; $i <= 28; $i++) $allTeeth[$i] = null;
                            for ($i = 31; $i <= 38; $i++) $allTeeth[$i] = null;
                            for ($i = 41; $i <= 48; $i++) $allTeeth[$i] = null;
                            
                            $latestCharts = $dentistRegistration->dentalCharts()
                                ->orderBy('chart_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->get()
                                ->unique('tooth_number')
                                ->keyBy('tooth_number');
                            
                            foreach ($latestCharts as $toothNumber => $chart) {
                                $allTeeth[$toothNumber] = $chart;
                            }
                        @endphp
                        @include('pages.dentist.charts.partials.tooth-chart', ['allTeeth' => $allTeeth, 'dentistRegistration' => $dentistRegistration])
                        @vite('public/assets/js/vue/dental-chart-app.js')
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
                            <input type="date" class="form-control" id="examination_date" name="examination_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="chief_complaint" class="form-label">{{ localize('global.chief_complaint') }}</label>
                            <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="clinical_findings" class="form-label">{{ localize('global.clinical_findings') }}</label>
                            <textarea class="form-control" id="clinical_findings" name="clinical_findings" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">{{ localize('global.diagnosis') }}</label>
                            <textarea class="form-control" id="diagnosis" name="diagnosis" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="treatment_plan" class="form-label">{{ localize('global.treatment_plan') }}</label>
                            <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="3"></textarea>
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
                                <label for="treatment_date" class="form-label">{{ localize('global.treatment_date') }}</label>
                                <input type="date" class="form-control" id="treatment_date" name="treatment_date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tooth_number" class="form-label">{{ localize('global.tooth_number') }}</label>
                                <input type="text" class="form-control" id="tooth_number" name="tooth_number">
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
                            <input type="date" class="form-control" id="note_date" name="note_date" value="{{ date('Y-m-d') }}" required>
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
