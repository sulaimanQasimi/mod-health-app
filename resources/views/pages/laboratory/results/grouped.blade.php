@extends('layouts.master')

@section('content')
<div class="content-wrapper">
    <div class="container-xxl flex-grow-1 container-p-y">
        {{-- Professional Header --}}
        <div class="report-header mb-4">
            <div class="header-grid">
                <!-- Left Logo -->
                <div class="logo-container logo-left">
                    <img src="{{ asset('images/logos/لوگو قومنداني.JPG') }}" alt="Left Logo" class="logo-image">
                </div>

                <!-- Center Text Column (Arabic) -->
                <div class="text-column text-column-center">
                    <h2>امارت اسلامی افغانستان</h2>
                    <h4>وزارت دفاع ملی</h4>
                    <h4>ستـــــــــــــردرستیــــــــــــز</h4>
                    <h4>قوماندانیت صحیه</h4>
                    <h4>قوماندانی اکادمی علوم طبی</h4>
                    <h4 class="dep-name">{{ auth()->user()->department?->name ?? '—' }}</h4>
                </div>

                <!-- Right Logo -->
                <div class="logo-container logo-right">
                    <img src="{{ asset('images/logos/لوگوی جدید وزارت دفاع ملی.png') }}" alt="Right Logo" class="logo-image">
                </div>
            </div>
            
            <!-- Report Title -->
            <div class="report-title">
                <h1>{{ localize('global.grouped_test_results') }}</h1>
                <h2>{{ localize('global.laboratory_system') }}</h2>
            </div>
        </div>

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
                                    <div class="patient-info-section mb-3">
                                        <h3>{{ localize('global.patient_information') }}</h3>
                                        <table class="patient-details">
                                            <tr>
                                                <th>{{ localize('global.name') }}</th>
                                                <td>{{ $patient->name }} {{ $patient->last_name }}</td>
                                                <th>{{ localize('global.father_name') }}</th>
                                                <td>{{ $patient->father_name ?? '—' }}</td>
                                                <th>{{ localize('global.age') }}</th>
                                                <td>{{ $patient->age ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>{{ localize('global.phone') }}</th>
                                                <td>{{ $patient->phone ?? '—' }}</td>
                                                <th>{{ localize('global.gender') }}</th>
                                                <td>{{ $patient->gender ?? '—' }}</td>
                                                <th>{{ localize('global.registration_date') }}</th>
                                                <td>{{ \Hekmatinasser\Verta\Verta::instance($firstTest->registration_date)->format('Y/n/j H:i') }}</td>
                                            </tr>
                                            @if($patient->id_number)
                                            <tr>
                                                <th>{{ localize('global.id_number') }}</th>
                                                <td>{{ $patient->id_number }}</td>
                                                @if($patient->date_of_birth)
                                                <th>{{ localize('global.date_of_birth') }}</th>
                                                <td>{{ \Verta($patient->date_of_birth)->formatJalaliDate() }}</td>
                                                @endif
                                                @if($patient->email)
                                                <th>{{ localize('global.email') }}</th>
                                                <td>{{ $patient->email }}</td>
                                                @endif
                                            </tr>
                                            @endif
                                        </table>
                                    </div>
                                @endif

                                {{-- Tests in Group --}}
                                <div class="test-section">
                                    <div class="test-details">
                                        <table class="test-meta">
                                            <tr>
                                                <th>{{ localize('global.test_group') }}</th>
                                                <td>{{ $categoryId }}</td>
                                                <th>{{ localize('global.total_tests') }}</th>
                                                <td>{{ $tests->count() }}</td>
                                                <th>{{ localize('global.completed_tests') }}</th>
                                                <td>{{ $tests->where('status', 'completed')->count() }}</td>
                                            </tr>
                                        </table>
                                        
                                        <div class="tests-table-container">
                                            <table class="parameters-table">
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
                                                            <td>{{ $test->labType->name ?? '—' }}</td>
                                                            <td class="result-value">{{ $test->ref_no }}</td>
                                                            <td>
                                                                <span class="status-badge 
                                                                    @if($test->status == 'completed') status-completed
                                                                    @elseif($test->status == 'in_progress') status-in-progress
                                                                    @elseif($test->status == 'cancelled') status-cancelled
                                                                    @else status-pending
                                                                    @endif">
                                                                    {{ ucfirst($test->status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="priority-badge 
                                                                    @if($test->priority == 'stat') priority-stat
                                                                    @elseif($test->priority == 'urgent') priority-urgent
                                                                    @else priority-normal
                                                                    @endif">
                                                                    {{ ucfirst($test->priority) }}
                                                                </span>
                                                            </td>
                                                            <td>{{ $test->doctor->name ?? '—' }}</td>
                                                            <td>
                                                                <div class="action-buttons">
                                                                    @if($test->status == 'completed')
                                                                        <a href="{{ route('laboratory.reports.print', $test->ref_no) }}" 
                                                                           class="btn-print" target="_blank" title="{{ localize('global.print_report') }}">
                                                                            <i class="bx bx-printer"></i>
                                                                        </a>
                                                                    @else
                                                                        <a href="{{ route('laboratory.results.show', $test->id) }}" 
                                                                           class="btn-edit" title="{{ localize('global.edit_test') }}">
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

    /* Report Header Styles */
    .report-header {
        margin-bottom: 30px;
        border-bottom: 2px solid #000;
        padding-bottom: 20px;
    }

    .header-grid {
        display: grid;
        grid-template-columns: 120px 1fr 120px;
        gap: 20px;
        align-items: center;
        min-height: 120px;
    }

    .logo-container {
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100px;
        height: 100px;
        position: relative;
    }

    .logo-image {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .text-column {
        padding: 10px;
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        text-align: center;
    }

    .text-column h2 {
        color: #000;
        margin: 0 0 10px 0;
        font-size: 14px;
        font-weight: bold;
        text-align: center;
        padding-bottom: 5px;
    }

    .text-column h4 {
        color: #333;
        margin: 2px 0;
        font-size: 11px;
        line-height: 1.3;
        text-align: center;
    }

    .dep-name {
        background-color: #000;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
    }

    .report-title {
        text-align: center;
        margin-top: 20px;
        padding-top: 15px;
        border-top: 1px solid #ddd;
    }

    .report-title h1 {
        color: #000;
        margin: 0;
        font-size: 24px;
    }

    .report-title h2 {
        color: #333;
        margin: 5px 0 0 0;
        font-size: 16px;
        font-weight: normal;
    }

    /* Patient Information Styles */
    .patient-info-section {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid #000;
    }

    .patient-info-section h3 {
        margin: 0 0 10px 0;
        color: #000;
        font-size: 16px;
    }

    .patient-details {
        width: 100%;
        border-collapse: collapse;
    }

    .patient-details th,
    .patient-details td {
        border: 1px solid #000;
        padding: 2px;
        text-align: right;
    }

    .patient-details th {
        font-weight: bold;
        color: #000;
        white-space: nowrap;
    }

    .patient-details td {
        width: auto;
    }

    /* Test Section Styles */
    .test-section {
        margin-bottom: 30px;
        page-break-inside: avoid;
        margin-top: 20px;
    }

    .test-details {}

    .test-meta {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .test-meta th,
    .test-meta td {
        border: 1px solid #000;
        text-align: center;
        padding: 2px;
    }

    .test-meta th {
        font-weight: bold;
        color: #000;
    }

    .parameters-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        display: table;
    }

    .parameters-table thead {
        display: table-header-group;
    }

    .parameters-table tbody {
        display: table-row-group;
    }

    .parameters-table tr {
        display: table-row;
    }

    .parameters-table th,
    .parameters-table td {
        border: 1px solid #000;
        padding: 8px;
        text-align: right;
        display: table-cell;
        vertical-align: middle;
    }

    .parameters-table th {
        background: #f0f0f0;
        font-weight: bold;
        color: #000;
        width: 25%;
    }

    .parameters-table td {
        width: 25%;
    }

    .parameters-table tr:nth-child(even) {
        background: #f5f5f5;
    }

    .result-value {
        font-weight: bold;
        color: #000;
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

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 5px;
    }

    .btn-print, .btn-edit {
        padding: 6px 10px;
        border: 1px solid #000;
        background: #fff;
        color: #000;
        text-decoration: none;
        border-radius: 3px;
        font-size: 12px;
        transition: all 0.3s ease;
    }

    .btn-print:hover, .btn-edit:hover {
        background: #000;
        color: #fff;
        text-decoration: none;
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

    .card-header[data-bs-toggle="collapse"] {
        transition: all 0.3s ease;
        user-select: none;
        background-color: #f8f9fa;
        color: #000;
    }

    .card-header.collapsed {
        background-color: #f8f9fa;
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
        .header-grid {
            grid-template-columns: 80px 1fr 80px;
            gap: 10px;
        }

        .logo-container {
            width: 80px;
            height: 80px;
        }

        .text-column h2 {
            font-size: 12px;
        }

        .text-column h4 {
            font-size: 10px;
        }

        .report-title h1 {
            font-size: 20px;
        }

        .report-title h2 {
            font-size: 14px;
        }

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
        .header-grid {
            grid-template-columns: 60px 1fr 60px;
            gap: 5px;
        }

        .logo-container {
            width: 60px;
            height: 60px;
        }

        .text-column h2 {
            font-size: 10px;
        }

        .text-column h4 {
            font-size: 9px;
        }

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
