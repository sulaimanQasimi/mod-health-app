@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="content-wrapper">
        @if (Session::has('success') || Session::has('error'))
        @include('components.toast')
        @endif

        <!-- Search and Filters Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ localize('global.search_and_filters') }}</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ request()->url() }}" class="row g-3" id="patient-filter-form">
                    <div class="col-md-4">
                        <label for="search" class="form-label">{{ localize('global.search_patient') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" class="form-control" id="search" name="search" 
                                value="{{ request('search') }}" placeholder="{{ localize('global.search_by_patient_name') }}">
                            @if(request('search'))
                                <button type="button" class="btn btn-outline-danger" id="clearSearch" title="{{ localize('global.clear_search') }}">
                                    <i class="bx bx-x"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ localize('global.status') }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ localize('global.all') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ localize('global.pending') }}</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ localize('global.in_progress') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ localize('global.cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priority" class="form-label">{{ localize('global.priority') }}</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">{{ localize('global.all') }}</option>
                            <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ localize('global.normal') }}</option>
                            <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ localize('global.urgent') }}</option>
                            <option value="stat" {{ request('priority') == 'stat' ? 'selected' : '' }}>{{ localize('global.stat') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="date_from" class="form-label">{{ localize('global.date_from') }}</label>
                        <input type="text" class="form-control datepicker_dari pdp-el" id="date_from" name="date_from" 
                            value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="date_to" class="form-label">{{ localize('global.date_to') }}</label>
                        <input type="text" class="form-control datepicker_dari pdp-el" id="date_to" name="date_to" 
                            value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                        </button>
                        <a href="{{ request()->url() }}" class="btn btn-outline-secondary ms-2">
                            <i class="bx bx-refresh me-1"></i>{{ localize('global.reset') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Main Results Card -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ localize('global.test_results') }} - {{ localize('global.patients') }}</h5>
                <div class="d-flex align-items-center">
                    <span class="badge bg-primary me-2">{{ $patients->count() }} {{ localize('global.patients') }}</span>
                </div>
            </div>

            <!-- Loading overlay -->
            <div id="loading-overlay" class="loading-overlay" style="display: none;">
                <div class="loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ localize('global.loading') }}...</span>
                    </div>
                    <p class="mt-2">{{ localize('global.loading') }}...</p>
                </div>
            </div>

            <div class="card-body">
                @if($patients->count() > 0)
                    <div id="registrations-container">
                        <div class="accordion" id="patientsAccordion">
                            @foreach($patients as $patientId => $registrations)
                                @php
                                    $patient = $registrations->first()->testable->patient ?? null;
                                    $accordionId = 'patient-' . $patientId;
                                @endphp
                                
                                @if($patient)
                                    @php
                                        $pendingRegistrations = $registrations->filter(function($reg) {
                                            return $reg->status === 'pending' && !$reg->assigned_to;
                                        });
                                    @endphp
                                    <div class="accordion-item mb-3">
                                        <!-- Top Header Section with Accept All Button -->
                                        <div class="accordion-top-header p-3 border-bottom bg-light">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-md me-3">
                                                        <div class="avatar-initial bg-primary text-white rounded-circle">
                                                            <i class="bx bx-user bx-md"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $patient->name }} {{ $patient->last_name }}</h6>
                                                        <small class="text-muted">
                                                            <i class="bx bx-user me-1"></i>{{ $patient->father_name }} | 
                                                            <i class="bx bx-calendar me-1"></i>{{ $patient->age }} | 
                                                            <i class="bx bx-phone me-1"></i>{{ $patient->phone }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge bg-primary rounded-pill">
                                                        {{ $registrations->count() }} {{ localize('global.tests') ?? 'Tests' }}
                                                    </span>
                                                    @if($registrations->count() > 0 && Route::is('laboratory.results.in-progress'))
                                                        <button type="button" 
                                                                class="btn btn-info fill-all-params-btn" 
                                                                data-patient-id="{{ $patientId }}"
                                                                data-registration-ids="{{ $registrations->pluck('id')->implode(',') }}"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#fillAllParamsModal"
                                                                title="{{ localize('global.fill_all_parameters') ?? 'Fill All Parameters' }}">
                                                            <i class="bx bx-edit me-1"></i>
                                                            {{ localize('global.fill_all') ?? 'Fill All' }}
                                                        </button>
                                                    @endif
                                                    @if($pendingRegistrations->count() > 0)
                                                        <button type="button" 
                                                                class="btn btn-success accept-all-btn" 
                                                                data-patient-id="{{ $patientId }}"
                                                                data-registration-ids="{{ $pendingRegistrations->pluck('id')->implode(',') }}"
                                                                title="{{ localize('global.accept_all_tests') ?? 'Accept All Tests' }}">
                                                            <i class="bx bx-check-double me-1"></i>
                                                            {{ localize('global.accept_all') ?? 'Accept All' }}
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Accordion Header -->
                                        <h2 class="accordion-header" id="heading-{{ $accordionId }}">
                                            <button class="accordion-button collapsed" type="button" 
                                                    data-bs-toggle="collapse" data-bs-target="#collapse-{{ $accordionId }}" 
                                                    aria-expanded="false" 
                                                    aria-controls="collapse-{{ $accordionId }}">
                                                <div class="d-flex align-items-center w-100">
                                                    <i class="bx bx-chevron-down me-2"></i>
                                                    <span>{{ localize('global.view_tests') ?? 'View Tests' }}</span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse-{{ $accordionId }}" 
                                             class="accordion-collapse collapse" 
                                             aria-labelledby="heading-{{ $accordionId }}" 
                                             data-bs-parent="#patientsAccordion">
                                            <div class="accordion-body p-0">
                                                <div class="table-responsive">
                                                    <table class="table table-hover mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th class="text-center">{{ localize('global.test_name') }}</th>
                                                                <th class="text-center">{{ localize('global.test_type') }}</th>
                                                                <th class="text-center">{{ localize('global.ref_no') }}</th>
                                                                <th class="text-center">{{ localize('global.status') }}</th>
                                                                <th class="text-center">{{ localize('global.priority') }}</th>
                                                                <th class="text-center">{{ localize('global.doctor') }}</th>
                                                                <th class="text-center">{{ localize('global.date') }}</th>
                                                                <th class="text-center">{{ localize('global.actions') }}</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($registrations as $registration)
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="d-flex align-items-center justify-content-center">
                                                                        <i class="bx bx-test-tube me-2 text-primary"></i>
                                                                        <strong>{{ $registration->labType->name ?? '—' }}</strong>
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($registration->labType && $registration->labType->directLabTestParameters && $registration->labType->directLabTestParameters->count() > 0)
                                                                        <span class="badge bg-info">{{ localize('global.parametered') }}</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">{{ localize('global.text_based') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <code class="bg-light px-2 py-1 rounded">{{ $registration->ref_no }}</code>
                                                                </td>
                                                                <td class="text-center">
                                                                    @php
                                                                        $statusClass = match($registration->status) {
                                                                            'pending' => 'bg-warning',
                                                                            'in_progress' => 'bg-info',
                                                                            'completed' => 'bg-success',
                                                                            'cancelled' => 'bg-danger',
                                                                            default => 'bg-secondary'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $statusClass }} rounded-pill">
                                                                        {{ localize('global.' . $registration->status) }}
                                                                    </span>
                                                                    
                                                                    @if($registration->assigned_to)
                                                                        <br>
                                                                        <small class="text-muted">
                                                                            <i class="bx bx-user me-1"></i>
                                                                            {{ $registration->assignedTo->name ?? 'Unknown' }}
                                                                        </small>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @php
                                                                        $priorityClass = match($registration->priority) {
                                                                            'normal' => 'bg-primary',
                                                                            'urgent' => 'bg-warning',
                                                                            'stat' => 'bg-danger',
                                                                            default => 'bg-secondary'
                                                                        };
                                                                    @endphp
                                                                    <span class="badge {{ $priorityClass }} rounded-pill">
                                                                        {{ localize('global.' . $registration->priority) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="d-flex align-items-center justify-content-center">
                                                                        <i class="bx bx-user me-1 text-muted"></i>
                                                                        {{ $registration->doctor->name ?? '—' }}
                                                                    </div>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($registration->testable && $registration->testable->date)
                                                                        <span class="text-muted">
                                                                            {{ \Verta(\Carbon\Carbon::parse($registration->testable->date))->formatJalaliDate() }}
                                                                        </span>
                                                                    @else
                                                                        <span class="text-muted">—</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <div class="btn-group btn-group-sm" role="group">
                                                                        @if($registration->status === 'pending')
                                                                            @if(!$registration->assigned_to)
                                                                                <button type="button" class="btn btn-success btn-sm accept-test-btn" 
                                                                                        data-registration-id="{{ $registration->id }}" 
                                                                                        title="{{ localize('global.accept_test') }}">
                                                                                    <i class="bx bx-check"></i>
                                                                                </button>
                                                                            @else
                                                                                <span class="badge bg-info">{{ localize('global.assigned') }}</span>
                                                                            @endif
                                                                        @endif
                                                                        
                                                                        @if($registration->status === 'in_progress')
                                                                            <a href="{{ route('laboratory.results.show', $registration->id) }}" class="btn btn-primary btn-sm" title="{{ localize('global.enter_results') }}">
                                                                                <i class="bx bx-edit"></i>
                                                                            </a>
                                                                            @if(!$registration->labType || !$registration->labType->directLabTestParameters || $registration->labType->directLabTestParameters->count() == 0)
                                                                                <button type="button" 
                                                                                        class="btn btn-info btn-sm attach-files-btn" 
                                                                                        data-registration-id="{{ $registration->id }}"
                                                                                        data-ref-no="{{ $registration->ref_no }}"
                                                                                        data-test-name="{{ $registration->labType->name ?? 'Test' }}"
                                                                                        data-bs-toggle="modal"
                                                                                        data-bs-target="#attachFilesModal"
                                                                                        title="{{ localize('global.attach_files') ?? 'Attach Files' }}">
                                                                                    <i class="bx bx-paperclip"></i>
                                                                                </button>
                                                                            @endif
                                                                            <form action="{{ route('laboratory.registrations.mark-completed', $registration->id) }}" method="POST" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-success btn-sm" title="{{ localize('global.mark_completed') }}">
                                                                                    <i class="bx bx-check"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                        
                                                                        @if($registration->status === 'completed')
                                                                            <a href="{{ route('laboratory.results.show', $registration->id) }}" class="btn btn-outline-primary btn-sm" title="{{ localize('global.view_results') }}">
                                                                                <i class="bx bx-show"></i>
                                                                            </a>
                                                                            <a href="{{ route('laboratory.reports.print', $registration->ref_no) }}" class="btn btn-outline-success btn-sm" title="{{ localize('global.print_report') }}" target="_blank">
                                                                                <i class="bx bx-printer"></i>
                                                                            </a>
                                                                        @endif
                                                                        
                                                                        @if(in_array($registration->status, ['pending', 'in_progress']))
                                                                            <form action="{{ route('laboratory.registrations.cancel', $registration->id) }}" method="POST" class="d-inline">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-danger btn-sm" title="{{ localize('global.cancel_registration') }}">
                                                                                    <i class="bx bx-x"></i>
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
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
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state">
                            <i class="bx bx-test-tube display-1 text-muted mb-3"></i>
                            <h5 class="text-muted">{{ localize('global.no_test_registrations_found') }}</h5>
                            <p class="text-muted">{{ localize('global.no_test_registrations_message') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Attach Files Modal -->
<div class="modal fade" id="attachFilesModal" tabindex="-1" aria-labelledby="attachFilesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attachFilesModalLabel">
                    <i class="bx bx-paperclip me-2"></i>
                    <span id="attachFilesModalTitle">{{ localize('global.attach_files') ?? 'Attach Files' }}</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <div class="alert alert-info">
                        <i class="bx bx-info-circle me-2"></i>
                        <strong id="attachFilesTestInfo"></strong>
                        <br>
                        <small id="attachFilesRefNo"></small>
                    </div>
                </div>
                
                <form id="attachFilesForm" enctype="multipart/form-data" style="display: block !important;">
                    @csrf
                    <input type="hidden" id="attachFilesTestResultId" name="test_result_id">
                    <input type="hidden" id="attachFilesRegistrationId" name="registration_id">
                    
                    <div class="mb-3">
                        <label for="attachmentFiles" class="form-label">
                            {{ localize('global.select_files') ?? 'Select Files' }}
                            <small class="text-muted">({{ localize('global.pdf_excel_images') ?? 'PDF, Excel, Images, etc.' }})</small>
                        </label>
                        <input type="file" 
                               class="form-control" 
                               id="attachmentFiles" 
                               name="files[]" 
                               multiple 
                               accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                        <small class="text-muted">{{ localize('global.max_file_size_10mb') ?? 'Maximum file size: 10MB per file' }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="attachmentDescription" class="form-label">{{ localize('global.description') ?? 'Description' }} ({{ localize('global.optional') ?? 'Optional' }})</label>
                        <textarea class="form-control" 
                                  id="attachmentDescription" 
                                  name="description" 
                                  rows="2" 
                                  placeholder="{{ localize('global.add_description_here') ?? 'Add description here...' }}"></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary" id="uploadAttachmentsBtn">
                            <i class="bx bx-upload me-1"></i>
                            {{ localize('global.upload_files') ?? 'Upload Files' }}
                        </button>
                    </div>
                </form>
                
                <hr>
                
                <div class="mb-2">
                    <h6 class="mb-0">
                        <i class="bx bx-file me-1"></i>
                        {{ localize('global.attached_files') ?? 'Attached Files' }}
                    </h6>
                </div>
                
                <div id="attachmentsList" class="list-group">
                    <div class="text-center py-3 text-muted">
                        <i class="bx bx-loader-alt bx-spin"></i>
                        {{ localize('global.loading') ?? 'Loading...' }}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ localize('global.close') ?? 'Close' }}
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fill All Parameters Modal -->
<div class="modal fade" id="fillAllParamsModal" tabindex="-1" aria-labelledby="fillAllParamsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fillAllParamsModalLabel">
                    <i class="bx bx-edit me-2"></i>
                    {{ localize('global.fill_all_parameters') ?? 'Fill All Parameters' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="fillAllParamsContent">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">{{ localize('global.loading') ?? 'Loading...' }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ localize('global.cancel') ?? 'Cancel' }}
                </button>
                <button type="button" class="btn btn-primary" id="saveAllParamsBtn">
                    <i class="bx bx-save me-1"></i>
                    {{ localize('global.save_all') ?? 'Save All' }}
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        border-radius: 0.375rem;
    }
    
    .loading-spinner {
        text-align: center;
    }
    
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .empty-state {
        padding: 3rem 1rem;
    }
    
    .avatar {
        width: 2.5rem;
        height: 2.5rem;
    }
    
    .avatar-md {
        width: 3rem;
        height: 3rem;
    }
    
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .table th {
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.75rem;
    }

    /* Accordion Styling */
    .accordion-item {
        border: 1px solid #e3e6f0;
        border-radius: 0.5rem;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .accordion-item:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    /* Accordion Top Header */
    .accordion-top-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    .accordion-top-header h6 {
        color: #5e5873;
        font-weight: 600;
        font-size: 1.1rem;
        margin-bottom: 0.25rem;
    }

    .accordion-top-header .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    .accordion-top-header .btn {
        font-weight: 500;
        padding: 0.5rem 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .accordion-top-header .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .accordion-button {
        background-color: #fff;
        padding: 0.875rem 1.5rem;
        font-weight: 500;
        border: none;
        box-shadow: none;
        color: #5e5873;
    }

    .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: #5e5873;
        box-shadow: none;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: transparent;
    }

    .accordion-button::after {
        background-size: 1.25rem;
        transition: transform 0.3s ease;
    }

    .accordion-button:not(.collapsed)::after {
        transform: rotate(180deg);
    }

    .accordion-body {
        padding: 0;
    }

    .accordion-body .table {
        margin-bottom: 0;
    }

    .accordion-body .table thead th {
        background-color: #f8f9fa;
        font-size: 0.875rem;
        padding: 0.75rem 0.5rem;
    }

    .accordion-body .table tbody td {
        padding: 0.875rem 0.5rem;
    }

    /* Patient Header Styling */
    .accordion-button h6 {
        color: #5e5873;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .accordion-button small {
        font-size: 0.875rem;
    }

    .accordion-button .badge {
        font-size: 0.875rem;
        padding: 0.5rem 0.75rem;
    }

    /* Smooth Collapse Animation */
    .accordion-collapse {
        transition: height 0.35s ease;
    }

</style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Initialize Persian datepicker
    $('.datepicker_dari').persianDatepicker({
        format: 'YYYY/MM/DD',
        observer: true,
    });
    
    // Auto-submit on filter change
    $('select[name="status"], select[name="priority"]').change(function() {
        $('#patient-filter-form').submit();
    });
    
    // Clear search functionality
    $('#clearSearch').click(function() {
        $('input[name="search"]').val('');
        $('#patient-filter-form').submit();
    });
    
    // Handle filter form submission with AJAX
    $('#patient-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        showLoading();
        
        $.ajax({
            url: '{{ request()->url() }}?' + $(this).serialize(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(response) {
                $('#registrations-container').html($(response).find('#registrations-container').html()).addClass('fade-in');
                history.pushState && history.pushState(null, null, this.url);
            },
            error: function() {
                alert('{{ localize("global.error_loading_data") }}');
            },
            complete: hideLoading
        });
    });
    
    // Handle accept test button
    $(document).on('click', '.accept-test-btn', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '/laboratory/results/' + $btn.data('registration-id') + '/accept',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    toastr.error(response.message);
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || '{{ localize("global.error_accepting_test") }}');
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Handle accept all tests button
    $(document).on('click', '.accept-all-btn', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        // Use attr() to get the raw string value
        const registrationIdsStr = $btn.attr('data-registration-ids') || '';
        
        // Handle both string and array formats
        let registrationIds = [];
        if (registrationIdsStr && typeof registrationIdsStr === 'string') {
            registrationIds = registrationIdsStr.split(',').filter(id => id.trim() !== '');
        } else {
            // Fallback to data() method
            const registrationIdsData = $btn.data('registration-ids');
            if (typeof registrationIdsData === 'string') {
                registrationIds = registrationIdsData.split(',').filter(id => id.trim() !== '');
            } else if (Array.isArray(registrationIdsData)) {
                registrationIds = registrationIdsData.filter(id => id != null && id !== '');
            }
        }
        
        if (registrationIds.length === 0) {
            toastr.warning('{{ localize("global.no_pending_tests") ?? "No pending tests to accept" }}');
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ localize("global.accepting") ?? "Accepting..." }}');
        
        let completed = 0;
        let failed = 0;
        const total = registrationIds.length;
        
        // Accept all tests sequentially
        const acceptNext = function(index) {
            if (index >= registrationIds.length) {
                // All done
                if (failed === 0) {
                    toastr.success('{{ localize("global.all_tests_accepted_successfully") ?? "All tests accepted successfully" }}');
                } else {
                    toastr.warning('{{ localize("global.some_tests_failed") ?? "Some tests failed to accept" }}: ' + completed + '/' + total);
                }
                setTimeout(() => location.reload(), 1000);
                return;
            }
            
            const registrationId = registrationIds[index].trim();
            
            $.ajax({
                url: '/laboratory/results/' + registrationId + '/accept',
                method: 'POST',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        completed++;
                    } else {
                        failed++;
                    }
                    acceptNext(index + 1);
                },
                error: function(xhr) {
                    failed++;
                    acceptNext(index + 1);
                }
            });
        };
        
        acceptNext(0);
    });
    
    // Handle form submissions with loading states
    $('form[action*="mark-"], form[action*="cancel"]').on('submit', function() {
        const $btn = $(this).find('button[type="submit"]');
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>' + $btn.text());
        setTimeout(() => $btn.prop('disabled', false), 3000);
    });
    
    // Helper functions
    function showLoading() {
        $('#loading-overlay').show();
    }
    
    function hideLoading() {
        $('#loading-overlay').hide();
    }
    
    // Handle browser back/forward
    window.addEventListener('popstate', () => location.reload());
    
    
    // Handle Fill All Parameters Modal
    let currentRegistrationIds = [];
    let currentPatientId = null;
    
    $('#fillAllParamsModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        // Use attr() to get the raw string value, then parse it
        const registrationIdsStr = button.attr('data-registration-ids') || '';
        
        // Handle both string and array formats
        if (registrationIdsStr && typeof registrationIdsStr === 'string') {
            currentRegistrationIds = registrationIdsStr.split(',').filter(id => id.trim() !== '');
        } else {
            // Fallback to data() method if attr() doesn't work
            const registrationIdsData = button.data('registration-ids');
            if (typeof registrationIdsData === 'string') {
                currentRegistrationIds = registrationIdsData.split(',').filter(id => id.trim() !== '');
            } else if (Array.isArray(registrationIdsData)) {
                currentRegistrationIds = registrationIdsData.filter(id => id != null && id !== '');
            } else {
                currentRegistrationIds = [];
            }
        }
        
        currentPatientId = button.attr('data-patient-id') || button.data('patient-id');
        
        // Load parameters for all tests
        if (currentRegistrationIds.length > 0) {
            loadAllParameters(currentRegistrationIds);
        } else {
            $('#fillAllParamsContent').html('<div class="alert alert-warning">{{ localize("global.no_tests_found") ?? "No tests found" }}</div>');
        }
    });
    
    function loadAllParameters(registrationIds) {
        const content = $('#fillAllParamsContent');
        content.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ localize("global.loading") ?? "Loading..." }}</span></div></div>');
        
        $.ajax({
            url: '{{ route("laboratory.results.load-all-parameters") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                registration_ids: registrationIds
            },
            success: function(response) {
                if (response.success) {
                    renderParametersForm(response.data);
                } else {
                    content.html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function(xhr) {
                content.html('<div class="alert alert-danger">{{ localize("global.error_loading_data") ?? "Error loading data" }}</div>');
            }
        });
    }
    
    function renderParametersForm(data) {
        let html = '<form id="fillAllParamsForm">';
        
        // Group by test registration
        Object.keys(data.tests).forEach(registrationId => {
            const test = data.tests[registrationId];
            html += `
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="bx bx-test-tube me-2"></i>
                            ${test.lab_type_name || 'Test'} - ${test.ref_no || ''}
                        </h6>
                    </div>
                    <div class="card-body">
            `;
            
            if (test.is_parametered && test.parameters && test.parameters.length > 0) {
                // Parametered test
                html += `
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>{{ localize("global.parameter") ?? "Parameter" }}</th>
                                        <th>{{ localize("global.result") ?? "Result" }}</th>
                                        <th>{{ localize("global.unit") ?? "Unit" }}</th>
                                        <th>{{ localize("global.normal_range") ?? "Normal Range" }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;
                
                test.parameters.forEach(param => {
                    html += `
                        <tr>
                            <td><strong>${param.parameter_name || '—'}</strong></td>
                            <td>
                                <input type="text" 
                                       name="results[${registrationId}][${param.id}]" 
                                       value="${(param.result || '').replace(/"/g, '&quot;')}" 
                                       class="form-control form-control-sm"
                                       data-parameter-id="${param.id}"
                                       data-registration-id="${registrationId}">
                            </td>
                            <td><span class="text-muted">${param.unit || '—'}</span></td>
                            <td><span class="text-muted">${param.normal_range || '—'}</span></td>
                        </tr>
                    `;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                `;
            } else {
                // Text-based test
                html += `
                        <div class="mb-3">
                            <label class="form-label"><strong>{{ localize("global.result") ?? "Result" }}</strong></label>
                            <textarea 
                                name="text_results[${registrationId}]" 
                                class="form-control" 
                                rows="4"
                                data-registration-id="${registrationId}"
                                data-is-text-result="true">${(test.text_result || '').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</textarea>
                        </div>
                `;
            }
            
            html += `
                    </div>
                </div>
            `;
        });
        
        html += '</form>';
        $('#fillAllParamsContent').html(html);
    }
    
    // Handle Save All Parameters
    $('#saveAllParamsBtn').on('click', function() {
        const $btn = $(this);
        const originalHtml = $btn.html();
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ localize("global.saving") ?? "Saving..." }}');
        
        const formData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            registration_ids: currentRegistrationIds,
            patient_id: currentPatientId,
            results: {},
            text_results: {}
        };
        
        // Collect all form data - parametered tests
        $('#fillAllParamsForm input[type="text"]').each(function() {
            const $input = $(this);
            const registrationId = $input.data('registration-id');
            const parameterId = $input.data('parameter-id');
            const isTextResult = $input.data('is-text-result');
            
            // Skip if it's a text result (handled separately)
            if (isTextResult) {
                return;
            }
            
            if (parameterId && registrationId) {
                const value = $input.val();
                if (value && value.trim() !== '') {
                    if (!formData.results[registrationId]) {
                        formData.results[registrationId] = {};
                    }
                    formData.results[registrationId][parameterId] = value;
                }
            }
        });
        
        // Collect text results
        $('#fillAllParamsForm textarea').each(function() {
            const $textarea = $(this);
            const registrationId = $textarea.data('registration-id');
            const value = $textarea.val();
            
            if (registrationId && value && value.trim() !== '') {
                formData.text_results[registrationId] = value;
            }
        });
        
        $.ajax({
            url: '{{ route("laboratory.results.save-all-parameters") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Store group_id before closing modal
                    const groupId = response.group_id;
                    
                    toastr.success(response.message || '{{ localize("global.all_parameters_saved_successfully") ?? "All parameters saved successfully" }}');
                    
                    // Open print page in new tab if group_id is available
                    // Do this before closing modal to avoid popup blocker
                    if (groupId) {
                        const baseUrl = '{{ url("/") }}';
                        const printUrl = baseUrl + '/laboratory/reports/print-group/' + groupId;
                        const printWindow = window.open(printUrl, '_blank');
                        
                        if (!printWindow || printWindow.closed || typeof printWindow.closed == 'undefined') {
                            // Popup blocked - create a link and click it programmatically
                            const link = document.createElement('a');
                            link.href = printUrl;
                            link.target = '_blank';
                            link.rel = 'noopener noreferrer';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        }
                    }
                    
                    $('#fillAllParamsModal').modal('hide');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    toastr.error(response.message || '{{ localize("global.error_saving_parameters") ?? "Error saving parameters" }}');
                    $btn.prop('disabled', false).html(originalHtml);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || '{{ localize("global.error_saving_parameters") ?? "Error saving parameters" }}';
                toastr.error(message);
                $btn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Handle Attach Files Modal
    let currentTestResultId = null;
    let currentRegistrationId = null;
    
    $('#attachFilesModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        currentRegistrationId = button.data('registration-id');
        const refNo = button.data('ref-no');
        const testName = button.data('test-name');
        
        $('#attachFilesModalTitle').text('{{ localize("global.attach_files") ?? "Attach Files" }} - ' + testName);
        $('#attachFilesTestInfo').text(testName);
        $('#attachFilesRefNo').html('<code>' + refNo + '</code>');
        $('#attachFilesRegistrationId').val(currentRegistrationId);
        
        // Reset form
        $('#attachFilesForm')[0].reset();
        $('#attachFilesTestResultId').val('');
        
        // Show form and hide loading initially
        $('#attachFilesForm').show();
        $('#attachmentsList').html('<div class="text-center py-3 text-muted"><i class="bx bx-loader-alt bx-spin"></i> {{ localize("global.loading") ?? "Loading..." }}</div>');
        
        // Load test result ID and attachments
        loadTestResultAndAttachments(currentRegistrationId);
    });
    
    function loadTestResultAndAttachments(registrationId) {
        const attachmentsList = $('#attachmentsList');
        attachmentsList.html('<div class="text-center py-3 text-muted"><i class="bx bx-loader-alt bx-spin"></i> {{ localize("global.loading") ?? "Loading..." }}</div>');
        
        // First, get the test result for this registration
        $.ajax({
            url: '{{ url("/laboratory/results/load") }}/' + registrationId,
            method: 'GET',
            success: function(response) {
                if (response.success && response.data) {
                    // Find the text-based result (no lab_parameter_id)
                    const textResult = response.data.results?.find(r => !r.lab_parameter_id);
                    
                    if (textResult && textResult.id) {
                        currentTestResultId = textResult.id;
                        $('#attachFilesTestResultId').val(textResult.id);
                        
                        // Ensure form is visible
                        $('#attachFilesForm').show();
                        
                        // Load attachments
                        loadAttachments(textResult.id);
                    } else {
                        // No test result yet, but we can still allow uploads - they'll create the result
                        currentTestResultId = null;
                        $('#attachFilesTestResultId').val('0'); // Use 0 to indicate we need to create
                        attachmentsList.html('<div class="alert alert-info"><i class="bx bx-info-circle me-2"></i>{{ localize("global.test_result_not_found") ?? "Test result not found. Files will be attached when you upload them." }}</div>');
                        $('#attachFilesForm').show();
                    }
                } else {
                    attachmentsList.html('<div class="alert alert-warning">{{ localize("global.test_result_not_found") ?? "Test result not found. Please enter a result first." }}</div>');
                }
            },
            error: function() {
                attachmentsList.html('<div class="alert alert-danger">{{ localize("global.error_loading_data") ?? "Error loading data" }}</div>');
            }
        });
    }
    
    function loadAttachments(testResultId) {
        const attachmentsList = $('#attachmentsList');
        
        $.ajax({
            url: '{{ url("/laboratory/results") }}/' + testResultId + '/attachments',
            method: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    renderAttachments(response.attachments);
                } else {
                    attachmentsList.html('<div class="alert alert-warning">{{ localize("global.no_attachments_found") ?? "No attachments found" }}</div>');
                }
            },
            error: function() {
                attachmentsList.html('<div class="alert alert-danger">{{ localize("global.error_loading_attachments") ?? "Error loading attachments" }}</div>');
            }
        });
    }
    
    function renderAttachments(attachments) {
        const attachmentsList = $('#attachmentsList');
        
        if (attachments.length === 0) {
            attachmentsList.html('<div class="text-center py-3 text-muted"><i class="bx bx-file-blank me-1"></i>{{ localize("global.no_attachments") ?? "No attachments yet" }}</div>');
            return;
        }
        
        let html = '';
        attachments.forEach(function(attachment) {
            const fileIcon = getFileIcon(attachment.file_type);
            html += `
                <div class="list-group-item d-flex justify-content-between align-items-center" data-attachment-id="${attachment.id}">
                    <div class="d-flex align-items-center flex-grow-1">
                        <i class="${fileIcon} me-2 text-primary" style="font-size: 1.5rem;"></i>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${escapeHtml(attachment.file_name)}</div>
                            ${attachment.description ? '<small class="text-muted">' + escapeHtml(attachment.description) + '</small><br>' : ''}
                            <small class="text-muted">
                                <i class="bx bx-calendar me-1"></i>${attachment.created_at}
                                ${attachment.file_size ? ' | <i class="bx bx-data me-1"></i>' + attachment.file_size : ''}
                            </small>
                        </div>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <a href="${attachment.file_url}" target="_blank" class="btn btn-outline-primary" title="{{ localize("global.view") ?? "View" }}">
                            <i class="bx bx-show"></i>
                        </a>
                        <button type="button" class="btn btn-outline-danger delete-attachment-btn" data-attachment-id="${attachment.id}" title="{{ localize("global.delete") ?? "Delete" }}">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        
        attachmentsList.html(html);
    }
    
    function getFileIcon(mimeType) {
        if (!mimeType) return 'bx bx-file';
        
        if (mimeType.includes('pdf')) return 'bx bxs-file-pdf';
        if (mimeType.includes('excel') || mimeType.includes('spreadsheet') || mimeType.includes('xls')) return 'bx bxs-file';
        if (mimeType.includes('word') || mimeType.includes('document') || mimeType.includes('doc')) return 'bx bxs-file-doc';
        if (mimeType.includes('image')) return 'bx bxs-image';
        return 'bx bx-file';
    }
    
    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }
    
    // Handle file upload
    $('#attachFilesForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const uploadBtn = $('#uploadAttachmentsBtn');
        const originalHtml = uploadBtn.html();
        
        if (!formData.get('files[]')) {
            toastr.warning('{{ localize("global.please_select_files") ?? "Please select files to upload" }}');
            return;
        }
        
        // Add registration_id if test result doesn't exist yet
        if (!currentTestResultId || currentTestResultId == 0) {
            formData.append('registration_id', currentRegistrationId);
        }
        
        uploadBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>{{ localize("global.uploading") ?? "Uploading..." }}');
        
        $.ajax({
            url: '{{ url("/laboratory/results") }}/' + currentTestResultId + '/attachments',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message || '{{ localize("global.files_uploaded_successfully") ?? "Files uploaded successfully" }}');
                    $('#attachFilesForm')[0].reset();
                    loadAttachments(currentTestResultId);
                } else {
                    toastr.error(response.message || '{{ localize("global.error_uploading_files") ?? "Error uploading files" }}');
                }
                uploadBtn.prop('disabled', false).html(originalHtml);
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || '{{ localize("global.error_uploading_files") ?? "Error uploading files" }}';
                toastr.error(message);
                uploadBtn.prop('disabled', false).html(originalHtml);
            }
        });
    });
    
    // Handle delete attachment
    $(document).on('click', '.delete-attachment-btn', function() {
        const attachmentId = $(this).data('attachment-id');
        const $btn = $(this);
        
        if (!confirm('{{ localize("global.confirm_delete_file") ?? "Are you sure you want to delete this file?" }}')) {
            return;
        }
        
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '{{ url("/laboratory/results/attachments") }}/' + attachmentId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success') {
                    toastr.success(response.message || '{{ localize("global.file_deleted_successfully") ?? "File deleted successfully" }}');
                    if (currentTestResultId) {
                        loadAttachments(currentTestResultId);
                    }
                } else {
                    toastr.error(response.message || '{{ localize("global.error_deleting_file") ?? "Error deleting file" }}');
                    $btn.prop('disabled', false).html('<i class="bx bx-trash"></i>');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || '{{ localize("global.error_deleting_file") ?? "Error deleting file" }}';
                toastr.error(message);
                $btn.prop('disabled', false).html('<i class="bx bx-trash"></i>');
            }
        });
    });
});
</script>
@endsection
