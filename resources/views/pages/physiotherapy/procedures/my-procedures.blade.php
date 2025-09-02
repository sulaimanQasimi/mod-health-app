@extends('layouts.master')

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        <i class="bx bx-health me-2 text-info"></i>
                        {{ localize('global.my_physiotherapy_procedures') }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">{{ localize('global.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">{{ localize('global.my_physiotherapy_procedures') }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Search and Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form id="searchForm" class="row g-3">
                            <div class="col-md-3">
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
                                        {{ localize('global.pending') }}</option>
                                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>
                                        {{ localize('global.in_progress') }}</option>
                                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>
                                        {{ localize('global.completed') }}</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        {{ localize('global.cancelled') }}</option>
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
                                <label for="start_date" class="form-label">{{ localize('global.start_date') }}</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <label for="end_date" class="form-label">{{ localize('global.end_date') }}</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Procedures Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-light text-dark d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-list-ul me-2 text-primary"></i>
                            {{ localize('global.procedures_list') }}
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetFilters()">
                                <i class="bx bx-refresh me-1"></i>
                                {{ localize('global.reset') }}
                            </button>
                            <button type="button" class="btn btn-outline-success btn-sm" onclick="exportData()">
                                <i class="bx bx-download me-1"></i>
                                {{ localize('global.export') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="myProceduresTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ localize('global.number') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.physiotherapy_type') }}</th>
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
                                            </td>
                                            <td>{{ $procedure->physiotherapyType->name ?? 'N/A' }}</td>
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
                                                    {{ localize('global.' . $procedure->status) }}
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
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center text-muted py-4">
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
    </div>

    <!-- View Procedure Modal -->
    <div class="modal fade" id="viewProcedureModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-show me-2"></i>
                        {{ localize('global.procedure_details') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="procedureModalBody">
                    <div class="text-center">
                        <i class="bx bx-loader-alt bx-spin"></i>
                        {{ localize('global.loading') }}...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Review Modal -->
    <div class="modal fade" id="addReviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-plus me-2"></i>
                        {{ localize('global.add_review') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="reviewModalBody">
                    <div class="text-center">
                        <i class="bx bx-loader-alt bx-spin"></i>
                        {{ localize('global.loading') }}...
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Progress Modal -->
    <div class="modal fade" id="updateProgressModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bx bx-edit me-2"></i>
                        {{ localize('global.update_progress') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="progressForm">
                        <div class="mb-3">
                            <label for="progress_counter"
                                class="form-label">{{ localize('global.current_progress') }}</label>
                            <input type="number" class="form-control" id="progress_counter" name="counter" min="0" required>
                            <small
                                class="form-text text-muted">{{ localize('global.enter_current_session_number') }}</small>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                {{ localize('global.cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i>
                                {{ localize('global.update') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
                window.location.href = '{{ route("physiotherapy-procedures.my-procedures") }}?' + params.toString();
            });

            // Reset filters
            window.resetFilters = function () {
                window.location.href = '{{ route("physiotherapy-procedures.my-procedures") }}';
            };

            // Export data
            window.exportData = function () {
                const formData = new FormData(document.getElementById('searchForm'));
                const params = new URLSearchParams(formData);
                window.open('{{ route("physiotherapy-procedures.my-procedures") }}?' + params.toString() + '&export=1', '_blank');
            };

            // View procedure details
            window.viewProcedure = function (procedureId) {
                $('#viewProcedureModal').modal('show');

                $.ajax({
                    url: `{{ url('physiotherapy-procedures') }}/${procedureId}`,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (resp) {
                        if (resp.success && resp.data) {
                            renderProcedureDetails(resp.data);
                        }
                    },
                    error: function () {
                        $('#procedureModalBody').html(
                            '<div class="alert alert-danger">{{ localize("global.error_loading_data") }}</div>'
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
                        <label class="form-label fw-bold">{{ localize('global.patient_name') }}:</label>
                        <p class="form-control-plaintext">${data.patient_name || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.physiotherapy_type') }}:</label>
                        <p class="form-control-plaintext">${data.physiotherapy_type_name || 'N/A'}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.type') }}:</label>
                        <p class="form-control-plaintext">${data.type || ''}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.duration') }}:</label>
                        <p class="form-control-plaintext">${data.duration || ''} {{ localize('global.minutes') }}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.progress') }}:</label>
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%">
                                ${data.counter}/${data.days_count}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.status') }}:</label>
                        <p class="form-control-plaintext">${renderStatusBadge(data.status)}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.start_date') }}:</label>
                        <p class="form-control-plaintext">${data.start_date || 'N/A'}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">{{ localize('global.end_date') }}:</label>
                        <p class="form-control-plaintext">${data.end_date || 'N/A'}</p>
                    </div>
                </div>`;

                if (data.description) {
                    html += `<div class="mb-3">
                    <label class="form-label fw-bold">{{ localize('global.description') }}:</label>
                    <p class="form-control-plaintext">${data.description}</p>
                </div>`;
                }
                if (data.notes) {
                    html += `<div class="mb-3">
                    <label class="form-label fw-bold">{{ localize('global.notes') }}:</label>
                    <p class="form-control-plaintext">${data.notes}</p>
                </div>`;
                }

                $('#procedureModalBody').html(html);
            };

            // Add review
            window.addReview = function (procedureId) {
                $('#addReviewModal').modal('show');

                $.ajax({
                    url: `{{ url('physiotherapy-procedures') }}/${procedureId}`,
                    type: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (resp) {
                        if (resp.success && resp.data) {
                            renderAddReviewForm(resp.data);
                        }
                    },
                    error: function () {
                        $('#reviewModalBody').html(
                            '<div class="alert alert-danger">{{ localize("global.error_loading_data") }}</div>'
                        );
                    }
                });
            };

            // Render add review form
            function renderAddReviewForm(data) {
                let html = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ localize('global.patient_name') }}:</label>
                        <p class="form-control-plaintext">${data.patient_name || 'N/A'}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">{{ localize('global.physiotherapy_type') }}:</label>
                        <p class="form-control-plaintext">${data.physiotherapy_type_name || 'N/A'}</p>
                    </div>
                </div>
                <hr class="my-3">
                <form class="review-form" data-procedure-id="${data.id}">
                    <div class="mb-3">
                        <label class="form-label">{{ localize('global.description') }} <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="4" required placeholder="{{ localize('global.enter_review_description') }}"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="pending">{{ localize("global.pending") }}</option>
                                <option value="in_progress">{{ localize("global.in_progress") }}</option>
                                <option value="completed">{{ localize("global.completed") }}</option>
                                <option value="cancelled">{{ localize("global.cancelled") }}</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">{{ localize('global.days_count') }}</label>
                            <input type="number" class="form-control" name="days_count" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">{{ localize('global.cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ localize('global.save') }}</button>
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
                submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> {{ localize("global.saving") }}...';

                $.ajax({
                    url: `{{ url('physiotherapy-procedures') }}/${procedureId}/reviews`,
                    type: 'POST',
                    data: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    processData: false,
                    contentType: false,
                    success: function (resp) {
                        if (resp.success) {
                            Swal.fire({
                                icon: 'success',
                                title: "{{ localize('global.success') }}",
                                text: resp.message,
                                customClass: { confirmButton: 'btn btn-success' },
                                buttonsStyling: false
                            });
                            $('#addReviewModal').modal('hide');
                            location.reload(); // Refresh to show new review count
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = "{{ localize('global.request_failed') }}";
                        if (xhr.responseJSON?.errors) {
                            errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        }
                        Swal.fire({
                            icon: 'error',
                            title: "{{ localize('global.error') }}",
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
                    url: `{{ url('physiotherapy-procedures') }}/${procedureId}`,
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

                submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> {{ localize("global.updating") }}...');

                $.ajax({
                    url: `{{ url('physiotherapy-procedures') }}/update-counter/${procedureId}`,
                    type: 'POST',
                    data: { counter: counter },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (resp) {
                        Swal.fire({
                            icon: 'success',
                            title: "{{ localize('global.success') }}",
                            text: "{{ localize('global.progress_updated_successfully') }}",
                            customClass: { confirmButton: 'btn btn-success' },
                            buttonsStyling: false
                        });
                        $('#updateProgressModal').modal('hide');
                        location.reload();
                    },
                    error: function () {
                        Swal.fire({
                            icon: 'error',
                            title: "{{ localize('global.error') }}",
                            text: "{{ localize('global.failed_to_update_progress') }}",
                            customClass: { confirmButton: 'btn btn-danger' },
                            buttonsStyling: false
                        });
                    },
                    complete: function () {
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
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
                return `<span class="badge bg-${color}">${status}</span>`;
            }

        })();
    </script>
@endsection