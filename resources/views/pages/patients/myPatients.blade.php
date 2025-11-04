@extends('layouts.master')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="mb-0">
                            <i class="bx bx-user-circle me-2"></i>
                            {{ localize('global.user_performance_report') ?? 'User Performance Report' }}
                        </h4>
                        <p class="text-muted mb-0 mt-1">{{ localize('global.comprehensive_analytics_and_performance_metrics') }}</p>
                    </div>
                    @isset($results)
                    <div>
                        <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                            <i class="bx bx-printer me-1"></i> {{ localize('global.print') }}
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="exportToExcel()">
                            <i class="bx bx-export me-1"></i> {{ localize('global.export') }}
                        </button>
                    </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bx bx-filter-alt me-2"></i>
                        {{ localize('global.filter_options') }}
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('user-performance-report.fetch') }}" id="performanceForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="startDate" class="form-label">
                                    <i class="bx bx-calendar me-1"></i> {{ localize('global.start_date') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="startDate" id="startDate" 
                                       class="form-control datepicker_dari pdp-el" required 
                                       value="{{ old('startDate', request('startDate')) }}">
                            </div>
                            <div class="col-md-3">
                                <label for="endDate" class="form-label">
                                    <i class="bx bx-calendar-check me-1"></i> {{ localize('global.end_date') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="endDate" id="endDate" 
                                       class="form-control datepicker_dari pdp-el" required 
                                       value="{{ old('endDate', request('endDate')) }}">
                            </div>
                            <div class="col-md-4 border">
                                <label for="userId" class="form-label">
                                    <i class="bx bx-user me-1"></i> {{ localize('global.select_user') }}
                                </label>
                                <select name="userId" id="userId" class="form-select select2">
                                    <option value="">{{ localize('global.all_users') }}</option>
                                    @forelse($users as $user)
                                    <option value="{{ $user->id }}" 
                                            {{ old('userId', request('userId')) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                        @if($user->section_name || $user->department_name)
                                            - {{ $user->section_name ?? 'N/A' }} / {{ $user->department_name ?? 'N/A' }}
                                        @endif
                                    </option>
                                    @empty
                                    <option disabled>{{ localize('global.no_users_found') }}</option>
                                    @endforelse
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100" id="submitBtn">
                                    <i class="bx bx-search me-1"></i>
                                    <span class="btn-text">{{ localize('global.generate') }}</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                                </button>
                            </div>
                        </div>

                        @if ($errors->any())
                        <div class="alert alert-danger mt-3">
                            <i class="bx bx-error-circle me-2"></i>
                            <strong>{{ localize('global.please_fix_the_following_errors') }}:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    @isset($results)
    <div class="row g-4 mb-4" id="summaryCards">
        @php
            $totalAppointments = collect($results)->sum('Appointments');
            $totalPrescriptions = collect($results)->sum('Prescriptions');
            $totalLabTests = collect($results)->sum('LabTests');
            $totalAnesthesias = collect($results)->sum('Anesthesias');
            $grandTotal = collect($results)->sum('Total');
        @endphp
        
        <div class="col-sm-6 col-xl-3">
            <div class="card bg-label-primary">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>{{ localize('global.total_appointments') }}</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 badge badge-center bg-primary" style="font-size: xx-large;">{{ number_format($totalAppointments) }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-primary rounded p-2">
                            <i class="bx bx-calendar bx-lg"></i>
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
                            <span>{{ localize('global.total_prescriptions') }}</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 badge badge-center bg-success" style="font-size: xx-large;">{{ number_format($totalPrescriptions) }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-success rounded p-2">
                            <i class="bx bx-receipt bx-lg"></i>
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
                            <span>{{ localize('global.total_lab_tests') }}</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 badge badge-center bg-info" style="font-size: xx-large;">{{ number_format($totalLabTests) }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-info rounded p-2">
                            <i class="bx bx-test-tube bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card bg-label-warning">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span>{{ localize('global.total_anesthesias') }}</span>
                            <div class="d-flex align-items-end mt-2">
                                <h4 class="mb-0 me-2 badge badge-center bg-warning" style="font-size: xx-large;">{{ number_format($totalAnesthesias) }}</h4>
                            </div>
                        </div>
                        <span class="badge bg-warning rounded p-2">
                            <i class="bx bx-first-aid bx-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-bar-chart-alt-2 me-2"></i>
                        {{ localize('global.performance_distribution') }}
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-pie-chart-alt-2 me-2"></i>
                        {{ localize('global.activity_breakdown') }}
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-table me-2"></i>
                        {{ localize('global.detailed_performance_analytics') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <input type="text" id="tableSearch" class="form-control form-control-sm" 
                               placeholder="{{ localize('global.search_users') }}..." style="width: 200px;">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="resetTable()">
                            <i class="bx bx-refresh"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="performanceTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-column="0">
                                        <i class="bx bx-user me-1"></i> {{ localize('global.user') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="sortable text-center" data-column="1">
                                        <i class="bx bx-calendar me-1"></i> {{ localize('global.appointments') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="sortable text-center" data-column="2">
                                        <i class="bx bx-receipt me-1"></i> {{ localize('global.prescriptions') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="sortable text-center" data-column="3">
                                        <i class="bx bx-test-tube me-1"></i> {{ localize('global.lab_tests') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="sortable text-center" data-column="4">
                                        <i class="bx bx-first-aid me-1"></i> {{ localize('global.anesthesias') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="sortable text-center" data-column="5">
                                        <i class="bx bx-calculator me-1"></i> {{ localize('global.total') }}
                                        <i class="bx bx-sort float-end"></i>
                                    </th>
                                    <th class="text-center">{{ localize('global.percentage') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($results as $result)
                                @php
                                    $appointments = $result->Appointments ?? 0;
                                    $prescriptions = $result->Prescriptions ?? 0;
                                    $labTests = $result->LabTests ?? 0;
                                    $anesthesias = $result->Anesthesias ?? 0;
                                    $total = $result->Total ?? 0;
                                    $percentage = $grandTotal > 0 ? ($total / $grandTotal * 100) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-2">
                                                <span class="avatar-initial rounded bg-label-primary">
                                                    {{ substr($result->User ?? 'N/A', 0, 1) }}
                                                </span>
                                            </div>
                                            <strong>{{ $result->User ?? 'N/A' }}</strong>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-primary">{{ number_format($appointments) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-success">{{ number_format($prescriptions) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-info">{{ number_format($labTests) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-warning">{{ number_format($anesthesias) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <strong class="text-primary">{{ number_format($total) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center">
                                            <div class="progress me-2" style="width: 100px; height: 8px;">
                                                <div class="progress-bar" role="progressbar" 
                                                     style="width: {{ $percentage }}%" 
                                                     aria-valuenow="{{ $percentage }}" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ number_format($percentage, 1) }}%</small>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <i class="bx bx-inbox fs-1 text-muted d-block mb-2"></i>
                                        <p class="text-muted">{{ localize('global.no_performance_data_found') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if(count($results) > 0)
                            <tfoot class="table-light">
                                <tr>
                                    <th><strong>{{ localize('global.total') }}</strong></th>
                                    <th class="text-center">
                                        <strong class="text-primary">{{ number_format($totalAppointments) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-success">{{ number_format($totalPrescriptions) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-info">{{ number_format($totalLabTests) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-warning">{{ number_format($totalAnesthesias) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-primary">{{ number_format($grandTotal) }}</strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>100%</strong>
                                    </th>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endisset

    <!-- Users List (Collapsible) -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-link p-0 text-decoration-none w-100 text-start" 
                            type="button" data-bs-toggle="collapse" 
                            data-bs-target="#usersList" aria-expanded="false">
                        <h5 class="mb-0">
                            <i class="bx bx-chevron-down me-2"></i>
                            {{ localize('global.users_directory') }}
                        </h5>
                    </button>
                </div>
                <div class="collapse" id="usersList">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ localize('global.name') }}</th>
                                        <th>{{ localize('global.section') }}</th>
                                        <th>{{ localize('global.department') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2">
                                                    <span class="avatar-initial rounded bg-label-secondary">
                                                        {{ substr($user->name, 0, 1) }}
                                                    </span>
                                                </div>
                                                {{ $user->name }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $user->section_name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-primary">{{ $user->department_name ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <i class="bx bx-user-x fs-1 text-muted d-block mb-2"></i>
                                            <p class="text-muted mb-0">{{ localize('global.no_users_found') }}</p>
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
</div>
@endsection

@push('custom-css')
<style>
    .sortable {
        cursor: pointer;
        user-select: none;
    }
    .sortable:hover {
        background-color: #f8f9fa;
    }
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .border-start {
        border-left-width: 4px !important;
    }
    @media print {
        .card-header button, #tableSearch, .btn, .collapse {
            display: none !important;
        }
        .card {
            box-shadow: none;
            border: 1px solid #dee2e6;
        }
    }
</style>
@endpush

@push('custom-js')
<script src="{{ asset('assets/vendor/libs/chartjs/chartjs.js') }}"></script>
<script>
    // Initialize Select2
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap-5',
                placeholder: 'Select a user...',
                allowClear: true
            });
        }

        // Form submission with loading state
        $('#performanceForm').on('submit', function() {
            const btn = $('#submitBtn');
            btn.prop('disabled', true);
            btn.find('.btn-text').text('{{ localize('global.generating') }}...');
            btn.find('.spinner-border').removeClass('d-none');
        });

        // Date validation
        $('#endDate').on('change', function() {
            const startDate = $('#startDate').val();
            const endDate = $(this).val();
            
            if (startDate && endDate && endDate < startDate) {
                alert('{{ localize('global.end_date_must_be_after_start_date') }}');
                $(this).val(startDate);
            }
        });

        // Table search functionality
        $('#tableSearch').on('keyup', function() {
            const value = $(this).val().toLowerCase();
            $('#performanceTable tbody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Table sorting
        $('.sortable').on('click', function() {
            const table = $('#performanceTable');
            const column = $(this).data('column');
            const rows = table.find('tbody tr').toArray();
            const isAsc = $(this).hasClass('sort-asc');
            
            // Remove sort classes
            $('.sortable').removeClass('sort-asc sort-desc');
            
            // Sort rows
            rows.sort(function(a, b) {
                const aVal = $(a).find('td').eq(column).text().trim();
                const bVal = $(b).find('td').eq(column).text().trim();
                
                if (column === 0) {
                    return isAsc ? bVal.localeCompare(aVal) : aVal.localeCompare(bVal);
                } else {
                    const aNum = parseInt(aVal.replace(/,/g, '')) || 0;
                    const bNum = parseInt(bVal.replace(/,/g, '')) || 0;
                    return isAsc ? bNum - aNum : aNum - bNum;
                }
            });
            
            // Add sort class
            $(this).addClass(isAsc ? 'sort-desc' : 'sort-asc');
            
            // Reorder table
            table.find('tbody').empty().append(rows);
        });

        @isset($results)
        // Initialize charts
        initializeCharts();
        @endisset
    });

    function resetTable() {
        $('#tableSearch').val('');
        $('#performanceTable tbody tr').show();
        $('.sortable').removeClass('sort-asc sort-desc');
        const rows = $('#performanceTable tbody tr').toArray();
        $('#performanceTable tbody').empty().append(rows);
    }

    function exportToExcel() {
        const table = document.getElementById('performanceTable');
        let html = table.outerHTML;
        html = '<table>' + html.replace(/<tfoot>[\s\S]*?<\/tfoot>/gi, '') + '</table>';
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'user-performance-report-' + new Date().toISOString().split('T')[0] + '.xls';
        a.click();
        URL.revokeObjectURL(url);
    }

    @isset($results)
    function initializeCharts() {
        const results = @json($results);
        
        // Prepare data for performance chart
        const labels = results.map(r => r.User || 'N/A');
        const appointmentsData = results.map(r => r.Appointments || 0);
        const prescriptionsData = results.map(r => r.Prescriptions || 0);
        const labTestsData = results.map(r => r.LabTests || 0);
        const anesthesiasData = results.map(r => r.Anesthesias || 0);

        // Performance Chart (Bar Chart)
        const performanceCtx = document.getElementById('performanceChart');
        if (performanceCtx) {
            new Chart(performanceCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: '{{ localize('global.appointments') }}',
                            data: appointmentsData,
                            backgroundColor: 'rgba(105, 108, 255, 0.8)',
                            borderColor: 'rgba(105, 108, 255, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '{{ localize('global.prescriptions') }}',
                            data: prescriptionsData,
                            backgroundColor: 'rgba(40, 208, 148, 0.8)',
                            borderColor: 'rgba(40, 208, 148, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '{{ localize('global.lab_tests') }}',
                            data: labTestsData,
                            backgroundColor: 'rgba(3, 195, 236, 0.8)',
                            borderColor: 'rgba(3, 195, 236, 1)',
                            borderWidth: 1
                        },
                        {
                            label: '{{ localize('global.anesthesias') }}',
                            data: anesthesiasData,
                            backgroundColor: 'rgba(255, 193, 7, 0.8)',
                            borderColor: 'rgba(255, 193, 7, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                }
            });
        }

        // Activity Breakdown Chart (Pie Chart)
        @php
            $totalAppointments = collect($results)->sum('Appointments');
            $totalPrescriptions = collect($results)->sum('Prescriptions');
            $totalLabTests = collect($results)->sum('LabTests');
            $totalAnesthesias = collect($results)->sum('Anesthesias');
        @endphp
        
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'doughnut',
                data: {
                    labels: [
                        '{{ localize('global.appointments') }}',
                        '{{ localize('global.prescriptions') }}',
                        '{{ localize('global.lab_tests') }}',
                        '{{ localize('global.anesthesias') }}'
                    ],
                    datasets: [{
                        data: [
                            {{ $totalAppointments }},
                            {{ $totalPrescriptions }},
                            {{ $totalLabTests }},
                            {{ $totalAnesthesias }}
                        ],
                        backgroundColor: [
                            'rgba(105, 108, 255, 0.8)',
                            'rgba(40, 208, 148, 0.8)',
                            'rgba(3, 195, 236, 0.8)',
                            'rgba(255, 193, 7, 0.8)'
                        ],
                        borderColor: [
                            'rgba(105, 108, 255, 1)',
                            'rgba(40, 208, 148, 1)',
                            'rgba(3, 195, 236, 1)',
                            'rgba(255, 193, 7, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return label + ': ' + value.toLocaleString() + ' (' + percentage + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    @endisset
</script>
@endpush
