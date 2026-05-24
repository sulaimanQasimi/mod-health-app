@php
    $registration = $nephrologyRegistration;
@endphp
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="doctor_id" class="form-label">{{ localize('global.doctor') }}</label>
        <select class="form-select @error('doctor_id') is-invalid @enderror" id="doctor_id" name="doctor_id">
            <option value="">{{ localize('global.select_doctor') }}</option>
            @foreach($doctors as $doctor)
                <option value="{{ $doctor->id }}" {{ old('doctor_id', $registration->doctor_id) == $doctor->id ? 'selected' : '' }}>
                    {{ $doctor->name }}
                </option>
            @endforeach
        </select>
        @error('doctor_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="visit_date" class="form-label">{{ localize('global.visit_date') }} <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('visit_date') is-invalid @enderror" id="visit_date" name="visit_date"
            value="{{ old('visit_date', $registration->visit_date?->format('Y-m-d')) }}" required>
        @error('visit_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">{{ localize('global.status') }} <span class="text-danger">*</span></label>
        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
            @foreach(['pending', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" {{ old('status', $registration->status) == $status ? 'selected' : '' }}>
                    {{ localize('global.' . $status) }}
                </option>
            @endforeach
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="chief_complaint" class="form-label">{{ localize('global.chief_complaint') }}</label>
        <textarea class="form-control @error('chief_complaint') is-invalid @enderror" id="chief_complaint" name="chief_complaint" rows="2">{{ old('chief_complaint', $registration->chief_complaint) }}</textarea>
        @error('chief_complaint')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="disease_id" class="form-label">{{ localize('global.disease') }}</label>
        <select class="form-select @error('disease_id') is-invalid @enderror" id="disease_id" name="disease_id">
            <option value="">{{ localize('global.select') }}</option>
            @foreach($nephrologyDiseases ?? [] as $disease)
                <option value="{{ $disease->id }}" {{ old('disease_id', $registration->disease_id) == $disease->id ? 'selected' : '' }}>
                    {{ $disease->name }}
                </option>
            @endforeach
        </select>
        @error('disease_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3">
        <label for="diagnosis" class="form-label">{{ localize('global.diagnosis') }}</label>
        <textarea class="form-control @error('diagnosis') is-invalid @enderror" id="diagnosis" name="diagnosis" rows="2">{{ old('diagnosis', $registration->diagnosis) }}</textarea>
        @error('diagnosis')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="ckd_aki_stage" class="form-label">{{ localize('global.ckd_aki_stage') }}</label>
        <input type="text" class="form-control @error('ckd_aki_stage') is-invalid @enderror" id="ckd_aki_stage" name="ckd_aki_stage"
            value="{{ old('ckd_aki_stage', $registration->ckd_aki_stage) }}" placeholder="e.g. CKD 3, AKI 2">
        @error('ckd_aki_stage')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label d-block">{{ localize('global.dialysis_required') }}</label>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="dialysis_required" id="dialysis_required_yes" value="1"
                {{ old('dialysis_required', $registration->dialysis_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="dialysis_required_yes">{{ localize('global.yes') }}</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="dialysis_required" id="dialysis_required_no" value="0"
                {{ !old('dialysis_required', $registration->dialysis_required) ? 'checked' : '' }}>
            <label class="form-check-label" for="dialysis_required_no">{{ localize('global.no') }}</label>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="dialysis_type" class="form-label">{{ localize('global.dialysis_type') }}</label>
        <select class="form-select @error('dialysis_type') is-invalid @enderror" id="dialysis_type" name="dialysis_type">
            <option value="">{{ localize('global.select') }}</option>
            @foreach(['HD', 'PD', 'CRRT'] as $type)
                <option value="{{ $type }}" {{ old('dialysis_type', $registration->dialysis_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
            @endforeach
        </select>
        @error('dialysis_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-3">
        <label for="access_type" class="form-label">{{ localize('global.access_type') }}</label>
        <select class="form-select @error('access_type') is-invalid @enderror" id="access_type" name="access_type">
            <option value="">{{ localize('global.select') }}</option>
            <option value="av_fistula" {{ old('access_type', $registration->access_type) == 'av_fistula' ? 'selected' : '' }}>{{ localize('global.av_fistula') }}</option>
            <option value="graft" {{ old('access_type', $registration->access_type) == 'graft' ? 'selected' : '' }}>{{ localize('global.graft') }}</option>
            <option value="catheter" {{ old('access_type', $registration->access_type) == 'catheter' ? 'selected' : '' }}>{{ localize('global.catheter') }}</option>
        </select>
        @error('access_type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row">
    <div class="col-12 mb-2">
        <h6 class="text-muted">{{ localize('global.labs') }}</h6>
    </div>
    @foreach([
        'lab_creatinine' => 'creatinine',
        'lab_urea' => 'urea',
        'lab_potassium' => 'potassium',
        'lab_sodium' => 'sodium',
        'lab_hb' => 'hb',
    ] as $field => $label)
        <div class="col-md-2 mb-3">
            <label for="{{ $field }}" class="form-label">{{ localize('global.' . $label) }}</label>
            <input type="number" step="0.01" min="0" class="form-control @error($field) is-invalid @enderror"
                id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $registration->$field) }}">
            @error($field)
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    @endforeach
</div>

<div class="row">
    <div class="col-md-12 mb-3">
        <label for="notes" class="form-label">{{ localize('global.notes') }}</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $registration->notes) }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12 mb-3">
        <label for="follow_up_plan" class="form-label">{{ localize('global.follow_up_plan') }}</label>
        <textarea class="form-control @error('follow_up_plan') is-invalid @enderror" id="follow_up_plan" name="follow_up_plan" rows="3">{{ old('follow_up_plan', $registration->follow_up_plan) }}</textarea>
        @error('follow_up_plan')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
