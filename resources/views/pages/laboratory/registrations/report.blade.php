@extends('layouts.master')
@section('title', localize('global.test_registration_report'))
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold mb-1">
                            <i class="bx bx-test-tube me-2"></i>
                            {{ localize('global.test_registration_report') }}
                        </h4>
                        <p class="text-muted mb-0">{{ localize('global.view_and_export_test_registration_statistics') ?? 'View and export test registration statistics by type and date range' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filters Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header border-bottom">
                <div class="d-flex align-items-center">
                    <i class="bx bx-search-alt-2 me-2 fs-5"></i>
                    <h5 class="mb-0">{{ localize('global.documents.search') }}</h5>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('laboratory.registrations.report') }}" id="search-form">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-calendar me-1"></i>
                                {{ localize('global.between_two_date') }}
                            </label>
                            <div class="input-group input-daterange">
                                <input autocomplete="off" type="text" name="from" 
                                    value="{{ old('from', request('from')) }}"
                                    placeholder="{{ localize('global.from') }}"
                                    class="form-control datepicker_dari" />
                                <span class="input-group-text bg-light">
                                    <i class="bx bx-right-arrow-alt"></i>
                                </span>
                                <input autocomplete="off" type="text" name="to" 
                                    value="{{ old('to', request('to')) }}"
                                    placeholder="{{ localize('global.to') }}"
                                    class="form-control datepicker_dari" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-category me-1"></i>
                                {{ localize('global.test_type') }}
                            </label>
                            <select class="form-select select2" name="test_type" id="test_type">
                                <option value="">{{ localize('global.all') }}</option>
                                @if(isset($labTypes))
                                    @foreach($labTypes as $labType)
                                        <option value="{{ $labType->id }}" {{ request('test_type') == $labType->id ? 'selected' : '' }}>
                                            {{ $labType->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">
                                <i class="bx bx-list-ul me-1"></i>
                                {{ localize('global.per_page') }}
                            </label>
                            <select class="form-select" name="per_page" id="per_page">
                                <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                <option value="100" {{ request('per_page', 15) == 100 ? 'selected' : '' }}>100</option>
                                <option value="all" {{ request('per_page', 15) == 'all' ? 'selected' : '' }}>{{ localize('global.all') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bx bx-search me-1"></i>
                                {{ localize('global.documents.search') }}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="reset-form-btn">
                                <i class="bx bx-refresh me-1"></i>
                                {{ localize('global.reset') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Results Card -->
        @if(isset($items) && $items->count() > 0)
            @php
                $totalCount = 0;
                if (is_a($items, 'Illuminate\Pagination\LengthAwarePaginator')) {
                    $totalCount = $items->sum('total_count');
                } else {
                    $totalCount = collect($items)->sum('total_count');
                }
            @endphp

            <!-- Statistics Cards -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-4">
                    <div class="card bg-label-primary">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.test_types') ?? 'Test Types' }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">
                                            {{ is_a($items, 'Illuminate\Pagination\LengthAwarePaginator') ? $items->total() : $items->count() }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-primary rounded p-2">
                                    <i class="bx bx-test-tube bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card bg-label-success">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.total_registrations') ?? 'Total Registrations' }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">
                                            {{ number_format($totalCount) }}
                                        </h4>
                                    </div>
                                </div>
                                <span class="badge bg-success rounded p-2">
                                    <i class="bx bx-list-check bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-4">
                    <div class="card bg-label-info">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between">
                                <div class="content-left">
                                    <span>{{ localize('global.date_range') ?? 'Date Range' }}</span>
                                    <div class="d-flex align-items-end mt-2">
                                        <h6 class="mb-0 me-2 fw-semibold">
                                            @if(request('from') && request('to'))
                                                {{ request('from') }} - {{ request('to') }}
                                            @else
                                                {{ localize('global.all_dates') ?? 'All Dates' }}
                                            @endif
                                        </h6>
                                    </div>
                                </div>
                                <span class="badge bg-info rounded p-2">
                                    <i class="bx bx-calendar-check bx-lg"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Table Card -->
            <div class="card shadow-sm">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-table me-2 fs-5"></i>
                            <h5 class="mb-0">{{ localize('global.report_results') ?? 'Report Results' }}</h5>
                        </div>
                        <form action="{{ route('laboratory.registrations.export-report') }}" method="POST" class="d-inline">
                            {{ csrf_field() }}
                            <input type="hidden" name="from" value="{{ request('from', '') }}">
                            <input type="hidden" name="to" value="{{ request('to', '') }}">
                            <input type="hidden" name="test_type" value="{{ request('test_type', '') }}">
                            <input type="hidden" name="per_page" value="{{ request('per_page', '') }}">
                            <button type="submit" name="type" value="excel" class="btn btn-sm btn-success me-2" id="export-excel-btn">
                                <i class="bx bx-file me-1"></i>Excel
                            </button>
                            <button type="submit" name="type" value="pdf" class="btn btn-sm btn-danger" id="export-pdf-btn">
                                <i class="bx bx-file-blank me-1"></i>PDF
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="print_excel_table">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 80px;">
                                        <i class="bx bx-hash me-1"></i>{{ localize('global.number') }}
                                    </th>
                                    <th>
                                        <i class="bx bx-category me-1"></i>{{ localize('global.test_type') }}
                                    </th>
                                    <th class="text-center" style="width: 150px;">
                                        <i class="bx bx-list-check me-1"></i>{{ localize('global.count') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $rowNumber = 1;
                                    if (is_a($items, 'Illuminate\Pagination\LengthAwarePaginator')) {
                                        $rowNumber = $items->firstItem() ?? 1;
                                    }
                                @endphp
                                @foreach ($items as $testType)
                                    <tr>
                                        <td class="text-center">
                                            <span class="badge bg-label-secondary">{{ $rowNumber }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-label-primary me-2">
                                                    <i class="bx bx-test-tube"></i>
                                                </div>
                                                <span class="fw-semibold">{{ $testType['lab_type_name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-label-primary fs-6">{{ number_format($testType['total_count']) }}</span>
                                        </td>
                                    </tr>
                                    @php $rowNumber++; @endphp
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator') && $items->hasPages())
                        <div class="card-footer border-top bg-light">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div class="text-muted small mb-2 mb-md-0">
                                    <i class="bx bx-info-circle me-1"></i>
                                    {{ localize('global.showing') }} {{ $items->firstItem() }} {{ localize('global.to') }} {{ $items->lastItem() }} 
                                    {{ localize('global.of') }} {{ $items->total() }} {{ localize('global.results') }}
                                </div>
                                <div>
                                    {{ $items->links() }}
                                </div>
                            </div>
                        </div>
                    @elseif(is_a($items, 'Illuminate\Pagination\LengthAwarePaginator'))
                        <div class="card-footer border-top bg-light">
                            <div class="text-muted small">
                                <i class="bx bx-info-circle me-1"></i>
                                {{ localize('global.showing') }} {{ $items->total() }} {{ localize('global.results') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @elseif(isset($items) && $items->count() == 0)
            <!-- Empty State -->
            <div class="card shadow-sm">
                <div class="card-body text-center py-5">
                    <div class="avatar avatar-xl bg-label-warning mb-3 mx-auto">
                        <i class="bx bx-search-alt-2 fs-1"></i>
                    </div>
                    <h5 class="mb-2">{{ localize('global.no_item_is_found') }}</h5>
                    <p class="text-muted mb-4">
                        {{ localize('global.no_results_found_for_selected_filters') ?? 'No test registrations found for the selected filters. Please adjust your search criteria and try again.' }}
                    </p>
                    <button type="button" class="btn btn-outline-primary" id="reset-form-btn">
                        <i class="bx bx-refresh me-1"></i>
                        {{ localize('global.reset_filters') ?? 'Reset Filters' }}
                    </button>
                </div>
            </div>
        @endif
    </div>
    <!-- / Content -->
</div>
@endsection

@push('custom-js')
<script>
    $(document).ready(function() {
        // Initialize or reinitialize Select2 for test_type dropdown with custom options
        function initTestTypeSelect2() {
            var $testType = $('#test_type');
            
            // Destroy existing Select2 instance if it exists
            if ($testType.hasClass('select2-hidden-accessible')) {
                try {
                    $testType.select2('destroy');
                    // Remove wrapper if exists
                    $testType.unwrap();
                } catch(e) {
                    console.log('Select2 destroy error:', e);
                }
            }
            
            // Initialize Select2 with custom options
            $testType.wrap('<div class="position-relative"></div>').select2({
                placeholder: '{{ localize("global.all") }}',
                allowClear: true,
                width: '100%',
                language: {
                    noResults: function() {
                        return '{{ localize("global.no_results_found") ?? "No results found" }}';
                    }
                },
                dropdownParent: $testType.parent()
            });
        }
        
        // Wait a bit for global scripts to load, then initialize
        setTimeout(initTestTypeSelect2, 200);

        // Auto-submit when per_page changes
        $('#per_page').on('change', function() {
            $('#search-form').submit();
        });

        // Auto-submit when test_type changes (works with Select2)
        $(document).on('change', '#test_type', function() {
            $('#search-form').submit();
        });

        // Handle reset button click
        $('#reset-form-btn').on('click', function(e) {
            e.preventDefault();
            
            // Reset all input fields
            $('#search-form input[type="text"]').val('');
            
            // Reset per_page to default
            $('#per_page').val('15');
            
            // Reset test_type to default (works with Select2)
            $('#test_type').val('').trigger('change');
            
            // Clear date pickers
            $('.datepicker_dari').val('');
            
            // Redirect to clean report URL (without query parameters)
            window.location.href = '{{ route("laboratory.registrations.report") }}';
        });

        // Add loading state to buttons
        $('#search-form').on('submit', function() {
            $(this).find('button[type="submit"]').prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>{{ localize("global.loading") ?? "Loading..." }}');
        });
    });
</script>
@endpush
@push('custom-css')
<style>
.sadira_date_range,
.wareda_date_range {
    display: none;
}

/* Statistics Cards Styling */
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

.bg-label-primary {
    background: var(--bs-primary-bg-subtle);
    border: 1px solid var(--bs-primary-border-subtle);
}

.bg-label-success {
    background: var(--bs-success-bg-subtle);
    border: 1px solid var(--bs-success-border-subtle);
}

.bg-label-info {
    background: var(--bs-info-bg-subtle);
    border: 1px solid var(--bs-info-border-subtle);
}

.bg-label-warning {
    background: var(--bs-warning-bg-subtle);
    border: 1px solid var(--bs-warning-border-subtle);
}

.bg-label-secondary {
    background-color: rgba(105, 122, 141, 0.1);
    color: #697a8d;
}

/* Avatar Styles (for empty state) */
.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
}

.avatar-xl {
    width: 5rem;
    height: 5rem;
}

/* Dark mode specific adjustments */
[data-bs-theme="dark"] .bg-label-primary {
    background: rgba(13, 110, 253, 0.1);
    border-color: rgba(13, 110, 253, 0.2);
}

[data-bs-theme="dark"] .bg-label-success {
    background: rgba(25, 135, 84, 0.1);
    border-color: rgba(25, 135, 84, 0.2);
}

[data-bs-theme="dark"] .bg-label-info {
    background: rgba(13, 202, 240, 0.1);
    border-color: rgba(13, 202, 240, 0.2);
}

[data-bs-theme="dark"] .bg-label-warning {
    background: rgba(255, 193, 7, 0.1);
    border-color: rgba(255, 193, 7, 0.2);
}

/* Statistics Card Content */
.card-body .content-left span {
    font-size: 0.875rem;
    color: #697a8d;
    font-weight: 500;
}

.card-body .badge.badge-center {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.5rem;
    padding: 0.5rem 0.75rem;
}

/* Table Enhancements */
.table-hover tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.05);
    transition: background-color 0.2s ease;
}

.table thead th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
    padding: 1rem;
}

.table tbody td {
    padding: 1rem;
    vertical-align: middle;
}

/* Badge Enhancements */
.badge {
    padding: 0.5rem 0.75rem;
    font-weight: 500;
}

/* Card Header Enhancements */
.card-header {
    padding: 1.25rem 1.5rem;
    font-weight: 600;
}


/* Form Enhancements */
.form-label {
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #696cff;
    box-shadow: 0 0 0 0.2rem rgba(105, 108, 255, 0.25);
}

/* Button Enhancements */
.btn {
    font-weight: 500;
    padding: 0.625rem 1.25rem;
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.btn-sm {
    padding: 0.5rem 1rem;
}

/* Input Group Enhancements */
.input-group-text {
    background-color: #f8f9fa;
    border-color: #d9dee3;
}

/* Statistics Card Hover Effect */
.card.bg-label-primary:hover,
.card.bg-label-success:hover,
.card.bg-label-info:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .card-header .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 1rem;
    }
    
    .table-responsive {
        font-size: 0.875rem;
    }
    
}

/* Print Styles */
@media print {
    .card-header,
    .btn,
    .card-footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .table {
        border: 1px solid #ddd !important;
    }
}
</style>
@endpush
