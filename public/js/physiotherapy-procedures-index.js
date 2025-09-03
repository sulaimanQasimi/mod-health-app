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
                    '<div class="alert alert-danger">Error loading data</div>'
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
                <label class="form-label fw-bold">Patient Name:</label>
                <p class="form-control-plaintext">${data.patient_name || 'N/A'}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Physiotherapy Type:</label>
                <p class="form-control-plaintext">${data.physiotherapy_type_name || 'N/A'}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Physiotherapist:</label>
                <p class="form-control-plaintext">${data.physiotherapist_name || 'N/A'}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Type:</label>
                <p class="form-control-plaintext">${data.type || ''}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Duration:</label>
                <p class="form-control-plaintext">${data.duration || ''} minutes</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Progress:</label>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%">
                        ${data.counter}/${data.days_count}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Status:</label>
                <p class="form-control-plaintext">${renderStatusBadge(data.status)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Start Date:</label>
                <p class="form-control-plaintext">${data.start_date || 'N/A'}</p>
            </div>
        </div>`;

        if (data.description) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">Description:</label>
            <p class="form-control-plaintext">${data.description}</p>
        </div>`;
        }
        if (data.notes) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">Notes:</label>
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
                    '<div class="alert alert-danger">Error loading data</div>'
                );
            }
        });
    };

    // Render add review form
    function renderAddReviewForm(data) {
        let html = `
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label fw-bold">Patient Name:</label>
                <p class="form-control-plaintext">${data.patient_name || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-bold">Physiotherapy Type:</label>
                <p class="form-control-plaintext">${data.physiotherapy_type_name || 'N/A'}</p>
            </div>
        </div>
        <hr class="my-3">
        <form class="review-form" data-procedure-id="${data.id}">
            <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" name="description" rows="4" required placeholder="Enter review description"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select class="form-control" name="status" required>
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Days Count</label>
                    <input type="number" class="form-control" name="days_count" min="0" placeholder="0">
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
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
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Saving...';

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
                        title: 'Success',
                        text: resp.message,
                        customClass: { confirmButton: 'btn btn-success' },
                        buttonsStyling: false
                    });
                    $('#addReviewModal').modal('hide');
                    location.reload(); // Refresh to show new review count
                }
            },
            error: function (xhr) {
                let errorMessage = "Request failed";
                if (xhr.responseJSON?.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
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

        submitBtn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin"></i> Updating...');

        $.ajax({
            url: `/physiotherapy-procedures/update-counter/${procedureId}`,
            type: 'POST',
            data: { counter: counter },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Progress updated successfully',
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                });
                $('#updateProgressModal').modal('hide');
                location.reload();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to update progress',
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
                    '<div class="alert alert-danger">Error loading reviews</div>'
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
            html += '<p class="mb-0">No reviews found</p>';
            html += '</div>';
        } else {
            reviews.forEach(function (review) {
                html += `
                <div class="card mb-2">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-${getReviewStatusColor(review.status)} me-2">${review.status}</span>
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
                            <small class="text-muted">Created by: ${review.created_by_name}</small>
                            ${review.days_count > 0 ? `<small class="text-info"><i class="bx bx-calendar me-1"></i>${review.days_count} days</small>` : ''}
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
                <h6 class="mb-0"><i class="bx bx-plus me-2"></i>Add Review</h6>
            </div>
            <div class="card-body">
                <form class="review-form" data-procedure-id="${procedureId}">
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="description" rows="3" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Days Count</label>
                            <input type="number" class="form-control" name="days_count" min="0" placeholder="0">
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bx bx-save me-1"></i>Save
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

