<!-- Patient Information -->
<div class="row">
    <div class="col-md-4">
        <div class="mb-3">
            <label for="patient_name" class="form-label">{{ localize('global.patient_name') }} <span class="text-danger">*</span></label>
            @if(isset($patient_name))
                <!-- Auto-filled from context -->
                <input type="hidden" name="patient_name" value="{{ $patient_name }}">
                <input type="text" class="form-control" value="{{ $patient_name }}" readonly>
            @else
                <!-- Manual entry for standalone forms -->
                <input type="text" class="form-control" id="patient_name" name="patient_name" value="{{ old('patient_name', $nutritionCare->patient_name ?? '') }}" required>
            @endif
            @error('patient_name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label for="nurse_id" class="form-label">{{ localize('global.nurse') }}</label>
            @if(auth()->user()->nurse)
                <input type="hidden" name="nurse_id" value="{{ auth()->user()->nurse->id }}">
                <input type="text" class="form-control" value="{{ auth()->user()->nurse->full_name }}" readonly>
            @else
                <select class="form-control" id="nurse_id" name="nurse_id">
                    <option value="">{{ localize('global.select_nurse') }}</option>
                    @foreach($nurses ?? [] as $nurse)
                        <option value="{{ $nurse->id }}" {{ old('nurse_id', $nutritionCare->nurse_id ?? '') == $nurse->id ? 'selected' : '' }}>
                            {{ $nurse->full_name }}
                        </option>
                    @endforeach
                </select>
            @endif
            @error('nurse_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="mb-3">
            <label class="form-label">{{ localize('global.record_type') }}</label>
            @if(isset($morphable_type) && isset($morphable_id))
                <!-- Auto-filled from context -->
                <input type="hidden" name="morphable_type" value="{{ $morphable_type }}">
                <input type="hidden" name="morphable_id" value="{{ $morphable_id }}">
                <input type="text" class="form-control" value="{{ $morphable_type == 'App\Models\UnderReview' ? localize('global.under_review') : localize('global.hospitalization') }}" readonly>
            @else
                <!-- Manual selection for standalone forms -->
                <select class="form-control" id="morphable_type" name="morphable_type" required>
                    <option value="">{{ localize('global.select_record_type') }}</option>
                    <option value="App\Models\UnderReview" {{ old('morphable_type', $nutritionCare->morphable_type ?? '') == 'App\Models\UnderReview' ? 'selected' : '' }}>{{ localize('global.under_review') }}</option>
                    <option value="App\Models\Hospitalization" {{ old('morphable_type', $nutritionCare->morphable_type ?? '') == 'App\Models\Hospitalization' ? 'selected' : '' }}>{{ localize('global.hospitalization') }}</option>
                </select>
                @error('morphable_type')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            @endif
        </div>
    </div>
</div>

@if(!isset($morphable_type) || !isset($morphable_id))
<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="morphable_id" class="form-label">{{ localize('global.record_id') }} <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="morphable_id" name="morphable_id" value="{{ old('morphable_id', $nutritionCare->morphable_id ?? '') }}" required>
            @error('morphable_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
@endif

<!-- Observations Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.observations') }}</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="cough" name="cough" value="1" {{ old('cough', $nutritionCare->cough ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="cough">{{ localize('global.cough') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="sound" name="sound" value="1" {{ old('sound', $nutritionCare->sound ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="sound">{{ localize('global.sound') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="fluid_swallowing_ability" name="fluid_swallowing_ability" value="1" {{ old('fluid_swallowing_ability', $nutritionCare->fluid_swallowing_ability ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="fluid_swallowing_ability">{{ localize('global.fluid_swallowing_ability') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="weight" name="weight" value="1" {{ old('weight', $nutritionCare->weight ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="weight">{{ localize('global.weight') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="amount_and_type_of_nutrition" name="amount_and_type_of_nutrition" value="1" {{ old('amount_and_type_of_nutrition', $nutritionCare->amount_and_type_of_nutrition ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="amount_and_type_of_nutrition">{{ localize('global.amount_and_type_of_nutrition') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="diarrhea" name="diarrhea" value="1" {{ old('diarrhea', $nutritionCare->diarrhea ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="diarrhea">{{ localize('global.diarrhea') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="heart_failure_and_kidney_disease" name="heart_failure_and_kidney_disease" value="1" {{ old('heart_failure_and_kidney_disease', $nutritionCare->heart_failure_and_kidney_disease ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="heart_failure_and_kidney_disease">{{ localize('global.heart_failure_and_kidney_disease') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="remaining_materials" name="remaining_materials" value="1" {{ old('remaining_materials', $nutritionCare->remaining_materials ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="remaining_materials">{{ localize('global.remaining_materials') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="type_of_tube" name="type_of_tube" value="1" {{ old('type_of_tube', $nutritionCare->type_of_tube ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="type_of_tube">{{ localize('global.type_of_tube') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Interventions Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.interventions') }}</h5>
        <div class="row">
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="constipation" name="constipation" value="1" {{ old('constipation', $nutritionCare->constipation ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="constipation">{{ localize('global.constipation') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="nutrition_is_provided" name="nutrition_is_provided" value="1" {{ old('nutrition_is_provided', $nutritionCare->nutrition_is_provided ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="nutrition_is_provided">{{ localize('global.nutrition_is_provided') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="mouth_hygiene" name="mouth_hygiene" value="1" {{ old('mouth_hygiene', $nutritionCare->mouth_hygiene ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="mouth_hygiene">{{ localize('global.mouth_hygiene') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="oral_nutrition_advices" name="oral_nutrition_advices" value="1" {{ old('oral_nutrition_advices', $nutritionCare->oral_nutrition_advices ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="oral_nutrition_advices">{{ localize('global.oral_nutrition_advices') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="voice_exercise" name="voice_exercise" value="1" {{ old('voice_exercise', $nutritionCare->voice_exercise ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="voice_exercise">{{ localize('global.voice_exercise') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="swallowing_exercise" name="swallowing_exercise" value="1" {{ old('swallowing_exercise', $nutritionCare->swallowing_exercise ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="swallowing_exercise">{{ localize('global.swallowing_exercise') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="aspiration_prevention_proceeded" name="aspiration_prevention_proceeded" value="1" {{ old('aspiration_prevention_proceeded', $nutritionCare->aspiration_prevention_proceeded ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="aspiration_prevention_proceeded">{{ localize('global.aspiration_prevention_proceeded') }}</label>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Full Note Section -->
<div class="row">
    <div class="col-12">
        <div class="mb-3">
            <label for="nutrition_care_full_note" class="form-label">{{ localize('global.nutrition_care_full_note') }}</label>
            <textarea class="form-control" id="nutrition_care_full_note" name="nutrition_care_full_note" rows="5" maxlength="5000">{{ old('nutrition_care_full_note', $nutritionCare->nutrition_care_full_note ?? '') }}</textarea>
            <div class="form-text">{{ localize('global.max_5000_characters') }}</div>
            @error('nutrition_care_full_note')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
