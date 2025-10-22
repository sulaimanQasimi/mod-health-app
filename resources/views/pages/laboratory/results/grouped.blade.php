@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Page Header --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-collection me-2"></i>
                            <h5 class="mb-0">{{ localize('global.grouped_test_results') }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advanced Search and Filters --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-light border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                        <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') }}</h6>
                    </div>
                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                        <i class="bx bx-chevron-down" id="filterToggleIcon"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('laboratory.results.grouped') }}">
                        {{-- Search Section --}}
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="bx bx-search"></i>
                                    </span>
                                    <input type="text" class="form-control form-control-lg" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="{{ localize('global.search_patient_placeholder') }}">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i>{{ localize('global.search') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Filter Controls --}}
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label for="status" class="form-label fw-semibold">
                                    <i class="bx bx-check-circle me-1 text-info"></i>{{ localize('global.status') }}
                                </label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">{{ localize('global.all_statuses') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        <i class="bx bx-time"></i> {{ localize('global.pending') }}
                                    </option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        <i class="bx bx-loader"></i> {{ localize('global.in_progress') }}
                                    </option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        <i class="bx bx-check"></i> {{ localize('global.completed') }}
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        <i class="bx bx-x"></i> {{ localize('global.cancelled') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="priority" class="form-label fw-semibold">
                                    <i class="bx bx-flag me-1 text-warning"></i>{{ localize('global.priority') }}
                                </label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">{{ localize('global.all_priorities') }}</option>
                                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>{{ localize('global.normal') }}</option>
                                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>{{ localize('global.urgent') }}</option>
                                    <option value="stat" {{ request('priority') == 'stat' ? 'selected' : '' }}>{{ localize('global.stat') }}</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="doctor" class="form-label fw-semibold">
                                    <i class="bx bx-user me-1 text-success"></i>{{ localize('global.doctor') }}
                                </label>
                                <select class="form-select" id="doctor" name="doctor">
                                    <option value="">{{ localize('global.all_doctors') }}</option>
                                    @php
                                        $doctors = \App\Models\User::whereHas('roles', function($q) {
                                            $q->where('name', 'doctor');
                                        })->get();
                                    @endphp
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}" {{ request('doctor') == $doctor->id ? 'selected' : '' }}>
                                            {{ $doctor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-cog me-1 text-secondary"></i>{{ localize('global.actions') }}
                                </label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary flex-fill">
                                        <i class="bx bx-search me-1"></i>{{ localize('global.filter') }}
                                    </button>
                                    <a href="{{ route('laboratory.results.grouped') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- Date Range Section --}}
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bx bx-calendar text-primary me-2"></i>
                                    <h6 class="mb-0 fw-semibold">{{ localize('global.date_range') }}</h6>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label fw-semibold">{{ localize('global.date_from') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control persian-datepicker" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}" placeholder="1403/01/01" autocomplete="off">
                                    <input type="hidden" id="date_from_gregorian" name="date_from_gregorian" value="{{ request('date_from_gregorian') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label fw-semibold">{{ localize('global.date_to') }}</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control persian-datepicker" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}" placeholder="1403/01/01" autocomplete="off">
                                    <input type="hidden" id="date_to_gregorian" name="date_to_gregorian" value="{{ request('date_to_gregorian') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">{{ localize('global.quick_actions') ?: 'Quick Actions' }}</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('today')">
                                        <i class="bx bx-calendar"></i> {{ localize('global.today') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('week')">
                                        <i class="bx bx-calendar-week"></i> {{ localize('global.this_week') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-info btn-sm" onclick="setDateRange('month')">
                                        <i class="bx bx-calendar-check"></i> {{ localize('global.this_month') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearAllFilters()">
                                        <i class="bx bx-x-circle"></i> {{ localize('global.clear_filters') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Active Filters Display --}}
        @if(request()->hasAny(['search', 'status', 'priority', 'doctor', 'date_from', 'date_to']))
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="mb-1">{{ localize('global.active_filters') }}:</h6>
                            <div class="d-flex flex-wrap gap-2">
                                @if(request('search'))
                                    <span class="badge bg-primary">{{ localize('global.search') }}: {{ request('search') }}</span>
                                @endif
                                @if(request('status'))
                                    <span class="badge bg-info">{{ localize('global.status') }}: {{ ucfirst(request('status')) }}</span>
                                @endif
                                @if(request('priority'))
                                    <span class="badge bg-warning">{{ localize('global.priority') }}: {{ ucfirst(request('priority')) }}</span>
                                @endif
                                @if(request('doctor'))
                                    @php
                                        $doctor = \App\Models\User::find(request('doctor'));
                                    @endphp
                                    <span class="badge bg-success">{{ localize('global.doctor') }}: {{ $doctor ? $doctor->name : 'Unknown' }}</span>
                                @endif
                                @if(request('date_from') || request('date_to'))
                                    <span class="badge bg-secondary">
                                        {{ localize('global.date_range') }}: 
                                        {{ request('date_from') ?: localize('global.start') }} - {{ request('date_to') ?: localize('global.end') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('laboratory.results.grouped') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bx bx-x"></i> {{ localize('global.clear_all') }}
                        </a>
                    </div>
                </div>
            </div>
        @endif


        {{-- Grouped Test Results Accordion --}}
        @if($groupedTests->count() > 0)
            <div class="accordion" id="groupedTestsAccordion">
                @foreach($groupedTests as $categoryId => $tests)
                    <div class="accordion-item mb-3">
                        <h2 class="accordion-header" id="heading{{ $categoryId }}">
                            <button class="accordion-button collapsed" type="button" 
                                    data-bs-toggle="collapse" 
                                    data-bs-target="#collapse{{ $categoryId }}" 
                                    aria-expanded="false" 
                                    aria-controls="collapse{{ $categoryId }}">
                                <div class="d-flex justify-content-between align-items-center w-100 me-3">
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-collection me-2"></i>
                                        <div>
                                            <h6 class="mb-0">
                                                {{ localize('global.test_group') }} #{{ $categoryId }}
                                            </h6>
                                            <small class="text-muted">
                                                {{ $tests->count() }} {{ localize('global.tests') }} | 
                                                @php
                                                    $firstTest = $tests->first();
                                                    $patient = $firstTest && $firstTest->testable ? $firstTest->testable->patient : null;
                                                @endphp
                                                {{ $patient ? $patient->name : '—' }} {{ $patient ? $patient->last_name : '' }}
                                            </small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('laboratory.reports.print-group', $categoryId) }}" 
                                           class="btn btn-success btn-sm" target="_blank"
                                           onclick="event.stopPropagation();">
                                            <i class="bx bx-printer me-1"></i>
                                            {{ localize('global.print_group') }}
                                        </a>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse{{ $categoryId }}" 
                             class="accordion-collapse collapse" 
                             aria-labelledby="heading{{ $categoryId }}" 
                             data-bs-parent="#groupedTestsAccordion">
                            <div class="accordion-body bg-none">
                                {{-- Patient Information --}}
                                @php
                                    $firstTest = $tests->first();
                                    $patient = $firstTest && $firstTest->testable ? $firstTest->testable->patient : null;
                                @endphp
                                @if($patient)
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.patient') }}:</strong> 
                                                        {{ $patient->name }} {{ $patient->last_name }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.phone') }}:</strong> 
                                                        {{ $patient->phone ?? '—' }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.age') }}:</strong> 
                                                        {{ $patient->age ?? '—' }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.registration_date') }}:</strong> 
                                                        {{ $firstTest->registration_date->format('Y-m-d H:i') }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Tests in Group --}}
                                 <div class="table-responsive">
                                     <table class="table table-hover">
                                         <thead>
                                            <tr>
                                                <th>{{ localize('global.test_name') }}</th>
                                                <th>{{ localize('global.reference_number') }}</th>
                                                <th>{{ localize('global.status') }}</th>
                                                <th>{{ localize('global.priority') }}</th>
                                                <th>{{ localize('global.doctor') }}</th>
                                                <th>{{ localize('global.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tests as $test)
                                                <tr>
                                                    <td>{{ $test->labTest->name ?? '—' }}</td>
                                                    <td>
                                                        <span class="badge bg-warning">{{ $test->ref_no }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($test->status == 'completed') bg-success
                                                            @elseif($test->status == 'in_progress') bg-warning
                                                            @elseif($test->status == 'cancelled') bg-danger
                                                            @else bg-secondary
                                                            @endif">
                                                            {{ ucfirst($test->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="badge 
                                                            @if($test->priority == 'stat') bg-danger
                                                            @elseif($test->priority == 'urgent') bg-warning
                                                            @else bg-primary
                                                            @endif">
                                                            {{ ucfirst($test->priority) }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $test->doctor->name ?? '—' }}</td>
                                                    <td>
                                                        <div class="btn-group" role="group">
                                                            @if($test->status == 'completed')
                                                                <a href="{{ route('laboratory.reports.print', $test->ref_no) }}" 
                                                                   class="btn btn-outline-info btn-sm" target="_blank">
                                                                    <i class="bx bx-printer"></i>
                                                                </a>
                                                            @else
                                                                <a href="{{ route('laboratory.results.show', $test->id) }}" 
                                                                   class="btn btn-outline-primary btn-sm">
                                                                    <i class="bx bx-edit"></i>
                                                                </a>
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
                @endforeach
            </div>
            
            {{-- Pagination Controls --}}
            @if(isset($groupedTestsPaginated) && $groupedTestsPaginated->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="d-flex align-items-center">
                        <span class="text-muted me-3">
                            {{ localize('global.showing') ?: 'Showing' }} {{ $groupedTestsPaginated->firstItem() ?? 0 }} 
                            {{ localize('global.to') ?: 'to' }} {{ $groupedTestsPaginated->lastItem() ?? 0 }} 
                            {{ localize('global.of') ?: 'of' }} {{ $groupedTestsPaginated->total() }} 
                            {{ localize('global.results') ?: 'results' }}
                        </span>
                        
                        {{-- Per Page Selector --}}
                        <div class="d-flex align-items-center">
                            <label for="per_page" class="form-label me-2 mb-0">{{ localize('global.per_page') ?: 'Per page' }}:</label>
                            <select class="form-select form-select-sm" id="per_page" name="per_page" style="width: auto;" onchange="changePerPage(this.value)">
                                <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Pagination Links --}}
                    <nav aria-label="{{ localize('global.pagination') ?: 'Pagination' }}">
                        {{ $groupedTestsPaginated->links() }}
                    </nav>
                </div>
            @endif
        @else
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="bx bx-collection text-muted" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">{{ localize('global.no_grouped_tests_found') }}</h5>
                    <p class="text-muted">{{ localize('global.no_grouped_tests_description') }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@push('custom-css')
<!-- Persian Date Picker CSS -->
<link rel="stylesheet" href="{{ asset('assets/persian date2/css/persianDatepicker-default.css') }}">
<style>
    .table th,
    .table td {
        text-align: right;
    }
    .card-header h6 {
        font-weight: 600;
    }
    .alert-info {
        background-color: #e7f3ff;
        border-color: #b3d9ff;
    }
    
    /* Enhanced Filter Section Styles */
    .card.shadow-sm {
        border: none;
        border-radius: 0.75rem;
    }
    
    .card-header.bg-light {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
        border-radius: 0.75rem 0.75rem 0 0;
    }
    
    .form-control-lg {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control-lg:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .input-group-text {
        border-radius: 0.5rem 0 0 0.5rem;
        border: 2px solid #e9ecef;
        border-right: none;
    }
    
    .form-control {
        border-radius: 0 0.5rem 0.5rem 0;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .form-select {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }
    
    .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    .btn {
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .form-label {
        color: #495057;
        margin-bottom: 0.5rem;
    }
    
    .form-label i {
        font-size: 1rem;
    }
    
    /* Accordion Custom Styles */
    .accordion-button {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        font-weight: 500;
    }
    
    .accordion-button:not(.collapsed) {
        background-color: #e7f3ff;
        border-color: #b3d9ff;
        color: #0c63e4;
    }
    
    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .accordion-item {
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
    }
    
    .accordion-body {
        background-color: transparent;
    }
    
    /* Print button styling in accordion header */
    .accordion-button .btn {
        z-index: 10;
        position: relative;
    }
    
    /* Persian Date Picker Styles */
    .persian-datepicker {
        direction: rtl;
        text-align: right;
    }
    
    .pwt-datepicker-input {
        direction: rtl !important;
        text-align: right !important;
    }
    
    /* Collapse Animation */
    .collapse {
        transition: all 0.3s ease;
    }
    
    /* Filter toggle button animation */
    #filterToggleIcon {
        transition: transform 0.3s ease;
    }
    
    .btn-outline-primary:hover #filterToggleIcon {
        transform: scale(1.1);
    }
    
    /* Pagination Styles */
    .pagination {
        margin-bottom: 0;
    }
    
    .pagination .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #0d6efd;
        transition: all 0.3s ease;
    }
    
    .pagination .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        transform: translateY(-1px);
    }
    
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: white;
    }
    
    .pagination .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
    }
    
    /* Per page selector */
    .form-select-sm {
        border-radius: 0.375rem;
        border: 1px solid #ced4da;
        transition: all 0.3s ease;
    }
    
    .form-select-sm:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    }
    
    /* Pagination info */
    .text-muted {
        font-size: 0.875rem;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .d-flex.flex-wrap {
            flex-direction: column;
        }
        
        .d-flex.flex-wrap .btn {
            margin-bottom: 0.5rem;
        }
        
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 1rem;
        }
        
        .d-flex.justify-content-between > div {
            width: 100%;
            justify-content: center;
        }
    }
</style>
@endpush

@push('custom-js')
<!-- Persian Date Picker JS -->
<script src="{{ asset('assets/persian date2/js/persianDatepicker.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Persian date pickers
    $("#date_from").persianDatepicker({
        format: 'YYYY/MM/DD',
        altField: '#date_from_gregorian',
        altFormat: 'YYYY-MM-DD',
        observer: true,
        timePicker: {
            enabled: false
        },
        autoClose: true,
        initialValue: false,
        initialValueType: 'persian'
    });
    
    $("#date_to").persianDatepicker({
        format: 'YYYY/MM/DD',
        altField: '#date_to_gregorian',
        altFormat: 'YYYY-MM-DD',
        observer: true,
        timePicker: {
            enabled: false
        },
        autoClose: true,
        initialValue: false,
        initialValueType: 'persian'
    });
    
    // Handle filter collapse icon rotation
    $('#filterCollapse').on('show.bs.collapse', function () {
        $('#filterToggleIcon').removeClass('bx-chevron-down').addClass('bx-chevron-up');
    });
    
    $('#filterCollapse').on('hide.bs.collapse', function () {
        $('#filterToggleIcon').removeClass('bx-chevron-up').addClass('bx-chevron-down');
    });
    
    // Convert existing Gregorian dates to Persian if they exist
    @if(request('date_from_gregorian'))
        var gregorianFrom = '{{ request('date_from_gregorian') }}';
        if (gregorianFrom) {
            var persianFrom = convertGregorianToPersian(gregorianFrom);
            $('#date_from').val(persianFrom);
        }
    @endif
    
    @if(request('date_to_gregorian'))
        var gregorianTo = '{{ request('date_to_gregorian') }}';
        if (gregorianTo) {
            var persianTo = convertGregorianToPersian(gregorianTo);
            $('#date_to').val(persianTo);
        }
    @endif
});

// Function to convert Gregorian date to Persian date
function convertGregorianToPersian(gregorianDate) {
    var date = new Date(gregorianDate);
    var year = date.getFullYear();
    var month = date.getMonth() + 1;
    var day = date.getDate();
    
    // Persian calendar constants
    var persianEpoch = 1948320.5;
    var gregorianEpoch = 1721425.5;
    
    // Convert to Julian Day Number
    var jd = gregorianToJulianDay(year, month, day);
    
    // Convert to Persian date
    var persianDate = julianDayToPersian(jd);
    
    // Format as Persian date (YYYY/MM/DD)
    return persianDate.year + '/' + 
           persianDate.month.toString().padStart(2, '0') + '/' + 
           persianDate.day.toString().padStart(2, '0');
}

// Convert Gregorian date to Julian Day Number
function gregorianToJulianDay(year, month, day) {
    var jd = gregorianEpoch - 1;
    
    jd += 365 * (year - 1);
    jd += Math.floor((year - 1) / 4);
    jd -= Math.floor((year - 1) / 100);
    jd += Math.floor((year - 1) / 400);
    jd += Math.floor((367 * month - 362) / 12);
    
    if (month > 2) {
        jd -= isLeapYear(year) ? 1 : 2;
    }
    
    jd += day;
    return jd;
}

// Convert Julian Day Number to Persian date
function julianDayToPersian(jd) {
    jd = Math.floor(jd) + 0.5;
    
    var depoch = jd - 1948320.5;
    var cycle = Math.floor(depoch / 1029983);
    var cyear = depoch % 1029983;
    
    if (cyear < 0) {
        cyear += 1029983;
    }
    
    var ycycle;
    if (cyear == 1029982) {
        ycycle = 2820;
    } else {
        var aux1 = Math.floor(cyear / 366);
        var aux2 = cyear % 366;
        ycycle = Math.floor(((2134 * aux1) + (2816 * aux2) + 2815) / 1028522) + aux1 + 1;
    }
    
    var year = ycycle + (2820 * cycle) + 474;
    if (year <= 0) {
        year--;
    }
    
    var yday = (jd - persianToJulianDay(year, 1, 1)) + 1;
    var month = (yday <= 186) ? Math.ceil(yday / 31) : Math.ceil((yday - 6) / 30);
    var day = (jd - persianToJulianDay(year, month, 1)) + 1;
    
    return { year: year, month: month, day: day };
}

// Convert Persian date to Julian Day Number
function persianToJulianDay(year, month, day) {
    var epbase = year - (year >= 0 ? 474 : 473);
    var epyear = 474 + (epbase % 2820);
    
    var mdays = (month <= 7) ? ((month - 1) * 31) : ((month - 1) * 30 + 6);
    
    return day + mdays + Math.floor(((epyear * 682) - 110) / 2816) + (epyear - 1) * 365 + Math.floor(epbase / 2820) * 1029983 + (1948320.5 - 1);
}

// Check if year is leap year
function isLeapYear(year) {
    return (year % 4 == 0 && year % 100 != 0) || (year % 400 == 0);
}

// Set date range functions
function setDateRange(range) {
    console.log('Setting date range:', range);
    var today = new Date();
    var persianToday = convertGregorianToPersian(today.toISOString().split('T')[0]);
    
    switch(range) {
        case 'today':
            $('#date_from').val(persianToday);
            $('#date_to').val(persianToday);
            // Set Gregorian dates
            var gregorianToday = today.toISOString().split('T')[0];
            $('#date_from_gregorian').val(gregorianToday);
            $('#date_to_gregorian').val(gregorianToday);
            break;
            
        case 'week':
            var weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            var weekEnd = new Date(weekStart);
            weekEnd.setDate(weekStart.getDate() + 6);
            
            var persianWeekStart = convertGregorianToPersian(weekStart.toISOString().split('T')[0]);
            var persianWeekEnd = convertGregorianToPersian(weekEnd.toISOString().split('T')[0]);
            
            $('#date_from').val(persianWeekStart);
            $('#date_to').val(persianWeekEnd);
            $('#date_from_gregorian').val(weekStart.toISOString().split('T')[0]);
            $('#date_to_gregorian').val(weekEnd.toISOString().split('T')[0]);
            break;
            
        case 'month':
            var monthStart = new Date(today.getFullYear(), today.getMonth(), 1);
            var monthEnd = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            var persianMonthStart = convertGregorianToPersian(monthStart.toISOString().split('T')[0]);
            var persianMonthEnd = convertGregorianToPersian(monthEnd.toISOString().split('T')[0]);
            
            $('#date_from').val(persianMonthStart);
            $('#date_to').val(persianMonthEnd);
            $('#date_from_gregorian').val(monthStart.toISOString().split('T')[0]);
            $('#date_to_gregorian').val(monthEnd.toISOString().split('T')[0]);
            break;
    }
    
    // Auto-submit the form after setting dates
    setTimeout(function() {
        $('form').submit();
    }, 100);
}

// Clear all filters function
function clearAllFilters() {
    console.log('Clearing all filters');
    $('#search').val('');
    $('#status').val('');
    $('#priority').val('');
    $('#doctor').val('');
    $('#date_from').val('');
    $('#date_to').val('');
    $('#date_from_gregorian').val('');
    $('#date_to_gregorian').val('');
    
    // Auto-submit the form after clearing filters
    setTimeout(function() {
        $('form').submit();
    }, 100);
}

// Change per page function
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
@endpush
