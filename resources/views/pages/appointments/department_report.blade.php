@extends('layouts.master')
@section('title', localize('global.department_report'))
@section('content')
<!-- Content wrapper -->
<div class="content-wrapper">
    <!-- Content -->
    <div class="container-xxl flex-grow-1 container-p-y">
        <!-- Basic Bootstrap Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">{{ localize('global.department_report') }}</h5>
                    <small class="text-muted">
                        <i class="bx bx-info-circle me-1"></i>
                        {{ localize('global.filter_appointments_by_department_and_date') }}
                    </small>
                </div>
                <button type="button" class="btn btn-primary no-print" onclick="window.print()">
                    <i class="bx bx-printer me-1"></i>
                    {{ localize('global.print') }}
                </button>
            </div>
            <div class="card-body">
                <!-- Filter Form -->
                <form id="departmentReportForm" method="GET" action="{{ route('appointments.department-report') }}" class="mb-3 no-print">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="department_id" class="form-label">{{ localize('global.department') }} <span class="text-danger">*</span></label>
                            <select class="form-control select2" name="department_id" id="department_id" required>
                                <option value="">{{ localize('global.select_department') }}</option>
                                @foreach($departments ?? [] as $department)
                                    <option value="{{ $department->id }}" {{ old('department_id', request('department_id')) == $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="date_from" class="form-label">{{ localize('global.from') }}</label>
                            <input type="text" 
                                   name="date_from" 
                                   id="date_from"
                                   class="form-control datepicker_dari" 
                                   value="{{ old('date_from', request('date_from')) }}"
                                   placeholder="{{ localize('global.from') }}" />
                        </div>
                        <div class="col-md-4">
                            <label for="date_to" class="form-label">{{ localize('global.to') }}</label>
                            <input type="text" 
                                   name="date_to" 
                                   id="date_to"
                                   class="form-control datepicker_dari" 
                                   value="{{ old('date_to', request('date_to')) }}"
                                   placeholder="{{ localize('global.to') }}" />
                        </div>
                    </div>
                    <div class="row g-3 mt-2">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-search me-1"></i>
                                {{ localize('global.search') }}
                            </button>
                            <a href="{{ route('appointments.department-report') }}" class="btn btn-secondary">
                                <i class="bx bx-refresh me-1"></i>
                                {{ localize('global.reset') }}
                            </a>
                        </div>
                    </div>
                </form>
                
                <!-- Results Table -->
                <div id="department-report-table-wrapper">
                    @include('pages.appointments.department_report_table', ['appointments' => $appointments])
                </div>
            </div>
        </div>
        <!--/ Basic Bootstrap Table -->
    </div>
    <!-- / Content -->
</div>
@endsection

@push('custom-css')
<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        /* Hide sidebar */
        .layout-menu {
            display: none !important;
        }
        
        /* Hide navbar */
        .layout-navbar {
            display: none !important;
        }
        
        /* Hide layout overlay */
        .layout-overlay {
            display: none !important;
        }
        
        /* Hide drag target */
        .drag-target {
            display: none !important;
        }
        
        body {
            -webkit-print-color-adjust: economy;
            print-color-adjust: economy;
            margin: 0;
            padding: 10px;
            font-size: 12px;
        }
        
        @page {
            margin: 0.5cm;
            size: A4;
        }
        
        .layout-wrapper {
            margin-left: 0 !important;
        }
        
        .layout-container {
            margin-left: 0 !important;
        }
        
        .layout-page {
            margin-left: 0 !important;
            padding-top: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }
        
        .container-xxl {
            padding: 0 !important;
            margin: 0 !important;
            max-width: 100% !important;
        }
        
        .card {
            border: none;
            box-shadow: none;
            margin: 0;
            width: 100% !important;
        }
        
        .card-header {
            border-bottom: 2px solid #000;
            margin-bottom: 10px;
            padding: 10px;
        }
        
        .card-body {
            padding: 10px !important;
        }
        
        #department-report-table-wrapper {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .table-responsive {
            width: 100%;
            overflow: visible;
        }
        
        table {
            page-break-inside: auto;
            width: 100% !important;
            max-width: 100% !important;
            table-layout: auto;
            border-collapse: collapse;
            margin: 0;
        }
        
        table thead th {
            background-color: transparent !important;
            background: none !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            padding: 8px 4px !important;
            font-size: 11px !important;
            font-weight: bold;
        }
        
        table tbody td {
            background-color: transparent !important;
            background: none !important;
            color: #000 !important;
            border: 1px solid #000 !important;
            padding: 6px 4px !important;
            font-size: 10px !important;
        }
        
        table tbody tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        thead {
            display: table-header-group;
        }
        
        tbody {
            display: table-row-group;
        }
        
        .card-body {
            padding: 10px !important;
        }
        
        .table-bordered {
            border: 1px solid #000 !important;
        }
        
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #000 !important;
        }
    }
    
    #department-report-table-wrapper table {
        text-align: right;
    }
    
    #department-report-table-wrapper table thead th {
        text-align: right;
        background-color: #f8f9fa;
        font-weight: bold;
    }
    
    /* Dark mode styles for table header */
    body[data-theme="dark"] #department-report-table-wrapper table thead th,
    html[data-theme="dark"] #department-report-table-wrapper table thead th,
    .dark-style #department-report-table-wrapper table thead th {
        background-color: #2b2c40 !important;
        color: #a3a4cc !important;
        border-color: #3b3d5e !important;
    }
    
    #department-report-table-wrapper table tbody td {
        text-align: right;
        vertical-align: middle;
    }
</style>
@endpush

@push('custom-js')
<script>
    $(document).ready(function() {
        // Initialize Select2 for department dropdown
        $('#department_id').select2({
            placeholder: '{{ localize("global.select_department") }}',
            allowClear: true,
            width: '100%'
        });
        
        // Initialize Persian datepicker
        $('.datepicker_dari').persianDatepicker({
            format: 'YYYY/MM/DD',
            observer: true,
        });
        
        // Handle form submission via AJAX
        $('#departmentReportForm').on('submit', function(e) {
            e.preventDefault();
            
            const form = $(this);
            const formData = form.serialize();
            const actionUrl = form.attr('action');
            const url = actionUrl + (formData ? '?' + formData : '');
            
            // Show loading state
            $('#department-report-table-wrapper').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">{{ localize("global.loading") }}...</p></div>');
            
            // Update URL without page reload
            if (history.pushState) {
                history.pushState(null, null, url);
            }
            
            // Load results via AJAX
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response && response.html) {
                        $('#department-report-table-wrapper').html(response.html);
                    } else {
                        console.error('Invalid response format:', response);
                        $('#department-report-table-wrapper').html('<div class="alert alert-danger">{{ localize("global.error_loading_data") }}</div>');
                    }
                },
                error: function(xhr) {
                    console.error('Error loading report:', xhr);
                    let errorMessage = '{{ localize("global.error_loading_data") }}';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    
                    $('#department-report-table-wrapper').html(
                        '<div class="alert alert-danger">' + errorMessage + '</div>'
                    );
                    
                    if (typeof toastr !== 'undefined') {
                        toastr.error(errorMessage);
                    }
                }
            });
        });
    });
</script>
@endpush
