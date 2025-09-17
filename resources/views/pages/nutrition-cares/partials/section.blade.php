<div class="col-md-12 mt-4">
    <h5 class="mb-4 p-3 bg-label-primary">
        <i class="bx bx-food-menu p-1"></i>{{ localize('global.nutrition_care') }}
    </h5>
    <div class="d-flex gap-2 mb-3">
        @can('create', \App\Models\NutritionCare::class)
            <button type="button" class="btn btn-success" data-bs-toggle="modal"
                data-bs-target="#createNutritionCareModal">
                <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
            </button>
        @endcan
    </div>

    <!-- Nutrition Care Data Container -->
    <div id="nutrition-care-data-container">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">{{ localize('global.loading') }}...</span>
            </div>
            <p class="mt-2 text-muted">{{ localize('global.loading_nutrition_care_data') }}...</p>
        </div>
    </div>
</div>

<!-- Create Nutrition Care Modal -->
<div class="modal fade" id="createNutritionCareModal" tabindex="-1" aria-labelledby="createNutritionCareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="createNutritionCareForm" method="POST" action="{{ route('nutrition-cares.store') }}">
                @csrf
                <input type="hidden" name="morphable_type" value="{{ str_replace('\\', '\\\\', get_class($morphModel)) }}">
                <input type="hidden" name="morphable_id" value="{{ $morphModel->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="createNutritionCareModalLabel">
                        <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                                <input type="text" class="form-control" id="patient_name" name="patient_name" value="{{ $morphModel->patient->name ?? '' }}" readonly>
                                <small class="form-text text-muted">{{ localize('global.patient_name_auto_filled') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
                                <input type="text" class="form-control" id="nurse_id" name="nurse_id" value="{{ auth()->user()->nurse->full_name ?? '' }}" readonly>
                                <input type="hidden" name="nurse_id" value="{{ auth()->user()->nurse->id ?? '' }}">
                                <small class="form-text text-muted">{{ localize('global.nurse_auto_filled') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Observations Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold">{{ localize('global.observations') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="cough" name="cough" value="1">
                                    <label class="form-check-label" for="cough">{{ localize('global.cough') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="sound" name="sound" value="1">
                                    <label class="form-check-label" for="sound">{{ localize('global.sound') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="fluid_swallowing_ability" name="fluid_swallowing_ability" value="1">
                                    <label class="form-check-label" for="fluid_swallowing_ability">{{ localize('global.fluid_swallowing_ability') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="weight" name="weight" value="1">
                                    <label class="form-check-label" for="weight">{{ localize('global.weight') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="amount_and_type_of_nutrition" name="amount_and_type_of_nutrition" value="1">
                                    <label class="form-check-label" for="amount_and_type_of_nutrition">{{ localize('global.amount_and_type_of_nutrition') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="diarrhea" name="diarrhea" value="1">
                                    <label class="form-check-label" for="diarrhea">{{ localize('global.diarrhea') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="heart_failure_and_kidney_disease" name="heart_failure_and_kidney_disease" value="1">
                                    <label class="form-check-label" for="heart_failure_and_kidney_disease">{{ localize('global.heart_failure_and_kidney_disease') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remaining_materials" name="remaining_materials" value="1">
                                    <label class="form-check-label" for="remaining_materials">{{ localize('global.remaining_materials') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="type_of_tube" name="type_of_tube" value="1">
                                    <label class="form-check-label" for="type_of_tube">{{ localize('global.type_of_tube') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interventions Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold">{{ localize('global.interventions') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="constipation" name="constipation" value="1">
                                    <label class="form-check-label" for="constipation">{{ localize('global.constipation') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="nutrition_is_provided" name="nutrition_is_provided" value="1">
                                    <label class="form-check-label" for="nutrition_is_provided">{{ localize('global.nutrition_is_provided') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="mouth_hygiene" name="mouth_hygiene" value="1">
                                    <label class="form-check-label" for="mouth_hygiene">{{ localize('global.mouth_hygiene') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="oral_nutrition_advices" name="oral_nutrition_advices" value="1">
                                    <label class="form-check-label" for="oral_nutrition_advices">{{ localize('global.oral_nutrition_advices') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="voice_exercise" name="voice_exercise" value="1">
                                    <label class="form-check-label" for="voice_exercise">{{ localize('global.voice_exercise') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="swallowing_exercise" name="swallowing_exercise" value="1">
                                    <label class="form-check-label" for="swallowing_exercise">{{ localize('global.swallowing_exercise') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="aspiration_prevention_proceeded" name="aspiration_prevention_proceeded" value="1">
                                    <label class="form-check-label" for="aspiration_prevention_proceeded">{{ localize('global.aspiration_prevention_proceeded') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="nutrition_care_full_note" class="form-label">{{ localize('global.nutrition_care_full_note') }}</label>
                        <textarea class="form-control" id="nutrition_care_full_note" name="nutrition_care_full_note" rows="4" placeholder="{{ localize('global.enter_nutrition_care_notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-success" id="submitCreateNutritionCareBtn">
                        <i class="bx bx-save"></i>
                        {{ localize('global.create_nutrition_care') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Nutrition Care Modal -->
<div class="modal fade" id="editNutritionCareModal" tabindex="-1" aria-labelledby="editNutritionCareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="editNutritionCareForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editNutritionCareModalLabel">
                        <i class="bx bx-edit"></i> {{ localize('global.edit_nutrition_care') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_patient_name" class="form-label">{{ localize('global.patient_name') }}</label>
                                <input type="text" class="form-control" id="edit_patient_name" name="patient_name" value="{{ $morphModel->patient->name ?? '' }}" readonly>
                                <small class="form-text text-muted">{{ localize('global.patient_name_auto_filled') }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
                                <input type="text" class="form-control" id="edit_nurse_id" name="nurse_id" value="{{ auth()->user()->nurse->full_name ?? '' }}" readonly>
                                <input type="hidden" name="nurse_id" value="{{ auth()->user()->nurse->id ?? '' }}">
                                <small class="form-text text-muted">{{ localize('global.nurse_auto_filled') }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Observations Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold">{{ localize('global.observations') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_cough" name="cough" value="1">
                                    <label class="form-check-label" for="edit_cough">{{ localize('global.cough') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_sound" name="sound" value="1">
                                    <label class="form-check-label" for="edit_sound">{{ localize('global.sound') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_fluid_swallowing_ability" name="fluid_swallowing_ability" value="1">
                                    <label class="form-check-label" for="edit_fluid_swallowing_ability">{{ localize('global.fluid_swallowing_ability') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_weight" name="weight" value="1">
                                    <label class="form-check-label" for="edit_weight">{{ localize('global.weight') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_amount_and_type_of_nutrition" name="amount_and_type_of_nutrition" value="1">
                                    <label class="form-check-label" for="edit_amount_and_type_of_nutrition">{{ localize('global.amount_and_type_of_nutrition') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_diarrhea" name="diarrhea" value="1">
                                    <label class="form-check-label" for="edit_diarrhea">{{ localize('global.diarrhea') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_heart_failure_and_kidney_disease" name="heart_failure_and_kidney_disease" value="1">
                                    <label class="form-check-label" for="edit_heart_failure_and_kidney_disease">{{ localize('global.heart_failure_and_kidney_disease') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_remaining_materials" name="remaining_materials" value="1">
                                    <label class="form-check-label" for="edit_remaining_materials">{{ localize('global.remaining_materials') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_type_of_tube" name="type_of_tube" value="1">
                                    <label class="form-check-label" for="edit_type_of_tube">{{ localize('global.type_of_tube') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Interventions Section -->
                    <div class="mb-4">
                        <h6 class="fw-bold">{{ localize('global.interventions') }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_constipation" name="constipation" value="1">
                                    <label class="form-check-label" for="edit_constipation">{{ localize('global.constipation') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_nutrition_is_provided" name="nutrition_is_provided" value="1">
                                    <label class="form-check-label" for="edit_nutrition_is_provided">{{ localize('global.nutrition_is_provided') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_mouth_hygiene" name="mouth_hygiene" value="1">
                                    <label class="form-check-label" for="edit_mouth_hygiene">{{ localize('global.mouth_hygiene') }}</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_oral_nutrition_advices" name="oral_nutrition_advices" value="1">
                                    <label class="form-check-label" for="edit_oral_nutrition_advices">{{ localize('global.oral_nutrition_advices') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_voice_exercise" name="voice_exercise" value="1">
                                    <label class="form-check-label" for="edit_voice_exercise">{{ localize('global.voice_exercise') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_swallowing_exercise" name="swallowing_exercise" value="1">
                                    <label class="form-check-label" for="edit_swallowing_exercise">{{ localize('global.swallowing_exercise') }}</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="edit_aspiration_prevention_proceeded" name="aspiration_prevention_proceeded" value="1">
                                    <label class="form-check-label" for="edit_aspiration_prevention_proceeded">{{ localize('global.aspiration_prevention_proceeded') }}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_nutrition_care_full_note" class="form-label">{{ localize('global.nutrition_care_full_note') }}</label>
                        <textarea class="form-control" id="edit_nutrition_care_full_note" name="nutrition_care_full_note" rows="4" placeholder="{{ localize('global.enter_nutrition_care_notes') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        {{ localize('global.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-warning" id="submitEditNutritionCareBtn">
                        <i class="bx bx-save"></i>
                        {{ localize('global.update_nutrition_care') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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

<script>
$(document).ready(function() {
    // Load nutrition care data via AJAX
    function loadNutritionCareData() {
        console.log('Loading nutrition care data...');
        console.log('Morphable Type:', '{{ str_replace('\\', '\\\\', get_class($morphModel)) }}');
        console.log('Morphable ID:', '{{ $morphModel->id }}');
        console.log('Route URL:', '{{ route("nutrition-cares.index") }}');
        
        $.ajax({
            url: '{{ route("nutrition-cares.index") }}',
            method: 'GET',
            data: {
                morphable_type: '{{ str_replace('\\', '\\\\', get_class($morphModel)) }}',
                morphable_id: '{{ $morphModel->id }}'
            },
            success: function(response) {
                console.log('Nutrition care data loaded successfully:', response);
                var nutritionCares = response.data;
                var container = $('#nutrition-care-data-container');
                
                if (nutritionCares.length > 0) {
                    var tableHtml = `
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>{{ localize('global.id') }}</th>
                                        <th>{{ localize('global.patient_name') }}</th>
                                        <th>{{ localize('global.nurse') }}</th>
                                        <th>{{ localize('global.observations') }}</th>
                                        <th>{{ localize('global.interventions') }}</th>
                                        <th>{{ localize('global.nutrition_care_full_note') }}</th>
                                        <th>{{ localize('global.date_signature') }}</th>
                                        <th>{{ localize('global.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                    
                    nutritionCares.forEach(function(nutritionCare) {
                        // Build observations list
                        var observations = [];
                        if (nutritionCare.cough) observations.push('{{ localize("global.cough") }}');
                        if (nutritionCare.sound) observations.push('{{ localize("global.sound") }}');
                        if (nutritionCare.fluid_swallowing_ability) observations.push('{{ localize("global.fluid_swallowing_ability") }}');
                        if (nutritionCare.weight) observations.push('{{ localize("global.weight") }}');
                        if (nutritionCare.amount_and_type_of_nutrition) observations.push('{{ localize("global.amount_and_type_of_nutrition") }}');
                        if (nutritionCare.diarrhea) observations.push('{{ localize("global.diarrhea") }}');
                        if (nutritionCare.heart_failure_and_kidney_disease) observations.push('{{ localize("global.heart_failure_and_kidney_disease") }}');
                        if (nutritionCare.remaining_materials) observations.push('{{ localize("global.remaining_materials") }}');
                        if (nutritionCare.type_of_tube) observations.push('{{ localize("global.type_of_tube") }}');
                        
                        // Build interventions list
                        var interventions = [];
                        if (nutritionCare.constipation) interventions.push('{{ localize("global.constipation") }}');
                        if (nutritionCare.nutrition_is_provided) interventions.push('{{ localize("global.nutrition_is_provided") }}');
                        if (nutritionCare.mouth_hygiene) interventions.push('{{ localize("global.mouth_hygiene") }}');
                        if (nutritionCare.oral_nutrition_advices) interventions.push('{{ localize("global.oral_nutrition_advices") }}');
                        if (nutritionCare.voice_exercise) interventions.push('{{ localize("global.voice_exercise") }}');
                        if (nutritionCare.swallowing_exercise) interventions.push('{{ localize("global.swallowing_exercise") }}');
                        if (nutritionCare.aspiration_prevention_proceeded) interventions.push('{{ localize("global.aspiration_prevention_proceeded") }}');
                        
                        var observationsText = observations.length > 0 ? observations.join(', ') : '-';
                        var interventionsText = interventions.length > 0 ? interventions.join(', ') : '-';
                        var noteText = nutritionCare.nutrition_care_full_note ? 
                            (nutritionCare.nutrition_care_full_note.length > 50 ? 
                                nutritionCare.nutrition_care_full_note.substring(0, 50) + '...' : 
                                nutritionCare.nutrition_care_full_note) : '-';
                        
                        tableHtml += `
                            <tr>
                                <td>${nutritionCare.id}</td>
                                <td>${nutritionCare.patient_name || '-'}</td>
                                <td>${nutritionCare.nurse ? nutritionCare.nurse.full_name : 'N/A'}</td>
                                <td>${observationsText}</td>
                                <td>${interventionsText}</td>
                                <td>
                                    ${nutritionCare.nutrition_care_full_note ? 
                                        `<span class="text-truncate d-inline-block" style="max-width: 200px;" title="${nutritionCare.nutrition_care_full_note}">${noteText}</span>` : 
                                        '<span class="text-muted">-</span>'
                                    }
                                </td>
                                <td>${new Date(nutritionCare.created_at).toLocaleDateString('en-CA')} ${new Date(nutritionCare.created_at).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'})}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info view-nutrition-care-btn"
                                            data-nutrition-care-id="${nutritionCare.id}"
                                            title="{{ localize('global.view') }}">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <a href="/nutrition-cares/${nutritionCare.id}/print" class="btn btn-sm btn-primary" title="{{ localize('global.print') }}" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-warning edit-nutrition-care-btn"
                                            data-nutrition-care-id="${nutritionCare.id}"
                                            data-cough="${nutritionCare.cough ? '1' : '0'}"
                                            data-sound="${nutritionCare.sound ? '1' : '0'}"
                                            data-fluid-swallowing-ability="${nutritionCare.fluid_swallowing_ability ? '1' : '0'}"
                                            data-weight="${nutritionCare.weight ? '1' : '0'}"
                                            data-amount-and-type-of-nutrition="${nutritionCare.amount_and_type_of_nutrition ? '1' : '0'}"
                                            data-diarrhea="${nutritionCare.diarrhea ? '1' : '0'}"
                                            data-heart-failure-and-kidney-disease="${nutritionCare.heart_failure_and_kidney_disease ? '1' : '0'}"
                                            data-remaining-materials="${nutritionCare.remaining_materials ? '1' : '0'}"
                                            data-type-of-tube="${nutritionCare.type_of_tube ? '1' : '0'}"
                                            data-constipation="${nutritionCare.constipation ? '1' : '0'}"
                                            data-nutrition-is-provided="${nutritionCare.nutrition_is_provided ? '1' : '0'}"
                                            data-mouth-hygiene="${nutritionCare.mouth_hygiene ? '1' : '0'}"
                                            data-oral-nutrition-advices="${nutritionCare.oral_nutrition_advices ? '1' : '0'}"
                                            data-voice-exercise="${nutritionCare.voice_exercise ? '1' : '0'}"
                                            data-swallowing-exercise="${nutritionCare.swallowing_exercise ? '1' : '0'}"
                                            data-aspiration-prevention-proceeded="${nutritionCare.aspiration_prevention_proceeded ? '1' : '0'}"
                                            data-nutrition-care-full-note="${nutritionCare.nutrition_care_full_note || ''}"
                                            title="{{ localize('global.edit') }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger delete-nutrition-care-btn" 
                                            data-nutrition-care-id="${nutritionCare.id}" 
                                            title="{{ localize('global.delete') }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>`;
                    });
                    
                    tableHtml += `
                                </tbody>
                            </table>
                        </div>`;
                    
                    container.html(tableHtml);
                } else {
                    container.html(`
                        <div class="text-center py-4">
                            <div class="mb-3">
                                <i class="bx bx-food-menu bx-lg text-muted"></i>
                            </div>
                            <h5 class="text-muted">{{ localize('global.no_nutrition_care_found') }}</h5>
                            <p class="text-muted">{{ localize('global.add_first_nutrition_care') }}</p>
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createNutritionCareModal">
                                <i class="bx bx-plus"></i> {{ localize('global.create_nutrition_care') }}
                            </button>
                        </div>
                    `);
                }
                
                // Re-bind event handlers for dynamically loaded content
                bindEventHandlers();
            },
            error: function(xhr) {
                console.error('Error loading nutrition care data:', xhr);
                console.error('Status:', xhr.status);
                console.error('Response:', xhr.responseText);
                
                var errorMessage = '{{ localize("global.error_loading_nutrition_care_data") }}';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                $('#nutrition-care-data-container').html(`
                    <div class="alert alert-danger">
                        <i class="bx bx-error"></i> ${errorMessage}
                        <br><small>Status: ${xhr.status}</small>
                    </div>
                `);
            }
        });
    }
    
    // Bind event handlers for dynamically loaded content
    function bindEventHandlers() {
        // View nutrition care functionality
        $('.view-nutrition-care-btn').off('click').on('click', function() {
            var nutritionCareId = $(this).data('nutrition-care-id');
            
            // Show modal first
            $('#viewNutritionCareModal').modal('show');
            
            // Reset modal state
            $('#view-nutrition-care-loading').show();
            $('#view-nutrition-care-content').hide();
            $('#view-nutrition-care-error').hide();
            
            // Load nutrition care data via AJAX
            $.ajax({
                url: '/nutrition-cares/' + nutritionCareId,
                method: 'GET',
                success: function(response) {
                    var nutritionCare = response.data;
                    
                    // Populate modal fields
                    $('#view-patient-name').text(nutritionCare.patient_name || 'N/A');
                    $('#view-nurse-name').text(nutritionCare.nurse ? nutritionCare.nurse.full_name : 'N/A');
                    $('#view-nutrition-care-full-note').text(nutritionCare.nutrition_care_full_note || 'N/A');
                    $('#view-created-at').text(new Date(nutritionCare.created_at).toLocaleString());
                    $('#view-created-by').text(nutritionCare.created_by ? nutritionCare.created_by.name : 'System');

                    // Populate observations
                    var observationsContainer = $('#view-observations');
                    var observationsList = [];
                    var observationFields = [
                        'cough', 'sound', 'fluid_swallowing_ability', 'weight', 
                        'amount_and_type_of_nutrition', 'diarrhea', 'heart_failure_and_kidney_disease', 
                        'remaining_materials', 'type_of_tube'
                    ];
                    
                    observationFields.forEach(function(field) {
                        if (nutritionCare[field]) {
                            var label = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            observationsList.push(label);
                        }
                    });
                    
                    if (observationsList.length > 0) {
                        observationsContainer.html('<p>' + observationsList.join(', ') + '</p>');
                    } else {
                        observationsContainer.html('<p class="text-muted">{{ localize("global.no_observations") }}</p>');
                    }

                    // Populate interventions
                    var interventionsContainer = $('#view-interventions');
                    var interventionsList = [];
                    var interventionFields = [
                        'constipation', 'nutrition_is_provided', 'mouth_hygiene', 
                        'oral_nutrition_advices', 'voice_exercise', 'swallowing_exercise', 
                        'aspiration_prevention_proceeded'
                    ];
                    
                    interventionFields.forEach(function(field) {
                        if (nutritionCare[field]) {
                            var label = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            interventionsList.push(label);
                        }
                    });
                    
                    if (interventionsList.length > 0) {
                        interventionsContainer.html('<p>' + interventionsList.join(', ') + '</p>');
                    } else {
                        interventionsContainer.html('<p class="text-muted">{{ localize("global.no_interventions") }}</p>');
                    }

                    // Show content and hide loading
                    $('#view-nutrition-care-loading').hide();
                    $('#view-nutrition-care-content').show();
                },
                error: function(xhr) {
                    // Show error and hide loading
                    $('#view-nutrition-care-loading').hide();
                    $('#view-nutrition-care-error').show();
                }
            });
        });

        // Edit nutrition care functionality
        $('.edit-nutrition-care-btn').off('click').on('click', function() {
            var nutritionCareId = $(this).data('nutrition-care-id');
            var observations = {
                cough: $(this).data('cough'),
                sound: $(this).data('sound'),
                fluid_swallowing_ability: $(this).data('fluid-swallowing-ability'),
                weight: $(this).data('weight'),
                amount_and_type_of_nutrition: $(this).data('amount-and-type-of-nutrition'),
                diarrhea: $(this).data('diarrhea'),
                heart_failure_and_kidney_disease: $(this).data('heart-failure-and-kidney-disease'),
                remaining_materials: $(this).data('remaining-materials'),
                type_of_tube: $(this).data('type-of-tube')
            };
            var interventions = {
                constipation: $(this).data('constipation'),
                nutrition_is_provided: $(this).data('nutrition-is-provided'),
                mouth_hygiene: $(this).data('mouth-hygiene'),
                oral_nutrition_advices: $(this).data('oral-nutrition-advices'),
                voice_exercise: $(this).data('voice-exercise'),
                swallowing_exercise: $(this).data('swallowing-exercise'),
                aspiration_prevention_proceeded: $(this).data('aspiration-prevention-proceeded')
            };
            var nutritionCareFullNote = $(this).data('nutrition-care-full-note');

            // Set the form action URL
            $('#editNutritionCareForm').attr('action', '/nutrition-cares/' + nutritionCareId);

            // Populate form fields (patient name and nurse are auto-filled)
            $('#edit_nutrition_care_full_note').val(nutritionCareFullNote);

            // Set observation checkboxes
            Object.keys(observations).forEach(function(key) {
                $('#edit_' + key).prop('checked', observations[key] === '1');
            });

            // Set intervention checkboxes
            Object.keys(interventions).forEach(function(key) {
                $('#edit_' + key).prop('checked', interventions[key] === '1');
            });

            // Show modal
            $('#editNutritionCareModal').modal('show');
        });

        // Delete nutrition care functionality
        $('.delete-nutrition-care-btn').off('click').on('click', function() {
            var nutritionCareId = $(this).data('nutrition-care-id');
            
            if (confirm('{{ localize('global.are_you_sure_delete') }}')) {
                $.ajax({
                    url: '/nutrition-cares/' + nutritionCareId,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        // Reload the nutrition care data
                        loadNutritionCareData();
                        
                        // Show success message
                        if (typeof showToast === 'function') {
                            showToast('success', '{{ localize("global.nutrition_care_deleted_successfully") }}');
                        } else {
                            alert('{{ localize("global.nutrition_care_deleted_successfully") }}');
                        }
                    },
                    error: function(xhr) {
                        var errorMessage = '{{ localize("global.error_deleting_nutrition_care") }}';
                        
                        if (typeof showToast === 'function') {
                            showToast('error', errorMessage);
                        } else {
                            alert(errorMessage);
                        }
                    }
                });
            }
        });
    }
    
    // Load data on page load
    loadNutritionCareData();
    
    // Handle create nutrition care form submission with AJAX
    $('#createNutritionCareForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitCreateNutritionCareBtn');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('{{ localize("global.saving") }}...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(response) {
                // Close modal
                $('#createNutritionCareModal').modal('hide');
                
                // Reload the nutrition care data
                loadNutritionCareData();
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', '{{ localize("global.nutrition_care_created_successfully") }}');
                } else {
                    alert('{{ localize("global.nutrition_care_created_successfully") }}');
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMessage = '{{ localize("global.error_creating_nutrition_care") }}';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Edit nutrition care functionality
    $('.edit-nutrition-care-btn').click(function() {
        var nutritionCareId = $(this).data('nutrition-care-id');
        var patientName = $(this).data('patient-name');
        var nurseId = $(this).data('nurse-id');
        var observations = {
            cough: $(this).data('cough'),
            sound: $(this).data('sound'),
            fluid_swallowing_ability: $(this).data('fluid-swallowing-ability'),
            weight: $(this).data('weight'),
            amount_and_type_of_nutrition: $(this).data('amount-and-type-of-nutrition'),
            diarrhea: $(this).data('diarrhea'),
            heart_failure_and_kidney_disease: $(this).data('heart-failure-and-kidney-disease'),
            remaining_materials: $(this).data('remaining-materials'),
            type_of_tube: $(this).data('type-of-tube')
        };
        var interventions = {
            constipation: $(this).data('constipation'),
            nutrition_is_provided: $(this).data('nutrition-is-provided'),
            mouth_hygiene: $(this).data('mouth-hygiene'),
            oral_nutrition_advices: $(this).data('oral-nutrition-advices'),
            voice_exercise: $(this).data('voice-exercise'),
            swallowing_exercise: $(this).data('swallowing-exercise'),
            aspiration_prevention_proceeded: $(this).data('aspiration-prevention-proceeded')
        };
        var nutritionCareFullNote = $(this).data('nutrition-care-full-note');

        // Set the form action URL
        $('#editNutritionCareForm').attr('action', '{{ route("nutrition-cares.update", ":id") }}'.replace(':id', nutritionCareId));

        // Populate form fields
        $('#edit_patient_name').val(patientName);
        $('#edit_nurse_id').val(nurseId);
        $('#edit_nutrition_care_full_note').val(nutritionCareFullNote);

        // Set observation checkboxes
        Object.keys(observations).forEach(function(key) {
            $('#edit_' + key).prop('checked', observations[key] === '1');
        });

        // Set intervention checkboxes
        Object.keys(interventions).forEach(function(key) {
            $('#edit_' + key).prop('checked', interventions[key] === '1');
        });

        // Show modal
        $('#editNutritionCareModal').modal('show');
    });

    // Handle edit nutrition care form submission with AJAX
    $('#editNutritionCareForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var submitBtn = $('#submitEditNutritionCareBtn');
        var originalText = submitBtn.text();
        
        // Disable submit button and show loading
        submitBtn.prop('disabled', true).text('{{ localize("global.updating") }}...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'PUT',
            data: form.serialize(),
            success: function(response) {
                // Close modal
                $('#editNutritionCareModal').modal('hide');
                
                // Reload the nutrition care data
                loadNutritionCareData();
                
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', '{{ localize("global.nutrition_care_updated_successfully") }}');
                } else {
                    alert('{{ localize("global.nutrition_care_updated_successfully") }}');
                }
            },
            error: function(xhr) {
                var errors = xhr.responseJSON?.errors || {};
                var errorMessage = '{{ localize("global.error_updating_nutrition_care") }}';
                
                if (Object.keys(errors).length > 0) {
                    errorMessage = Object.values(errors).flat().join('\n');
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            },
            complete: function() {
                // Re-enable submit button
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    // View nutrition care functionality
    $('.view-nutrition-care-btn').click(function() {
        var patientName = $(this).data('patient-name');
        var nurseName = $(this).data('nurse-name');
        var observations = $(this).data('observations');
        var interventions = $(this).data('interventions');
        var nutritionCareFullNote = $(this).data('nutrition-care-full-note');
        var createdAt = $(this).data('created-at');
        var createdBy = $(this).data('created-by');

        // Populate modal fields
        $('#view-patient-name').text(patientName);
        $('#view-nurse-name').text(nurseName);
        $('#view-nutrition-care-full-note').text(nutritionCareFullNote);
        $('#view-created-at').text(createdAt);
        $('#view-created-by').text(createdBy);

        // Populate observations
        var observationsContainer = $('#view-observations');
        var observationsList = [];
        Object.keys(observations).forEach(function(key) {
            if (observations[key]) {
                observationsList.push(key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
            }
        });
        
        if (observationsList.length > 0) {
            observationsContainer.html('<p>' + observationsList.join(', ') + '</p>');
        } else {
            observationsContainer.html('<p class="text-muted">{{ localize("global.no_observations") }}</p>');
        }

        // Populate interventions
        var interventionsContainer = $('#view-interventions');
        var interventionsList = [];
        Object.keys(interventions).forEach(function(key) {
            if (interventions[key]) {
                interventionsList.push(key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
            }
        });
        
        if (interventionsList.length > 0) {
            interventionsContainer.html('<p>' + interventionsList.join(', ') + '</p>');
        } else {
            interventionsContainer.html('<p class="text-muted">{{ localize("global.no_interventions") }}</p>');
        }

        // Show modal
        $('#viewNutritionCareModal').modal('show');
    });
});
</script>
