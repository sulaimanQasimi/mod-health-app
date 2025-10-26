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
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">{{ localize('global.patient') }}</th>
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
                                    @foreach($patients as $patientId => $registrations)
                                        @php
                                            $patient = $registrations->first()->testable->patient ?? null;
                                        @endphp
                                        
                                        @if($patient)
                                            @foreach($registrations as $registration)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="d-flex align-items-center justify-content-center">
                                                        <div class="avatar avatar-sm me-2">
                                                            <div class="avatar-initial bg-primary text-white rounded-circle">
                                                                <i class="bx bx-user"></i>
                                                            </div>
                                                        </div>
                                                        <div class="text-start">
                                                            <div class="fw-semibold">{{ $patient->name }} {{ $patient->last_name }}</div>
                                                            <small class="text-muted">{{ $patient->father_name }} | {{ $patient->age }} | {{ $patient->phone }}</small>
                                                        </div>
                                                    </div>
                                                </td>
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
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
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
    
    .avatar {
        width: 2rem;
        height: 2rem;
    }
    
    .avatar-initial {
        font-size: 0.875rem;
    }
</style>
@endpush

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize tooltips
    $('[title]').tooltip();
    
    // Handle filter form submission with AJAX
    $('#patient-filter-form').on('submit', function(e) {
        e.preventDefault();
        
        showLoadingOverlay();
        
        var formData = $(this).serialize();
        var url = '{{ request()->url() }}?' + formData;
        
        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                $('#registrations-container').html($(response).find('#registrations-container').html());
                $('#registrations-container').addClass('fade-in');
                
                // Update URL
                if (history.pushState) {
                    history.pushState(null, null, url);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('{{ localize("global.error_loading_data") }}');
            },
            complete: function() {
                hideLoadingOverlay();
            }
        });
    });
    
    // Auto-submit on select change
    $('select[name="status"], select[name="priority"]').change(function() {
        $('#patient-filter-form').submit();
    });
    
    // Clear search functionality
    $(document).on('click', '#clearSearch', function() {
        $('input[name="search"]').val('');
        $('#patient-filter-form').submit();
    });
    
    // Initialize Persian datepicker
    $('.datepicker_dari').persianDatepicker({
        format: 'YYYY/MM/DD',
        observer: true,
    });
    
    
    // Handle form submissions with loading states
    $('form[action*="mark-in-progress"], form[action*="mark-completed"], form[action*="cancel"]').on('submit', function() {
        var btn = $(this).find('button[type="submit"]');
        var originalHtml = btn.html();
        
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm me-1"></span>' + btn.text());
        
        // Re-enable after 3 seconds as fallback
        setTimeout(function() {
            btn.prop('disabled', false);
            btn.html(originalHtml);
        }, 3000);
    });
    
    function showLoadingOverlay() {
        $('#loading-overlay').show();
    }
    
    function hideLoadingOverlay() {
        $('#loading-overlay').hide();
    }
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(event) {
        if (event.state) {
            location.reload();
        }
    });
    
    // Handle accept test functionality
    $(document).on('click', '.accept-test-btn', function() {
        var registrationId = $(this).data('registration-id');
        var btn = $(this);
        var originalHtml = btn.html();
        
        // Show loading state
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: '/laboratory/results/' + registrationId + '/accept',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    toastr.success(response.message);
                    
                    // Reload the page to show updated data
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(response.message);
                    btn.prop('disabled', false);
                    btn.html(originalHtml);
                }
            },
            error: function(xhr) {
                var message = 'Error accepting test';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                toastr.error(message);
                btn.prop('disabled', false);
                btn.html(originalHtml);
            }
        });
    });
});
</script>
@endsection
