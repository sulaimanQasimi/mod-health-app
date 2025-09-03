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

<!-- View Reviews Modal -->
<div class="modal fade" id="viewReviewsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bx bx-message-square-dots me-2"></i>
                    {{ localize('global.procedure_reviews') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="reviewsModalBody">
                <div class="text-center">
                    <i class="bx bx-loader-alt bx-spin"></i>
                    {{ localize('global.loading') }}...
                </div>
            </div>
        </div>
    </div>
</div>