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
                <input type="text" class="form-control" id="patient_name" name="patient_name" value="{{ old('patient_name', $nursingAssessment->patient_name ?? '') }}" required>
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
                        <option value="{{ $nurse->id }}" {{ old('nurse_id', $nursingAssessment->nurse_id ?? '') == $nurse->id ? 'selected' : '' }}>
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
                    <option value="App\Models\UnderReview" {{ old('morphable_type', $nursingAssessment->morphable_type ?? '') == 'App\Models\UnderReview' ? 'selected' : '' }}>{{ localize('global.under_review') }}</option>
                    <option value="App\Models\Hospitalization" {{ old('morphable_type', $nursingAssessment->morphable_type ?? '') == 'App\Models\Hospitalization' ? 'selected' : '' }}>{{ localize('global.hospitalization') }}</option>
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
            <input type="number" class="form-control" id="morphable_id" name="morphable_id" value="{{ old('morphable_id', $nursingAssessment->morphable_id ?? '') }}" required>
            @error('morphable_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>
@endif

<!-- Admission Details Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.admission_details') }}</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="admitted_from_time" class="form-label">{{ localize('global.time') }}</label>
                    <input type="time" class="form-control" id="admitted_from_time" name="admitted_from_time" value="{{ old('admitted_from_time', $nursingAssessment->admitted_from_time ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="admitted_from_date" class="form-label">{{ localize('global.date') }}</label>
                    <input type="text" autocomplete="off" class="form-control form-control datepicker_dari pdp-el" id="admitted_from_date" name="admitted_from_date" value="{{ old('admitted_from_date', $nursingAssessment->admitted_from_date ?? '') }}" placeholder="{{ localize('global.date') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ localize('global.admitted_from') }}</label>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admitted_from_emergency" name="admitted_from_emergency" value="1" {{ old('admitted_from_emergency', $nursingAssessment->admitted_from_emergency ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="admitted_from_emergency">{{ localize('global.emergency') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admitted_from_hospital" name="admitted_from_hospital" value="1" {{ old('admitted_from_hospital', $nursingAssessment->admitted_from_hospital ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="admitted_from_hospital">{{ localize('global.hospital') }}</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admitted_from_family_member" name="admitted_from_family_member" value="1" {{ old('admitted_from_family_member', $nursingAssessment->admitted_from_family_member ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="admitted_from_family_member">{{ localize('global.family_member') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="chief_complaint" class="form-label">{{ localize('global.chief_complaint') }}</label>
                    <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="3">{{ old('chief_complaint', $nursingAssessment->chief_complaint ?? '') }}</textarea>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">{{ localize('global.information_provided_by') }}</label>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="information_provided_by_patient" name="information_provided_by_patient" value="1" {{ old('information_provided_by_patient', $nursingAssessment->information_provided_by_patient ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="information_provided_by_patient">{{ localize('global.patient') }}</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="information_provided_by_family_member" name="information_provided_by_family_member" value="1" {{ old('information_provided_by_family_member', $nursingAssessment->information_provided_by_family_member ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label" for="information_provided_by_family_member">{{ localize('global.family_member') }}</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Vital Signs Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.vital_signs') }}</h5>
        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="blood_pressure" class="form-label">{{ localize('global.blood_pressure') }}</label>
                    <input type="text" class="form-control" id="blood_pressure" name="blood_pressure" value="{{ old('blood_pressure', $nursingAssessment->blood_pressure ?? '') }}" placeholder="120/80">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="pulse_rate" class="form-label">{{ localize('global.pulse_rate') }}</label>
                    <input type="number" class="form-control" id="pulse_rate" name="pulse_rate" value="{{ old('pulse_rate', $nursingAssessment->pulse_rate ?? '') }}" placeholder="72">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="respiratory_rate" class="form-label">{{ localize('global.respiratory_rate') }}</label>
                    <input type="number" class="form-control" id="respiratory_rate" name="respiratory_rate" value="{{ old('respiratory_rate', $nursingAssessment->respiratory_rate ?? '') }}" placeholder="16">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="temperature" class="form-label">{{ localize('global.temperature') }}</label>
                    <input type="number" class="form-control" id="temperature" name="temperature" value="{{ old('temperature', $nursingAssessment->temperature ?? '') }}" step="0.1" placeholder="37.0">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="oxygen_saturation" class="form-label">{{ localize('global.oxygen_saturation') }}</label>
                    <input type="number" class="form-control" id="oxygen_saturation" name="oxygen_saturation" value="{{ old('oxygen_saturation', $nursingAssessment->oxygen_saturation ?? '') }}" placeholder="98">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="weight" class="form-label">{{ localize('global.weight') }}</label>
                    <input type="number" class="form-control" id="weight" name="weight" value="{{ old('weight', $nursingAssessment->weight ?? '') }}" step="0.01" placeholder="70.5">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="height" class="form-label">{{ localize('global.height') }}</label>
                    <input type="number" class="form-control" id="height" name="height" value="{{ old('height', $nursingAssessment->height ?? '') }}" step="0.01" placeholder="170.0">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="bmi" class="form-label">{{ localize('global.bmi') }}</label>
                    <input type="number" class="form-control" id="bmi" name="bmi" value="{{ old('bmi', $nursingAssessment->bmi ?? '') }}" step="0.01" placeholder="24.2">
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Pregnancy Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.pregnancy') }}</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="pregnancy_yes" name="pregnancy_yes" value="1" {{ old('pregnancy_yes', $nursingAssessment->pregnancy_yes ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pregnancy_yes">{{ localize('global.yes') }}</label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="pregnancy_no" name="pregnancy_no" value="1" {{ old('pregnancy_no', $nursingAssessment->pregnancy_no ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pregnancy_no">{{ localize('global.not') }}</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="pregnancy_age" class="form-label">{{ localize('global.pregnancy_age') }}</label>
                    <input type="number" class="form-control" id="pregnancy_age" name="pregnancy_age" value="{{ old('pregnancy_age', $nursingAssessment->pregnancy_age ?? '') }}" placeholder="28">
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Medical History Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.medical_history') }}</h5>
        
        <!-- Underlying Disease -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">{{ localize('global.underlying_disease') }}</label>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="underlying_disease_yes" name="underlying_disease_yes" value="1" {{ old('underlying_disease_yes', $nursingAssessment->underlying_disease_yes ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="underlying_disease_yes">{{ localize('global.yes') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="underlying_disease_no" name="underlying_disease_no" value="1" {{ old('underlying_disease_no', $nursingAssessment->underlying_disease_no ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="underlying_disease_no">{{ localize('global.not') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="underlying_disease_dm" name="underlying_disease_dm" value="1" {{ old('underlying_disease_dm', $nursingAssessment->underlying_disease_dm ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="underlying_disease_dm">DM</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="underlying_disease_ht" name="underlying_disease_ht" value="1" {{ old('underlying_disease_ht', $nursingAssessment->underlying_disease_ht ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="underlying_disease_ht">HT</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" id="underlying_disease_other" name="underlying_disease_other" value="{{ old('underlying_disease_other', $nursingAssessment->underlying_disease_other ?? '') }}" placeholder="{{ localize('global.other') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Hospitalization History -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">{{ localize('global.hospitalization_history') }}</label>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hospitalization_history_yes" name="hospitalization_history_yes" value="1" {{ old('hospitalization_history_yes', $nursingAssessment->hospitalization_history_yes ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hospitalization_history_yes">{{ localize('global.yes') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="hospitalization_history_no" name="hospitalization_history_no" value="1" {{ old('hospitalization_history_no', $nursingAssessment->hospitalization_history_no ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="hospitalization_history_no">{{ localize('global.not') }}</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <textarea class="form-control" id="hospitalization_history_reasons" name="hospitalization_history_reasons" rows="2" placeholder="{{ localize('global.reasons') }}">{{ old('hospitalization_history_reasons', $nursingAssessment->hospitalization_history_reasons ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Surgical History -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">{{ localize('global.surgical_history') }}</label>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="surgical_history_yes" name="surgical_history_yes" value="1" {{ old('surgical_history_yes', $nursingAssessment->surgical_history_yes ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="surgical_history_yes">{{ localize('global.yes') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="surgical_history_no" name="surgical_history_no" value="1" {{ old('surgical_history_no', $nursingAssessment->surgical_history_no ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="surgical_history_no">{{ localize('global.not') }}</label>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <textarea class="form-control" id="surgical_history_reasons" name="surgical_history_reasons" rows="2" placeholder="{{ localize('global.reasons') }}">{{ old('surgical_history_reasons', $nursingAssessment->surgical_history_reasons ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- Allergy History -->
        <div class="row mb-3">
            <div class="col-12">
                <label class="form-label">{{ localize('global.allergy_history') }}</label>
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allergy_history_yes" name="allergy_history_yes" value="1" {{ old('allergy_history_yes', $nursingAssessment->allergy_history_yes ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allergy_history_yes">{{ localize('global.yes') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allergy_history_no" name="allergy_history_no" value="1" {{ old('allergy_history_no', $nursingAssessment->allergy_history_no ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allergy_history_no">{{ localize('global.not') }}</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="allergy_history_food" name="allergy_history_food" value="1" {{ old('allergy_history_food', $nursingAssessment->allergy_history_food ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="allergy_history_food">{{ localize('global.food') }}</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" id="allergy_history_others" name="allergy_history_others" value="{{ old('allergy_history_others', $nursingAssessment->allergy_history_others ?? '') }}" placeholder="{{ localize('global.others') }}">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Pain Assessment Section -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.pain_assessment') }}</h5>
        <div class="row">
            <div class="col-md-2">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="pain_no" name="pain_no" value="1" {{ old('pain_no', $nursingAssessment->pain_no ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pain_no">{{ localize('global.not') }}</label>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="pain_yes" name="pain_yes" value="1" {{ old('pain_yes', $nursingAssessment->pain_yes ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="pain_yes">{{ localize('global.yes') }}</label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pain_location" class="form-label">{{ localize('global.pain_location') }}</label>
                    <input type="text" class="form-control" id="pain_location" name="pain_location" value="{{ old('pain_location', $nursingAssessment->pain_location ?? '') }}" placeholder="{{ localize('global.location') }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="pain_intensity_score" class="form-label">{{ localize('global.pain_intensity_score') }}</label>
                    <input type="number" class="form-control" id="pain_intensity_score" name="pain_intensity_score" value="{{ old('pain_intensity_score', $nursingAssessment->pain_intensity_score ?? '') }}" min="0" max="10" placeholder="0-10">
                </div>
            </div>
        </div>
    </div>
</div>

<hr>

<!-- Administrative Details -->
<div class="row">
    <div class="col-12">
        <h5 class="mb-3">{{ localize('global.administrative_details') }}</h5>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="assessment_initiated_by_rn" class="form-label">{{ localize('global.assessment_initiated_by_rn') }}</label>
                    <input type="text" class="form-control" id="assessment_initiated_by_rn" name="assessment_initiated_by_rn" value="{{ old('assessment_initiated_by_rn', $nursingAssessment->assessment_initiated_by_rn ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="assessment_initiated_by_date" class="form-label">{{ localize('global.date') }}</label>
                    <input type="text" autocomplete="off" class="form-control form-control datepicker_dari pdp-el" id="assessment_initiated_by_date" name="assessment_initiated_by_date" value="{{ old('assessment_initiated_by_date', $nursingAssessment->assessment_initiated_by_date ?? '') }}" placeholder="{{ localize('global.date') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="assessment_initiated_by_time" class="form-label">{{ localize('global.time') }}</label>
                    <input type="time" class="form-control" id="assessment_initiated_by_time" name="assessment_initiated_by_time" value="{{ old('assessment_initiated_by_time', $nursingAssessment->assessment_initiated_by_time ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="patient_age" class="form-label">{{ localize('global.patient_age') }}</label>
                    <input type="number" class="form-control" id="patient_age" name="patient_age" value="{{ old('patient_age', $nursingAssessment->patient_age ?? '') }}" placeholder="35">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="file_number" class="form-label">{{ localize('global.file_number') }}</label>
                    <input type="text" class="form-control" id="file_number" name="file_number" value="{{ old('file_number', $nursingAssessment->file_number ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="hn" class="form-label">HN</label>
                    <input type="text" class="form-control" id="hn" name="hn" value="{{ old('hn', $nursingAssessment->hn ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="sn" class="form-label">SN</label>
                    <input type="text" class="form-control" id="sn" name="sn" value="{{ old('sn', $nursingAssessment->sn ?? '') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="assessment_initiated_by_nurse" class="form-label">{{ localize('global.assessment_initiated_by_nurse') }}</label>
                    <input type="text" class="form-control" id="assessment_initiated_by_nurse" name="assessment_initiated_by_nurse" value="{{ old('assessment_initiated_by_nurse', $nursingAssessment->assessment_initiated_by_nurse ?? '') }}">
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Initialize Persian date picker for date inputs
    $('.datepicker_dari').persianDatepicker({
        formatDate: 'YYYY-MM-DD',
        calendar: {
            persian: {
                locale: 'en',
                showHint: true,
                leapYearMode: 'algorithmic'
            }
        },
        checkDate: function(unix) {
            return true;
        }
    });
});
</script>
