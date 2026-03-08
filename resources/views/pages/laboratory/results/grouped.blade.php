@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">

        {{-- Statistics Cards --}}
        <div class="row g-4 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card bg-label-warning">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ localize('global.pending_tests') }}</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2 badge badge-center bg-warning" style="font-size: xx-large;">
                                        {{ $groupedTests->flatten()->where('status', 'pending')->count() }}
                                    </h4>
                                </div>
                            </div>
                            <span class="badge bg-warning rounded p-2">
                                <i class="bx bx-hourglass bx-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card bg-label-success">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ localize('global.completed_tests') }}</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">
                                        {{ $groupedTests->flatten()->where('status', 'completed')->count() }}
                                    </h4>
                                </div>
                            </div>
                            <span class="badge bg-success rounded p-2">
                                <i class="bx bx-check-double bx-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card bg-label-info">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ localize('global.in_progress_tests') }}</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">
                                        {{ $groupedTests->flatten()->where('status', 'in_progress')->count() }}
                                    </h4>
                                </div>
                            </div>
                            <span class="badge bg-info rounded p-2">
                                <i class="bx bx-time-five bx-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card bg-label-primary">
                    <div class="card-body">
                        <div class="d-flex align-items-start justify-content-between">
                            <div class="content-left">
                                <span>{{ localize('global.total_tests') }}</span>
                                <div class="d-flex align-items-end mt-2">
                                    <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                        {{ $groupedTests->flatten()->count() }}
                                    </h4>
                                </div>
                            </div>
                            <span class="badge bg-primary rounded p-2">
                                <i class="bx bx-clipboard bx-lg"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Advanced Search and Filters --}}
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-none border-0">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-filter-alt text-primary me-2" style="font-size: 1.2rem;"></i>
                        <h6 class="mb-0 fw-semibold">{{ localize('global.advanced_filters') }}</h6>
                    </div>
                </div>
            </div>
            <div class="card-body">
                    <form method="GET" action="{{ route('laboratory.results.grouped') }}">
                        {{-- Combined Search and Filter Container --}}
                        <div class="row g-3">
                            {{-- Search Input --}}
                            <div class="col-md-3">
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

                            {{-- Patient ID --}}
                            <div class="col-md-2">
                                <label for="patient_id" class="form-label fw-semibold">
                                    <i class="bx bx-user me-1 text-info"></i>{{ localize('global.patient_id') }}
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bx bx-user"></i>
                                    </span>
                                    <input type="text" class="form-control" id="patient_id" name="patient_id" 
                                           value="{{ request('patient_id') }}" placeholder="{{ localize('global.search_by_patient_id') }}">
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

        {{-- Active Filters Display --}}
        @if(request()->hasAny(['search', 'status', 'priority', 'doctor', 'date_from', 'date_to', 'patient_id']))
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
                                @if(request('patient_id'))
                                    <span class="badge bg-info">{{ localize('global.patient_id') }}: {{ request('patient_id') }}</span>
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
                                           class="btn btn-success btn-sm print-group-link" target="_blank"
                                           data-print-url="{{ route('laboratory.reports.print-group', $categoryId) }}">
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
                            <div class="accordion-body">
                                {{-- Tests in Group - Horizontal Card Layout --}}
                                <div class="d-flex flex-column gap-3">
                                    @foreach($tests as $test)
                                        <div class="test-card card">
                                            <div class="card-body">
                                                <div class="row align-items-center">
                                                    <div class="col-md-3">
                                                        <h6 class="mb-1">{{ $test->labType->name ?? '—' }}</h6>
                                                        <small class="text-muted">{{ localize('global.reference_number') }}: {{ $test->ref_no }}</small>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="badge 
                                                            @if($test->status == 'completed') bg-success
                                                            @elseif($test->status == 'in_progress') bg-warning
                                                            @elseif($test->status == 'cancelled') bg-danger
                                                            @else bg-secondary
                                                            @endif">
                                                            {{ ucfirst($test->status) }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <span class="priority-badge 
                                                            @if($test->priority == 'stat') priority-stat
                                                            @elseif($test->priority == 'urgent') priority-urgent
                                                            @else priority-normal
                                                            @endif">
                                                            {{ ucfirst($test->priority) }}
                                                        </span>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <small class="text-muted">{{ localize('global.doctor') }}:</small>
                                                        <div>{{ $test->doctor->name ?? '—' }}</div>
                                                    </div>
                                                    <div class="col-md-2 text-end">
                                                        <a href="{{ route('laboratory.reports.print', $test->ref_no) }}" 
                                                           class="btn btn-success btn-sm" target="_blank" title="{{ localize('global.print_report') }}">
                                                            <i class="bx bx-printer me-1"></i>
                                                            {{ localize('global.print') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            {{-- Simple Pagination with Per Page --}}
            @if(isset($groupedTestsPaginated) && $groupedTestsPaginated->hasPages())
                <div class="d-flex justify-content-between align-items-center mt-4">
                    {{-- Per Page Selector --}}
                    <div class="d-flex align-items-center">
                        <label for="per_page" class="form-label me-2 mb-0">
                            {{ localize('global.per_page') ?: 'Per page' }}:
                        </label>
                        <select class="form-select form-select-sm" id="per_page" name="per_page" style="width: auto;" onchange="changePerPage(this.value)">
                            <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                            <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    
                    {{-- Pagination Navigation --}}
                    <nav aria-label="{{ localize('global.pagination') ?: 'Pagination' }}">
                        <ul class="pagination pagination-simple mb-0">
                            {{-- Previous Page --}}
                            @if($groupedTestsPaginated->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bx bx-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <a class="page-link" href="{{ $groupedTestsPaginated->previousPageUrl() }}">
                                        <i class="bx bx-chevron-left"></i>
                                    </a>
                                </li>
                            @endif
                            
                            {{-- Page Numbers --}}
                            @php
                                $currentPage = $groupedTestsPaginated->currentPage();
                                $lastPage = $groupedTestsPaginated->lastPage();
                                $startPage = max(1, $currentPage - 1);
                                $endPage = min($lastPage, $currentPage + 1);
                            @endphp
                            
                            {{-- Show first page if not in range --}}
                            @if($startPage > 1)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $groupedTestsPaginated->url(1) }}">1</a>
                                </li>
                                @if($startPage > 2)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                            @endif
                            
                            {{-- Page numbers in range --}}
                            @for($i = $startPage; $i <= $endPage; $i++)
                                <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                    @if($i == $currentPage)
                                        <span class="page-link">{{ $i }}</span>
                                    @else
                                        <a class="page-link" href="{{ $groupedTestsPaginated->url($i) }}">{{ $i }}</a>
                                    @endif
                                </li>
                            @endfor
                            
                            {{-- Show last page if not in range --}}
                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                @endif
                                <li class="page-item">
                                    <a class="page-link" href="{{ $groupedTestsPaginated->url($lastPage) }}">{{ $lastPage }}</a>
                                </li>
                            @endif
                            
                            {{-- Next Page --}}
                            @if($groupedTestsPaginated->hasMorePages())
                                <li class="page-item">
                                    <a class="page-link" href="{{ $groupedTestsPaginated->nextPageUrl() }}">
                                        <i class="bx bx-chevron-right"></i>
                                    </a>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bx bx-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
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
    @font-face {
        font-family: 'ModFont';
        src: url('{{ asset("assets/fonts/mod_font.ttf") }}') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'ModFont', 'Arial', sans-serif;
        font-size: 12px;
        line-height: 1.4;
        color: #333;
        margin: 0;
        padding: 20px;
        background: white;
        direction: rtl;
        text-align: right;
    }



    /* Status and Priority Badges */
    .status-badge, .priority-badge {
        padding: 4px 8px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .status-completed {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .status-in-progress {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .status-cancelled {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .status-pending {
        background-color: #e2e3e5;
        color: #383d41;
        border: 1px solid #d6d8db;
    }

    .priority-stat {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .priority-urgent {
        background-color: #fff3cd;
        color: #856404;
        border: 1px solid #ffeaa7;
    }

    .priority-normal {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }

    /* Test Card Styles */
    .test-card {
        border: 1px solid #dee2e6;
        border-radius: 0.5rem;
        transition: all 0.2s ease-in-out;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    .test-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }

    .test-card .card-body {
        padding: 1rem;
    }

    .test-card .badge {
        font-size: 0.75rem;
        font-weight: 500;
    }

    .test-card h6 {
        font-weight: 600;
        color: #212529;
    }

    .test-card .row {
        min-height: 60px;
    }

    /* Accordion Styles */
    .accordion-button {
        background-color: #f8f9fa;
        border: 1px solid #000;
        font-weight: 500;
        color: #000;
    }

    .accordion-button:not(.collapsed) {
        background-color: #e7f3ff;
        border-color: #000;
        color: #000;
    }

    .accordion-button:focus {
        box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.25);
    }

    .accordion-item {
        border: 1px solid #000;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
    }

    .accordion-body {
        background-color: transparent;
    }

    /* Print button inside accordion */
    .print-group-link {
        position: relative;
        z-index: 10;
        pointer-events: auto;
        cursor: pointer;
        text-decoration: none;
    }

    .accordion-button .print-group-link {
        margin: 0;
    }

    /* Statistics Cards */
    .card {
        border: 1px solid #000;
    }

    .card-header {
        background-color: #000;
        color: white;
        border-bottom: 1px solid #000;
    }

    /* Filter Section */
    .card.shadow-sm {
        border: 1px solid #000;
        border-radius: 0.75rem;
    }

    .card-header {
        background-color: #f8f9fa;
        color: #000;
    }

    .form-control, .form-select {
        border: 1px solid #000;
        border-radius: 0.375rem;
    }

    .form-control:focus, .form-select:focus {
        border-color: #000;
        box-shadow: 0 0 0 0.2rem rgba(0, 0, 0, 0.25);
    }

    .btn {
        border: 1px solid #000;
        border-radius: 0.375rem;
        font-weight: 500;
    }

    .btn-primary {
        background-color: #000;
        border-color: #000;
        color: white;
    }

    .btn-primary:hover {
        background-color: #333;
        border-color: #333;
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

    /* Pagination Styles */
    .pagination-simple {
        margin-bottom: 0;
        gap: 0.25rem;
    }

    .pagination-simple .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
        border: 1px solid #000;
        color: #000;
        background-color: #fff;
        transition: all 0.2s ease;
        min-width: 38px;
        text-align: center;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
    }

    .pagination-simple .page-link:hover {
        background-color: #f8f9fa;
        border-color: #000;
        color: #000;
    }

    .pagination-simple .page-item.active .page-link {
        background-color: #000;
        border-color: #000;
        color: white;
    }

    .pagination-simple .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        cursor: not-allowed;
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

        .pagination-simple {
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-simple .page-link {
            min-width: 35px;
            padding: 0.375rem 0.5rem;
            font-size: 0.875rem;
        }
    }

    @media (max-width: 576px) {

        .pagination-simple .page-link {
            min-width: 30px;
            padding: 0.25rem 0.375rem;
            font-size: 0.75rem;
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
    
    // Handle print group button clicks - prevent accordion toggle
    $(document).on('click', '.print-group-link', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var url = $(this).attr('data-print-url') || $(this).attr('href');
        if (url) {
            window.open(url, '_blank');
        }
        return false;
    });
    
    // Also handle mousedown to prevent accordion toggle
    $(document).on('mousedown', '.print-group-link', function(e) {
        e.stopPropagation();
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
