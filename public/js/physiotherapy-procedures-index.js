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
                    `<div class="alert alert-danger">${window.translations.error_loading_data}</div>`
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
                <label class="form-label fw-bold">${window.translations.patient_name}:</label>
                <p class="form-control-plaintext">${data.patient_name || window.translations.n_a}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.physiotherapy_type}:</label>
                <p class="form-control-plaintext">${data.physiotherapy_type_name || window.translations.n_a}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.physiotherapist}:</label>
                <p class="form-control-plaintext">${data.physiotherapist_name || window.translations.n_a}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.type}:</label>
                <p class="form-control-plaintext">${data.type || ''}</p>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.duration}:</label>
                <p class="form-control-plaintext">${data.duration || ''} ${window.translations.minutes}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.progress}:</label>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: ${percentage}%">
                        ${data.counter}/${data.days_count}
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.status}:</label>
                <p class="form-control-plaintext">${renderStatusBadge(data.status)}</p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">${window.translations.start_date}:</label>
                <p class="form-control-plaintext">${data.start_date || window.translations.n_a}</p>
            </div>
        </div>`;

        if (data.description) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">${window.translations.description}:</label>
            <p class="form-control-plaintext">${data.description}</p>
        </div>`;
        }
        if (data.notes) {
            html += `<div class="mb-3">
            <label class="form-label fw-bold">${window.translations.notes}:</label>
            <p class="form-control-plaintext">${data.notes}</p>
        </div>`;
        }

        $('#procedureModalBody').html(html);
    };



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

        submitBtn.prop('disabled', true).html(`<i class="bx bx-loader-alt bx-spin"></i> ${window.translations.updating}`);

        $.ajax({
            url: `/physiotherapy-procedures/update-counter/${procedureId}`,
            type: 'POST',
            data: { counter: counter },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (resp) {
                Swal.fire({
                    icon: 'success',
                    title: window.translations.success,
                    text: window.translations.progress_updated_successfully,
                    customClass: { confirmButton: 'btn btn-success' },
                    buttonsStyling: false
                });
                $('#updateProgressModal').modal('hide');
                location.reload();
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: window.translations.error,
                    text: window.translations.failed_to_update_progress,
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
                    `<div class="alert alert-danger">${window.translations.error_loading_reviews}</div>`
                );
            }
        });
    };

    // Render reviews (read-only)
    function renderReviews(reviews, procedureId) {
        let html = '<div class="mb-3">';
        
        if (reviews.length === 0) {
            html += '<div class="text-center text-muted py-4">';
            html += '<i class="bx bx-message-square-dots bx-lg mb-3"></i>';
            html += `<p class="mb-0">${window.translations.no_reviews_found}</p>`;
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
                        </div>
                        <p class="mb-1">${review.description}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">${window.translations.created_by}: ${review.created_by_name}</small>
                            ${review.days_count > 0 ? `<small class="text-info"><i class="bx bx-calendar me-1"></i>${review.days_count} ${window.translations.days}</small>` : ''}
                        </div>
                    </div>
                </div>`;
            });
        }

        html += '</div>';

        $('#reviewsModalBody').html(html);
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
            'pending': window.translations.pending,
            'in_progress': window.translations.in_progress,
            'completed': window.translations.completed,
            'cancelled': window.translations.cancelled
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

