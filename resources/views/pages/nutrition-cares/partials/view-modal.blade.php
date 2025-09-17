<!-- View Nutrition Care Modal -->
<div class="modal fade" id="viewNutritionCareModal" tabindex="-1" aria-labelledby="viewNutritionCareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewNutritionCareModalLabel">
                    <i class="bx bx-show"></i> {{ localize('global.view_nutrition_care_details') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loading State -->
                <div id="view-nutrition-care-loading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">{{ localize('global.loading') }}...</span>
                    </div>
                    <p class="mt-2 text-muted">{{ localize('global.loading_nutrition_care_details') }}...</p>
                </div>

                <!-- Content (hidden initially) -->
                <div id="view-nutrition-care-content" style="display: none;">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.patient_name') }}</label>
                                <p class="form-control-plaintext" id="view-patient-name">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.nurse') }}</label>
                                <p class="form-control-plaintext" id="view-nurse-name">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ localize('global.observations') }}</label>
                        <div id="view-observations">
                            <p class="text-muted">{{ localize('global.no_observations') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ localize('global.interventions') }}</label>
                        <div id="view-interventions">
                            <p class="text-muted">{{ localize('global.no_interventions') }}</p>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ localize('global.nutrition_care_full_note') }}</label>
                        <p class="form-control-plaintext" id="view-nutrition-care-full-note">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.created_at') }}</label>
                                <p class="form-control-plaintext" id="view-created-at">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ localize('global.created_by') }}</label>
                                <p class="form-control-plaintext" id="view-created-by">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div id="view-nutrition-care-error" class="alert alert-danger" style="display: none;">
                    <i class="bx bx-error"></i> {{ localize('global.error_loading_nutrition_care_details') }}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    {{ localize('global.close') }}
                </button>
            </div>
        </div>
    </div>
</div>
