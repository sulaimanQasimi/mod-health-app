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

        {{-- Search and Filters --}}
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('laboratory.results.grouped') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="search" class="form-label">{{ localize('global.search_patient') }}</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               value="{{ request('search') }}" placeholder="{{ localize('global.patient_name') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="status" class="form-label">{{ localize('global.status') }}</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">{{ localize('global.all_statuses') }}</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>{{ localize('global.pending') }}</option>
                            <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>{{ localize('global.in_progress') }}</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>{{ localize('global.completed') }}</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>{{ localize('global.cancelled') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="priority" class="form-label">{{ localize('global.priority') }}</label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="">{{ localize('global.all_priorities') }}</option>
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
                    <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bx bx-search"></i>
                            </button>
                            <a href="{{ route('laboratory.results.grouped') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bx bx-x"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

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
                                                {{ $tests->first()->testable->patient->name ?? '—' }} {{ $tests->first()->testable->patient->last_name ?? '' }}
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
                                @if($tests->first()->testable->patient)
                                    <div class="row mb-3">
                                        <div class="col-12">
                                            <div class="alert alert-info">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.patient') }}:</strong> 
                                                        {{ $tests->first()->testable->patient->name }} {{ $tests->first()->testable->patient->last_name }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.phone') }}:</strong> 
                                                        {{ $tests->first()->testable->patient->phone ?? '—' }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.age') }}:</strong> 
                                                        {{ $tests->first()->testable->patient->age ?? '—' }}
                                                    </div>
                                                    <div class="col-md-3">
                                                        <strong>{{ localize('global.registration_date') }}:</strong> 
                                                        {{ $tests->first()->registration_date->format('Y-m-d H:i') }}
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
</style>
@endpush

@push('custom-js')
<script src="{{ asset('ShamsiCalender/js/persianDatepicker.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize Persian datepicker
    $('.datepicker_dari').persianDatepicker({
        format: 'YYYY/MM/DD',
        observer: true,
    });
});
</script>
@endpush
