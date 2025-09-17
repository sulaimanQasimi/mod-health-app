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
