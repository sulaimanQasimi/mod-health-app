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
            <div class="card-header bg-none border-0 collapsed" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse"">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                        <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') }}</h6>
                    </div>
                </div>
            </div>
            <div class="collapse" id="filterCollapse">
                <div class="card-body">
                    <form method="GET" action="{{ route('laboratory.results.grouped') }}">
                        {{-- Combined Search and Filter Container --}}
                        <div class="row g-3">
                            {{-- Search Input --}}
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">
                                    <i class="bx bx-search me-1 text-primary"></i>{{ localize('global.search_patient') }}
                                </label>
                                <div class="input-group" onclick="document.getElementById('search').focus()" style="cursor: pointer;">
                                    <span class="input-group-text bg-primary text-white">
                                        <i class="bx bx-search"></i>
                                    </span>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="{{ localize('global.search_patient_placeholder') }}"
                                           autocomplete="off" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">
                                </div>
                                {{-- Search Dropdown --}}
                                <div class="dropdown-menu w-100" id="searchDropdown" style="max-height: 300px; overflow-y: auto;">
                                    <div class="dropdown-header">
                                        <i class="bx bx-history me-2"></i>{{ localize('global.recent_searches') ?: 'Recent Searches' }}
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <div class="dropdown-item-text">
                                        <small class="text-muted">{{ localize('global.start_typing_to_search') ?: 'Start typing to search for patients...' }}</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Date From --}}
                            <div class="col-md-2">
                                <label for="date_from" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_from') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control persian-datepicker" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}" placeholder="1403/01/01" autocomplete="off">
                                </div>
                            </div>

                            {{-- Date To --}}
                            <div class="col-md-2">
                                <label for="date_to" class="form-label fw-semibold">
                                    <i class="bx bx-calendar me-1 text-info"></i>{{ localize('global.date_to') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-calendar"></i>
                                    </span>
                                    <input type="text" class="form-control persian-datepicker" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}" placeholder="1403/01/01" autocomplete="off">
                                </div>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">
                                    <i class="bx bx-cog me-1 text-secondary"></i>{{ localize('global.actions') }}
                                </label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-search me-1"></i>{{ localize('global.filter') }}
                                    </button>
                                    <a href="{{ route('laboratory.results.grouped') }}" class="btn btn-outline-secondary">
                                        <i class="bx bx-refresh"></i>
                                    </a>
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
    
    /* Clickable header styles */
    .card-header[data-bs-toggle="collapse"] {
        transition: all 0.3s ease;
        user-select: none;
    }
    
    
    .card-header.collapsed {
        background-color: transparent;
    }
    
    
    .card-header .badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
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
    
    /* Search Dropdown Styles */
    .dropdown-menu {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        margin-top: 0.25rem;
    }
    
    .dropdown-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        font-weight: 600;
        color: #495057;
    }
    
    .dropdown-item-text {
        padding: 0.75rem 1rem;
        color: #6c757d;
    }
    
    
    .input-group:focus-within {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        border-radius: 0.5rem;
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
<script src="{{ asset('ShamsiCalender/js/persianDatepicker.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Persian date pickers
    $('.persian-datepicker').persianDatepicker({
        format: 'YYYY/MM/DD',
        observer: true,
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
    
    // Handle filter collapse icon rotation
    $('#filterCollapse').on('show.bs.collapse', function () {
        $('#filterToggleIcon').removeClass('bx-chevron-down').addClass('bx-chevron-up');
    });
    
    $('#filterCollapse').on('hide.bs.collapse', function () {
        $('#filterToggleIcon').removeClass('bx-chevron-up').addClass('bx-chevron-down');
    });
    
    // Update filter count
    function updateFilterCount() {
        var activeFilters = 0;
        if ($('#search').val()) activeFilters++;
        if ($('#date_from').val()) activeFilters++;
        if ($('#date_to').val()) activeFilters++;
        
        $('#filterCount').text(activeFilters);
        
        if (activeFilters > 0) {
            $('#filterCount').removeClass('bg-primary').addClass('bg-success');
        } else {
            $('#filterCount').removeClass('bg-success').addClass('bg-primary');
        }
    }
    
    // Update filter count on input changes
    $('#search, #date_from, #date_to').on('input change', function() {
        updateFilterCount();
    });
    
    // Initial filter count update
    updateFilterCount();
    
    // Handle search dropdown functionality
    $('#search').on('focus', function() {
        $('#searchDropdown').addClass('show');
    });
    
    $('#search').on('blur', function() {
        // Delay hiding to allow clicking on dropdown items
        setTimeout(function() {
            if (!$('#searchDropdown:hover').length && !$('#search:hover').length) {
                $('#searchDropdown').removeClass('show');
            }
        }, 200);
    });
    
    // Keep dropdown open when hovering over it
    $('#searchDropdown').on('mouseenter', function() {
        $(this).addClass('show');
    });
    
    $('#searchDropdown').on('mouseleave', function() {
        if (!$('#search:focus').length) {
            $(this).removeClass('show');
        }
    });
    
    // Handle search input changes
    $('#search').on('input', function() {
        var searchTerm = $(this).val();
        if (searchTerm.length > 0) {
            // Show dropdown with search suggestions
            $('#searchDropdown').addClass('show');
            // You can add AJAX call here to fetch search suggestions
        } else {
            $('#searchDropdown').removeClass('show');
        }
    });
    
});

// Change per page function
function changePerPage(perPage) {
    const url = new URL(window.location);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page'); // Reset to first page
    window.location.href = url.toString();
}
</script>
@endpush
