@extends('layouts.master')

@section('content')
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        <i class="bx bx-health me-2 text-info"></i>
                        {{ localize('global.physiotherapy_procedures') }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">{{ localize('global.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ localize('global.physiotherapy_procedures') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="searchForm" class="row g-3">
                            <div class="col-md-2">
                                <label for="search" class="form-label">{{ localize('global.search') }}</label>
                                <input type="text" class="form-control" id="search" name="search"
                                    placeholder="{{ localize('global.search_patient_name') }}"
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">{{ localize('global.status') }}</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="">{{ localize('global.all_statuses') }}</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>
                                        {{ localize('global.pending') }}
                                    </option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        {{ localize('global.in_progress') }}
                                    </option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        {{ localize('global.completed') }}
                                    </option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        {{ localize('global.cancelled') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="physiotherapy_type_id"
                                    class="form-label">{{ localize('global.physiotherapy_type') }}</label>
                                <select class="form-control" id="physiotherapy_type_id" name="physiotherapy_type_id">
                                    <option value="">{{ localize('global.all_types') }}</option>
                                    @foreach($physiotherapyTypes as $type)
                                        <option value="{{ $type->id }}" {{ request('physiotherapy_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="physiotherapist_id"
                                    class="form-label">{{ localize('global.physiotherapist') }}</label>
                                <select class="form-control" id="physiotherapist_id" name="physiotherapist_id">
                                    <option value="">{{ localize('global.all_physiotherapists') }}</option>
                                    @foreach($physiotherapists as $physio)
                                        <option value="{{ $physio->id }}" {{ request('physiotherapist_id') == $physio->id ? 'selected' : '' }}>
                                            {{ $physio->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="start_date" class="form-label">{{ localize('global.start_date') }}</label>
                                <input type="text" class="form-control datepicker_dari pdp-el persian-date" id="start_date" name="start_date"
                                    >
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">{{ localize('global.end_date') }}</label>
                                <input type="text" class="form-control datepicker_dari pdp-el persian-date" id="end_date" name="end_date"
                                    >
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search me-1"></i>
                                    {{ localize('global.search') }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                    <i class="bx bx-refresh me-1"></i>
                                    {{ localize('global.reset') }}
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="exportData()">
                                    <i class="bx bx-download me-1"></i>
                                    {{ localize('global.export') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->total() }}</h4>
                                <p class="mb-0">{{ localize('global.total_procedures') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-health bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'pending')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.pending') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-time bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'in_progress')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.in_progress') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-play-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $physiotherapyProcedures->where('status', 'completed')->count() }}</h4>
                                <p class="mb-0">{{ localize('global.completed') }}</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bx bx-check-circle bx-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

<<<<<<< HEAD
        <!-- Procedures Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-none text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-list-ul me-2 text-primary"></i>
                            {{ localize('global.procedures_list') }}
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('physiotherapy-procedures.my-procedures') }}" class="btn btn-outline-primary">
                                <i class="bx bx-user me-1"></i>
                                {{ localize('global.my_procedures') }}
                            </a>
                            <a href="{{ route('physiotherapy-reports.index') }}" class="btn btn-outline-info">
                                <i class="bx bx-chart me-1"></i>
                                {{ localize('global.reports') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="proceduresTable">
                                <thead class="table-bg-none">
=======
    <!-- Procedures Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-none text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bx bx-list-ul me-2 text-primary"></i>
                        {{ localize('global.procedures_list') }}
                    </h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                            <i class="bx bx-refresh me-1"></i>
                            {{ localize('global.refresh') }}
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="proceduresTable">
                            <thead class="table-bg-none">
                                <tr>
                                    <th>{{ localize('global.number') }}</th>
                                    <th>{{ localize('global.patient_name') }}</th>
                                    <th>{{ localize('global.physiotherapy_type') }}</th>
                                    <th>{{ localize('global.physiotherapist') }}</th>
                                    <th>{{ localize('global.type') }}</th>
                                    <th>{{ localize('global.duration') }}</th>
                                    <th>{{ localize('global.progress') }}</th>
                                    <th>{{ localize('global.status') }}</th>
                                    <th>{{ localize('global.start_date') }}</th>
                                    <th>{{ localize('global.reviews') }}</th>
                                    <th>{{ localize('global.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($physiotherapyProcedures as $procedure)
>>>>>>> e159b00d4a4c0e26d33d7f5997c972543a5a9732
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.physiotherapy_type') }}</th>
                                        <th>{{ localize('global.physiotherapist') }}</th>
                                        <th>{{ localize('global.type') }}</th>
                                        <th>{{ localize('global.duration') }}</th>
                                        <th>{{ localize('global.progress') }}</th>
                                        <th>{{ localize('global.status') }}</th>
                                        <th>{{ localize('global.start_date') }}</th>
                                        <th>{{ localize('global.reviews') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($physiotherapyProcedures as $procedure)
                                        <tr>
                                            <td>
                                                <span class="badge bg-info rounded-pill">{{ $loop->iteration }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $procedure->appointment->patient->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $procedure->appointment->patient->phone ?? 'N/A' }}</small>
                                            </td>
                                            <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ $procedure->physiotherapist->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>{{ $procedure->type }}</td>
                                            <td>{{ $procedure->duration }} {{ localize('global.minutes') }}</td>
                                            <td>
                                                @php
                                                    $percentage = $procedure->days_count > 0 ? ($procedure->counter / $procedure->days_count) * 100 : 0;
                                                @endphp
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar bg-info" role="progressbar"
                                                        style="width: {{ $percentage }}%">
                                                        {{ $procedure->counter }}/{{ $procedure->days_count }}
                                                    </div>
                                                </div>
<<<<<<< HEAD
                                                <small class="text-muted">{{ round($percentage, 1) }}%</small>
                                            </td>
                                            <td>
                                                @php
                                                    $statusColors = [
                                                        'pending' => 'secondary',
                                                        'in_progress' => 'warning',
                                                        'completed' => 'success',
                                                        'cancelled' => 'danger'
                                                    ];
                                                    $color = $statusColors[$procedure->status] ?? 'secondary';
                                                @endphp
                                                <span class="badge bg-{{ $color }}">
                                                    {{ localize('global.physiotherapy_procedures_' . $procedure->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $procedure->start_date ? verta($procedure->start_date)->format('Y-m-d') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $procedure->reviews->count() }} {{ localize('global.reviews') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button type="button" class="btn btn-outline-info btn-sm"
                                                        onclick="viewProcedure({{ $procedure->id }})"
                                                        title="{{ localize('global.view') }}">
                                                        <i class="bx bx-show"></i>
=======
                                            </div>
                                            <small class="text-muted">{{ round($percentage, 1) }}%</small>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger'
                                                ];
                                                $color = $statusColors[$procedure->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ localize('global.physiotherapy_procedures_' . $procedure->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $procedure->start_date ? $procedure->start_date->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $procedure->reviews->count() }} {{ localize('global.reviews') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-outline-info btn-sm"
                                                    onclick="viewProcedure({{ $procedure->id }})"
                                                    title="{{ localize('global.view') }}">
                                                    <i class="bx bx-show"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success btn-sm"
                                                    onclick="addReview({{ $procedure->id }})"
                                                    title="{{ localize('global.add_review') }}">
                                                    <i class="bx bx-plus"></i>
                                                </button>
                                                @if($procedure->status !== 'completed' && $procedure->status !== 'cancelled')
                                                    <button type="button" class="btn btn-outline-warning btn-sm"
                                                        onclick="updateProgress({{ $procedure->id }})"
                                                        title="{{ localize('global.update_progress') }}">
                                                        <i class="bx bx-edit"></i>
>>>>>>> e159b00d4a4c0e26d33d7f5997c972543a5a9732
                                                    </button>
                                                    <button type="button" class="btn btn-outline-success btn-sm"
                                                        onclick="addReview({{ $procedure->id }})"
                                                        title="{{ localize('global.add_review') }}">
                                                        <i class="bx bx-plus"></i>
                                                    </button>
                                                    @if($procedure->status !== 'completed' && $procedure->status !== 'cancelled')
                                                        <button type="button" class="btn btn-outline-warning btn-sm"
                                                            onclick="updateProgress({{ $procedure->id }})"
                                                            title="{{ localize('global.update_progress') }}">
                                                            <i class="bx bx-edit"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                                        onclick="viewReviews({{ $procedure->id }})"
                                                        title="{{ localize('global.view_reviews') }}">
                                                        <i class="bx bx-message-square-dots"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center text-muted py-4">
                                                <i class="bx bx-inbox bx-lg mb-3"></i>
                                                <p class="mb-0">{{ localize('global.no_procedures_found') }}</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($physiotherapyProcedures->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $physiotherapyProcedures->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        @include('pages.physiotherapy.procedures.partials.modals')
    </div>
@endsection

@push('custom-css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.css') }}" />
@endpush

@push('custom-js')
    <script src="{{ asset('assets/vendor/libs/sweetalert2/sweetalert2.js') }}"></script>
@endpush

@section('scripts')
    @parent
    <script>
(function () {
    'use strict';

    // Translation strings
    const translations = {
        'patient_name': "{{ localize('global.patient_name') }}",
        'physiotherapy_type': "{{ localize('modules.physiotherapy.type') }}",
        'physiotherapist': "{{ localize('modules.physiotherapy.physiotherapist') }}",
        'type': "{{ localize('global.type') }}",
        'duration': "{{ localize('global.duration') }}",
        'minutes': "{{ localize('global.minutes') }}",
        'progress': "{{ localize('global.progress') }}",
        'status': "{{ localize('global.status') }}",
        'start_date': "{{ localize('global.start_date') }}",
        'description': "{{ localize('global.description') }}",
        'notes': "{{ localize('global.notes') }}",
        'n_a': "{{ localize('global.n_a') }}",
        'error_loading_data': "{{ localize('global.error_loading_data') }}",
        'add_review': "{{ localize('modules.physiotherapy.add_review') }}",
        'review_description_placeholder': "{{ localize('modules.physiotherapy.review_description_placeholder') }}",
        'days_count': "{{ localize('modules.physiotherapy.days_count') }}",
        'cancel': "{{ localize('global.cancel') }}",
        'save': "{{ localize('global.save') }}",
        'saving': "{{ localize('global.saving') }}",
        'success': "{{ localize('global.success') }}",
        'error': "{{ localize('global.error') }}",
        'request_failed': "{{ localize('global.request_failed') }}",
        'pending': "{{ localize('global.pending') }}",
        'in_progress': "{{ localize('global.in_progress') }}",
        'completed': "{{ localize('global.completed') }}",
        'cancelled': "{{ localize('global.cancelled') }}",
        'created_by': "{{ localize('global.created_by') }}",
        'no_reviews_found': "{{ localize('modules.physiotherapy.no_reviews_found') }}",
        'add_review_title': "{{ localize('modules.physiotherapy.add_review_title') }}",
        'save_review': "{{ localize('modules.physiotherapy.save_review') }}",
        'updating': "{{ localize('global.updating') }}",
        'progress_updated_successfully': "{{ localize('modules.physiotherapy.progress_updated_successfully') }}",
        'failed_to_update_progress': "{{ localize('modules.physiotherapy.failed_to_update_progress') }}",
        'error_loading_reviews': "{{ localize('modules.physiotherapy.error_loading_reviews') }}",
        'days': "{{ localize('global.days') }}",
    };

    // Global AJAX setup for CSRF token
    function getCsrfToken() {
        let token = $('meta[name="csrf-token"]').attr('content');
        if (!token) {
            token = $('input[name="_token"]').val();
        }
        if (!token) {
            token = $('form input[name="_token"]').first().val();
        }
        return token;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': getCsrfToken()
        }
    });

    // Form submission
    $('#searchForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        window.location.href = '/physiotherapy-procedures?' + params.toString();
    });

    // Reset filters
    window.resetFilters = function () {
        window.location.href = '/physiotherapy-procedures';
    };

    // Export data
    window.exportData = function () {
        const formData = new FormData(document.getElementById('searchForm'));
        const params = new URLSearchParams(formData);
        window.open('/physiotherapy-procedures?' + params.toString() + '&export=1', '_blank');
    };

    // Refresh table
    window.refreshTable = function () {
        location.reload();
    };

    // View procedure details
    window.viewProcedure = function (procedureId) {
        $('#viewProcedureModal').modal('show');

        $.ajax({
            url: `/physiotherapy-procedures/${procedureId}`,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                if (resp.success && resp.data) {
                    renderProcedureDetails(resp.data);
                }
            },
            error: function () {
                $('#procedureModalBody').html(
                    `<div class="alert alert-danger">${translations.error_loading_data}</div>`
                );
            }
        });
    };

    // Render procedure details
    function renderProcedureDetails(data) {
        const percentage = data.days_count > 0 ? (data.counter / data.days_count) * 100 : 0;
        let html = `
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.patient_name}:</label>
                <p class="form-control-plaintext">${data.patient_name || translations.n_a}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.physiotherapy_type}:</label>
                <p class="form-control-plaintext">${data.physiotherapy_type_name || translations.n_a}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.physiotherapist}:</label>
                <p class="form-control-plaintext">${data.physiotherapist_name || translations.n_a}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.type}:</label>
                <p class="form-control-plaintext">${data.type || ''}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.duration}:</label>
                <p class="form-control-plaintext">${data.duration || ''} ${translations.minutes}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.progress}:</label>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%">
                        ${data.counter}/${data.days_count}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.status}:</label>
                <p class="form-control-plaintext">${renderStatusBadge(data.status)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${translations.start_date}:</label>
                <p class="form-control-plaintext">${data.start_date || translations.n_a}</p>
            </div>
        </div>`;

        if (data.description) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">${translations.description}:</label>
            <p class="form-control-plaintext">${data.description}</p>
        </div>`;
        }
        if (data.notes) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">${translations.notes}:</label>
            <p class="form-control-plaintext">${data.notes}</p>
        </div>`;
        }

        $('#procedureModalBody').html(html);
    };

    // Add review
    window.addReview = function (procedureId) {
        $('#addReviewModal').modal('show');

        $.ajax({
            url: `/physiotherapy-procedures/${procedureId}`,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                if (resp.success && resp.data) {
                    renderAddReviewForm(resp.data);
                }
            },
            error: function () {
                $('#reviewModalBody').html(
                    `<div class="alert alert-danger">${translations.error_loading_data}</div>`
                );
            }
        });
    };

    // Render add review form
    function renderAddReviewForm(data) {
        let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">${translations.patient_name}:</label>
                <p class="form-control-plaintext">${data.patient_name || translations.n_a}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">${translations.physiotherapy_type}:</label>
                <p class="form-control-plaintext">${data.physiotherapy_type_name || translations.n_a}</p>
            </div>
        </div>
        <hr class="my-3">
        <form class="review-form" data-procedure-id="${data.id}">
            <div class="mb-3">
                <label class="form-label">${translations.description} <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" rows="4" required placeholder="${translations.review_description_placeholder}"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">${translations.status} <span class="text-danger">*</span></label>
                    <select class="form-control" name="status" required>
                        <option value="pending">${translations.pending}</option>
                        <option value="in_progress">${translations.in_progress}</option>
                        <option value="completed">${translations.completed}</option>
                        <option value="cancelled">${translations.cancelled}</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">${translations.days_count}</label>
                    <input type="number" class="form-control" name="days_count" min="0" placeholder="0">
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">${translations.cancel}</button>
                <button type="submit" class="btn btn-primary">${translations.save}</button>
            </div>
        </form>`;

        $('#reviewModalBody').html(html);

        // Bind form submission
        $('#reviewModalBody .review-form').on('submit', function (e) {
            e.preventDefault();
            submitReview(this, data.id);
        });
    };

    // Submit review
    function submitReview(form, procedureId) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="bx bx-loader-alt bx-spin"></i> ${translations.saving}`;

        $.ajax({
            url: `/physiotherapy-procedures/${procedureId}/reviews`,
            type: 'POST',
            data: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            processData: false,
            contentType: false,
            success: function (resp) {
                if (resp.success) {
                    Swal.fire({
                        icon: 'success',
                        title: translations.success,
                        text: resp.message,
                        customClass: { confirmButton: 'btn btn-success' },
                        buttonsStyling: false
                    });
                    $('#addReviewModal').modal('hide');
                    location.reload(); // Refresh to show new review count
                }
            },
            error: function (xhr) {
                let errorMessage = translations.request_failed;
                if (xhr.responseJSON?.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: translations.error,
                    html: errorMessage,
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
            },
            complete: function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Update progress
    window.updateProgress = function (procedureId) {
        $('#updateProgressModal').modal('show');

        // Get current progress
        $.ajax({
            url: `/physiotherapy-procedures/${procedureId}`,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                if (resp.success && resp.data) {
                    $('#progress_counter').val(resp.data.counter || 0);
                    $('#progress_counter').attr('max', resp.data.days_count);
                }
            }
        });

        // Bind form submission
        $('#progressForm').off('submit').on('submit', function (e) {
            e.preventDefault();
            updateProcedureProgress(procedureId);
        });
    };

    // Update procedure progress
    function updateProcedureProgress(procedureId) {
        const counter = $('#progress_counter').val();
        const submitBtn = $('#progressForm button[type="submit"]');
        const originalText = submitBtn.html();

        submitBtn.prop('disabled', true).html(`<i class="bx bx-loader-alt bx-spin"></i> ${translations.updating}`);

        $.ajax({
            url: `/physiotherapy-procedures/update-counter/${procedureId}`,
            type: 'POST',
            data: { counter: counter },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                Swal.fire({
                    icon: 'success',
                    title: translations.success,
                    text: translations.progress_updated_successfully,
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                });
                $('#updateProgressModal').modal('hide');
                location.reload();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: translations.error,
                    text: translations.failed_to_update_progress,
                    customClass: { confirmButton: 'btn btn-danger' },
                    buttonsStyling: false
                });
            },
            complete: function () {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    }

    // View reviews
    window.viewReviews = function (procedureId) {
        $('#viewReviewsModal').modal('show');

        $.ajax({
            url: `/physiotherapy-procedures/${procedureId}/reviews`,
            type: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                if (resp.success && resp.data) {
                    renderReviews(resp.data, procedureId);
                }
            },
            error: function () {
                $('#reviewsModalBody').html(
                    `<div class="alert alert-danger">${translations.error_loading_reviews}</div>`
                );
            }
        });
    };

    // Render reviews
    function renderReviews(reviews, procedureId) {
        let html = '<div class="mb-3">';
        
        if (reviews.length === 0) {
            html += '<div class="text-center text-muted py-4">';
            html += '<i class="bx bx-message-square-dots bx-lg mb-3"></i>';
            html += `<p class="mb-0">${translations.no_reviews_found}</p>`;
            html += '</div>';
        } else {
            reviews.forEach(function (review) {
                html += `
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-${getReviewStatusColor(review.status)} me-2">${getReviewStatusText(review.status)}</span>
                                <small class="text-muted">${review.created_at}</small>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="editReview(${review.id}, ${procedureId})">
                                    <i class="bx bx-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteReview(${review.id}, ${procedureId})">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                        <p class="mb-1">${review.description}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${translations.created_by}: ${review.created_by_name}</small>
                            ${review.days_count > 0 ? `<small class="text-info"><i class="bx bx-calendar me-1"></i>${review.days_count} ${translations.days}</small>` : ''}
                        </div>
                    </div>
                </div>`;
            });
        }

        html += '</div>';

        // Add new review form
        html += `
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-plus me-2"></i>${translations.add_review}</h6>
            </div>
            <div class="card-body">
                <form class="review-form" data-procedure-id="${procedureId}">
                    <div class="mb-3">
                        <label class="form-label">${translations.description} <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required placeholder="${translations.review_description_placeholder}"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">${translations.status} <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="pending">${translations.pending}</option>
                                <option value="in_progress">${translations.in_progress}</option>
                                <option value="completed">${translations.completed}</option>
                                <option value="cancelled">${translations.cancelled}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">${translations.days_count}</label>
                            <input type="number" class="form-control" name="days_count" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bx bx-save me-1"></i>${translations.save}
                        </button>
                    </div>
                </form>
            </div>
        </div>`;

        $('#reviewsModalBody').html(html);

        // Bind form submission
        $('#reviewsModalBody .review-form').on('submit', function (e) {
            e.preventDefault();
            submitReview(this, procedureId);
        });
    }

    // Get review status color
    function getReviewStatusColor(status) {
        const colors = {
            'pending': 'secondary',
            'in_progress': 'warning',
            'completed': 'success',
            'cancelled': 'danger'
        };
        return colors[status] || 'secondary';
    }

    // Get review status text (translated)
    function getReviewStatusText(status) {
        const texts = {
            'pending': translations.pending,
            'in_progress': translations.in_progress,
            'completed': translations.completed,
            'cancelled': translations.cancelled
        };
        return texts[status] || status;
    }

    // Render status badge
    function renderStatusBadge(status) {
        const colors = {
            'pending': 'secondary',
            'in_progress': 'warning',
            'completed': 'success',
            'cancelled': 'danger'
        };
        const color = colors[status] || 'secondary';
        return `<span class="badge bg-${color}">${getReviewStatusText(status)}</span>`;
    }

})();



    </script>
@endsection